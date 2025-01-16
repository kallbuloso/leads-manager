<?php
class LM_Forms
{
    public function __construct()
    {
        add_shortcode('lm_newsletter_form', array($this, 'newsletter_form'));
        add_shortcode('lm_contact_form', array($this, 'contact_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    public function enqueue_scripts()
    {
        // Verifica se os scripts já foram carregados
        if (wp_script_is('lm-forms-script', 'enqueued')) {
            return;
        }

        // Estilos
        wp_enqueue_style(
            'lm-forms-style',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/forms.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_style(
            'lm-toast-style',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/toast.css',
            array(),
            '1.0.0'
        );

        // Scripts
        wp_enqueue_script(
            'lm-toast-script',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/toast.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'lm-forms-script',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/forms.js',
            array('jquery', 'lm-toast-script'),
            '1.0.0',
            true
        );

        wp_localize_script('lm-forms-script', 'lmAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lm-nonce')
        ));
    }

    public function admin_enqueue_scripts($hook)
    {
        if ('leads-manager_page_leads-newsletter' !== $hook && 'toplevel_page_leads-manager' !== $hook) {
            return;
        }

        // Estilos
        wp_enqueue_style(
            'lm-admin-style',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_style(
            'lm-leads-style',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/leads.css',
            array(),
            '1.0.0'
        );

        // Scripts
        wp_enqueue_script(
            'lm-toast-script',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/toast.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'lm-leads-script',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/leads.js',
            array('jquery', 'lm-toast-script'),
            '1.0.0',
            true
        );

        wp_localize_script('lm-leads-script', 'lmAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lm-nonce')
        ));
    }

    public function newsletter_form()
    {
        $button_text = LM_Settings::get_option('subscribe_button_text', 'Inscrever-se');
        $button_color = LM_Settings::get_option('subscribe_button_color', '#0073aa');

        // Calcula o brilho da cor do botão
        $hex = ltrim($button_color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Fórmula para calcular o brilho (quanto maior, mais clara é a cor)
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        // Se a cor for clara (brightness > 128), texto preto, senão branco
        $text_color = $brightness > 128 ? '#000000' : '#ffffff';

        ob_start();
?>
        <style>
            .lm-newsletter-form button {
                background-color: <?php echo esc_attr($button_color); ?>;
                border-color: <?php echo esc_attr($button_color); ?>;
                color: <?php echo esc_attr($text_color); ?>;
            }

            .lm-newsletter-form button:hover {
                background-color: <?php echo esc_attr($this->adjust_brightness($button_color, -20)); ?>;
                border-color: <?php echo esc_attr($this->adjust_brightness($button_color, -20)); ?>;
                color: <?php echo esc_attr($text_color); ?>;
            }
        </style>
        <div class="lm-newsletter-form">
            <form id="lm-newsletter">
                <input type="email" name="email" placeholder="Seu e-mail" required>
                <button type="submit"><?php echo esc_html($button_text); ?></button>
            </form>
        </div>
    <?php
        return ob_get_clean();
    }

    private function adjust_brightness($hex, $steps)
    {
        // Remove o # se existir
        $hex = ltrim($hex, '#');

        // Converte para RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Ajusta o brilho
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Converte de volta para hex
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    public function contact_form()
    {
        ob_start();
    ?>
        <div class="lm-contact-form">
            <form id="lm-contact">
                <div class="form-group">
                    <label for="nome_completo">Nome Completo</label>
                    <input type="text" id="nome_completo" name="nome_completo" required>
                </div>
                <div class="form-group">
                    <label for="nome_preferido">Como gostaria de ser chamado(a)?</label>
                    <input type="text" id="nome_preferido" name="nome_preferido">
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_contato">Tipo de Contato</label>
                        <select id="tipo_contato" name="tipo_contato" required>
                            <option value="">Selecione...</option>
                            <option value="duvida">Tirar uma dúvida</option>
                            <option value="orcamento">Solicitar orçamento</option>
                            <option value="produto">Saber mais sobre um produto</option>
                            <option value="outro">Outro assunto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="departamento">Departamento</label>
                        <select id="departamento" name="departamento" required>
                            <option value="">Selecione...</option>
                            <option value="comercial">Comercial</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="rh">Recursos Humanos</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="7"></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="whatsapp" name="whatsapp">
                        <label for="whatsapp">Este número tem WhatsApp</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="aceita_whatsapp" name="aceita_whatsapp">
                        <label for="aceita_whatsapp">Aceito receber mensagens por WhatsApp</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="aceita_newsletter" name="aceita_newsletter">
                        <label for="aceita_newsletter">Desejo receber novidades por e-mail</label>
                    </div>
                </div>

                <button type="submit" class="lm-submit-button">Enviar Mensagem</button>
            </form>
        </div>
<?php
        return ob_get_clean();
    }
}
