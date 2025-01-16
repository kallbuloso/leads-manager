<?php
class LM_Settings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    public function add_settings_page() {
        add_submenu_page(
            'leads-manager',
            'Configurações',
            'Configurações',
            'manage_options',
            'leads-settings',
            array($this, 'render_settings_page')
        );
    }

    public function init_settings() {
        register_setting('lm_settings', 'lm_settings');

        // Seção Newsletter
        add_settings_section(
            'lm_newsletter_section',
            'Configurações da Newsletter',
            array($this, 'newsletter_section_callback'),
            'lm_settings'
        );

        // Campo: Texto do botão
        add_settings_field(
            'subscribe_button_text',
            'Texto do Botão',
            array($this, 'text_field_callback'),
            'lm_settings',
            'lm_newsletter_section',
            array(
                'label_for' => 'subscribe_button_text',
                'field_name' => 'subscribe_button_text',
                'default' => 'Inscrever-se'
            )
        );

        // Campo: Cor do botão
        add_settings_field(
            'subscribe_button_color',
            'Cor do Botão',
            array($this, 'color_field_callback'),
            'lm_settings',
            'lm_newsletter_section',
            array(
                'label_for' => 'subscribe_button_color',
                'field_name' => 'subscribe_button_color',
                'default' => '#0073aa'
            )
        );
    }

    public function newsletter_section_callback() {
        echo '<p>Configure as opções da newsletter abaixo:</p>';
    }

    public function text_field_callback($args) {
        $options = get_option('lm_settings');
        $value = isset($options[$args['field_name']]) 
            ? $options[$args['field_name']] 
            : $args['default'];
        ?>
        <input type="text" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="lm_settings[<?php echo esc_attr($args['field_name']); ?>]"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text">
        <?php
    }

    public function color_field_callback($args) {
        $options = get_option('lm_settings');
        $value = isset($options[$args['field_name']]) 
            ? $options[$args['field_name']] 
            : $args['default'];
        ?>
        <input type="color" 
               id="<?php echo esc_attr($args['label_for']); ?>"
               name="lm_settings[<?php echo esc_attr($args['field_name']); ?>]"
               value="<?php echo esc_attr($value); ?>">
        <?php
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'lm_messages',
                'lm_message',
                'Configurações salvas com sucesso!',
                'updated'
            );
        }

        settings_errors('lm_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('lm_settings');
                do_settings_sections('lm_settings');
                submit_button('Salvar Configurações');
                ?>
            </form>
        </div>
        <?php
    }

    public static function get_option($key, $default = '') {
        $options = get_option('lm_settings');
        return isset($options[$key]) ? $options[$key] : $default;
    }
}
