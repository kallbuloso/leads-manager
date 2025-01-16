<?php
/**
 * Plugin Name: Leads Manager
 * Plugin URI: https://github.com/amaralkarl/leads-manager
 * Description: Sistema de gerenciamento de leads e newsletter para WordPress
 * Version: 1.0.0
 * Author: Amaral Karl
 * Author URI: https://github.com/amaralkarl
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: leads-manager
 * Domain Path: /languages
 * 
 * @package LeadsManager
 * 
 * MIT License
 * 
 * Copyright (c) 2025 Amaral Karl
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constantes
define('LM_VERSION', '1.0.0');
define('LM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carrega as classes
require_once LM_PLUGIN_DIR . 'includes/class-database.php';
require_once LM_PLUGIN_DIR . 'includes/class-helper.php';
require_once LM_PLUGIN_DIR . 'includes/class-forms.php';
require_once LM_PLUGIN_DIR . 'includes/class-newsletter.php';
require_once LM_PLUGIN_DIR . 'includes/class-settings.php';
require_once LM_PLUGIN_DIR . 'includes/class-mailer.php';
require_once LM_PLUGIN_DIR . 'includes/class-email-settings.php';
require_once LM_PLUGIN_DIR . 'includes/class-leads.php';
require_once LM_PLUGIN_DIR . 'includes/class-ajax.php';

class Leads_Manager
{
    private $forms;
    private $newsletter;
    private $settings;
    private $ajax;

    public function __construct()
    {
        $this->setup_hooks();

        // Instancia as classes
        $this->forms = new LM_Forms();
        $this->newsletter = new LM_Newsletter();
        $this->settings = new LM_Settings();
        $this->ajax = new LM_Ajax();

        // Registra hooks de ativação e desativação
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Registra configurações de email
        add_action('admin_init', array('LM_Email_Settings', 'register_settings'));
        add_action('admin_menu', array('LM_Email_Settings', 'add_settings_page'));
    }

    public function setup_hooks()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_subscribe_newsletter', array($this, 'subscribe_newsletter'));
        add_action('wp_ajax_nopriv_subscribe_newsletter', array($this, 'subscribe_newsletter'));
        add_action('admin_init', array($this, 'process_admin_actions'));
        add_action('pre_current_active_plugins', array($this, 'deactivation_warning'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function activate()
    {
        $database = new LM_Database();
        $database->create_tables();
    }

    public function deactivate()
    {
        $database = new LM_Database();
        $database->drop_tables();
    }

    public function deactivation_warning()
    {
        $database = new LM_Database();
        $count = $database->count_subscribers();
        
        if ($count > 0) {
            $class = 'notice notice-warning';
            $message = sprintf(
                'ATENÇÃO: Ao desativar o plugin Leads Manager, todos os %d inscritos da newsletter serão removidos permanentemente do banco de dados.',
                $count
            );
            printf('<div class="%1$s" style="padding: 10px;"><p><strong>%2$s</strong></p></div>', esc_attr($class), esc_html($message));
        }
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Leads Manager',
            'Leads Manager',
            'manage_options',
            'leads-manager',
            array($this, 'render_leads_page'),
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'leads-manager',
            'Newsletter',
            'Newsletter',
            'manage_options',
            'leads-newsletter',
            array($this, 'render_newsletter_page')
        );
    }

    public function render_leads_page()
    {
        require_once LM_PLUGIN_DIR . 'admin/leads-page.php';
    }

    public function render_newsletter_page()
    {
        require_once LM_PLUGIN_DIR . 'admin/newsletter-page.php';
    }

    public function subscribe_newsletter()
    {
        check_ajax_referer('lm-nonce', 'nonce');

        $email = sanitize_email($_POST['email']);
        $newsletter = new LM_Newsletter();
        $result = $newsletter->subscribe($email);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result['message']);
        }
    }

    public function process_admin_actions()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Processar atualização de status se houver
        if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
            check_admin_referer('update_newsletter_status');
            
            $id = intval($_POST['subscriber_id']);
            $status = sanitize_text_field($_POST['status']);
            
            $newsletter = new LM_Newsletter();
            $result = $newsletter->update_status($id, $status);
            
            if (is_wp_error($result)) {
                add_settings_error(
                    'newsletter_messages',
                    'update_failed',
                    $result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'newsletter_messages',
                    'update_success',
                    'Status atualizado com sucesso!',
                    'success'
                );
            }

            // Redireciona após o processamento
            $redirect_url = add_query_arg(array(
                'page' => 'leads-newsletter',
                'status' => isset($_GET['status']) ? $_GET['status'] : 'active'
            ), admin_url('admin.php'));
            
            wp_safe_redirect($redirect_url);
            exit;
        }

        // Processar exportação de inscritos
        if (isset($_POST['action']) && $_POST['action'] === 'export_subscribers') {
            check_admin_referer('export_newsletter', 'export_nonce');
            
            $status = sanitize_text_field($_POST['export_status']);
            $newsletter = new LM_Newsletter();
            $subscribers = $newsletter->get_subscribers($status);
            
            // Prepara o cabeçalho do CSV
            $filename = 'newsletter-subscribers-' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            
            // Cria o arquivo CSV
            $output = fopen('php://output', 'w');
            
            // Adiciona BOM para UTF-8
            fputs($output, "\xEF\xBB\xBF");
            
            // Cabeçalho
            fputcsv($output, array('Email', 'Status', 'Data de Cadastro'));
            
            // Dados
            foreach ($subscribers as $subscriber) {
                $status_pt = LM_Helper::get_status_pt($subscriber->status);
                fputcsv($output, array(
                    $subscriber->email,
                    $status_pt,
                    date('d/m/Y H:i', strtotime($subscriber->created_at))
                ));
            }
            
            fclose($output);
            exit;
        }
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook === 'plugins.php') {
            wp_enqueue_script(
                'lm-plugin-admin',
                plugins_url('assets/js/plugin-admin.js', __FILE__),
                array('jquery'),
                LM_VERSION,
                true
            );

            // Passa a informação sobre inscritos para o JavaScript
            $database = new LM_Database();
            $has_subscribers = $database->count_subscribers() > 0;
            wp_localize_script('lm-plugin-admin', 'lmHasSubscribers', $has_subscribers);
        }
    }

    public function enqueue_scripts() {
        wp_enqueue_style('lm-forms', plugins_url('assets/css/forms.css', __FILE__));
        wp_enqueue_style('lm-toast', plugins_url('assets/css/toast.css', __FILE__));
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('lm-toast', plugins_url('assets/js/toast.js', __FILE__), array(), '1.0', true);
        wp_enqueue_script('lm-forms', plugins_url('assets/js/forms.js', __FILE__), array('jquery', 'lm-toast'), '1.0', true);
        
        wp_localize_script('lm-forms', 'lmAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lm-nonce')
        ));
    }
}

// Inicializar o plugin
function leads_manager()
{
    static $instance = null;
    if (null === $instance) {
        $instance = new Leads_Manager();
    }
    return $instance;
}

leads_manager();
