<?php
class LM_Ajax {
    public function __construct() {
        // Admin
        add_action('wp_ajax_update_lead_status', array($this, 'update_lead_status'));
        add_action('wp_ajax_get_lead_stats', array($this, 'get_lead_stats'));
        add_action('wp_ajax_get_lead_details', array($this, 'get_lead_details'));
        add_action('wp_ajax_delete_lead', array($this, 'delete_lead'));

        // Frontend
        add_action('wp_ajax_lm_save_lead', array($this, 'save_lead'));
        add_action('wp_ajax_nopriv_lm_save_lead', array($this, 'save_lead'));
        add_action('wp_ajax_lm_subscribe', array($this, 'subscribe'));
        add_action('wp_ajax_nopriv_lm_subscribe', array($this, 'subscribe'));
    }

    public function update_lead_status() {
        check_ajax_referer('update_lead_status');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissão negada');
        }

        $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!$lead_id || !$status) {
            wp_send_json_error('Parâmetros inválidos');
        }

        $leads = new LM_Leads();
        $result = $leads->update_status($lead_id, $status);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function get_lead_stats() {
        check_ajax_referer('lm-nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissão negada');
        }

        $leads = new LM_Leads();
        $stats = array(
            'total' => $leads->count_leads('all'),
            'new' => $leads->count_leads('new'),
            'contacted' => $leads->count_leads('contacted'),
            'converted' => $leads->count_leads('converted')
        );

        wp_send_json_success($stats);
    }

    public function get_lead_details() {
        check_ajax_referer('get_lead_details');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissão negada');
        }

        $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;

        if (!$lead_id) {
            wp_send_json_error('ID do lead inválido');
        }

        $leads = new LM_Leads();
        $lead = $leads->get_lead($lead_id);

        if (!$lead) {
            wp_send_json_error('Lead não encontrado');
        }

        // Formata as datas
        $lead->data_criacao = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->data_criacao));
        $lead->data_atualizacao = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->data_atualizacao));

        wp_send_json_success($lead);
    }

    public function delete_lead() {
        check_ajax_referer('delete_lead');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissão negada');
        }

        $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0;

        if (!$lead_id) {
            wp_send_json_error('ID do lead inválido');
        }

        $leads = new LM_Leads();
        $result = $leads->delete_lead($lead_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success();
    }

    public function save_lead() {
        check_ajax_referer('lm-nonce', 'nonce');

        $data = array(
            'nome_completo' => isset($_POST['nome_completo']) ? sanitize_text_field($_POST['nome_completo']) : '',
            'nome_preferido' => isset($_POST['nome_preferido']) ? sanitize_text_field($_POST['nome_preferido']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'telefone' => isset($_POST['telefone']) ? sanitize_text_field($_POST['telefone']) : '',
            'whatsapp' => isset($_POST['whatsapp']) ? 1 : 0,
            'aceita_whatsapp' => isset($_POST['aceita_whatsapp']) ? 1 : 0,
            'departamento' => isset($_POST['departamento']) ? sanitize_text_field($_POST['departamento']) : '',
            'tipo_contato' => isset($_POST['tipo_contato']) ? sanitize_text_field($_POST['tipo_contato']) : '',
            'mensagem' => isset($_POST['mensagem']) ? sanitize_textarea_field($_POST['mensagem']) : '',
            'origem' => isset($_POST['origem']) ? sanitize_text_field($_POST['origem']) : 'form'
        );

        $leads = new LM_Leads();
        $result = $leads->save_lead($data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Lead salvo com sucesso!');
    }

    public function subscribe() {
        check_ajax_referer('lm-nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!$email) {
            wp_send_json_error('Email inválido');
        }

        $newsletter = new LM_Newsletter();
        $result = $newsletter->subscribe($email);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Inscrição realizada com sucesso!');
    }
}
