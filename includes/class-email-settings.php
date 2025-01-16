<?php
class LM_Email_Settings
{
    public static function get_smtp_settings()
    {
        return array(
            'smtp_host' => get_option('lm_smtp_host', 'smtp.gmail.com'),
            'smtp_port' => get_option('lm_smtp_port', '587'),
            'smtp_user' => get_option('lm_smtp_user', ''),
            'smtp_pass' => get_option('lm_smtp_pass', ''),
            'smtp_secure' => get_option('lm_smtp_secure', 'tls'),
            'from_email' => get_option('lm_from_email', get_bloginfo('admin_email')),
            'from_name' => get_option('lm_from_name', get_bloginfo('name'))
        );
    }

    public static function add_settings_page()
    {
        add_submenu_page(
            'leads-manager',
            'Configurações de Email',
            'Config. Email',
            'manage_options',
            'leads-email-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function register_settings()
    {
        register_setting('lm_email_settings', 'lm_smtp_host');
        register_setting('lm_email_settings', 'lm_smtp_port');
        register_setting('lm_email_settings', 'lm_smtp_user');
        register_setting('lm_email_settings', 'lm_smtp_pass');
        register_setting('lm_email_settings', 'lm_smtp_secure');
        register_setting('lm_email_settings', 'lm_from_email');
        register_setting('lm_email_settings', 'lm_from_name');
    }

    public static function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Salva as configurações
        if (isset($_POST['lm_email_settings_nonce']) && wp_verify_nonce($_POST['lm_email_settings_nonce'], 'lm_email_settings')) {
            update_option('lm_smtp_host', sanitize_text_field($_POST['lm_smtp_host']));
            update_option('lm_smtp_port', sanitize_text_field($_POST['lm_smtp_port']));
            update_option('lm_smtp_user', sanitize_text_field($_POST['lm_smtp_user']));
            if (!empty($_POST['lm_smtp_pass'])) {
                update_option('lm_smtp_pass', sanitize_text_field($_POST['lm_smtp_pass']));
            }
            update_option('lm_smtp_secure', sanitize_text_field($_POST['lm_smtp_secure']));
            update_option('lm_from_email', sanitize_email($_POST['lm_from_email']));
            update_option('lm_from_name', sanitize_text_field($_POST['lm_from_name']));

            echo '<div class="notice notice-success"><p>Configurações salvas com sucesso!</p></div>';
        }

        $settings = self::get_smtp_settings();
?>
        <div class="wrap">
            <h1>Configurações de Email</h1>
            <form method="post" action="">
                <?php wp_nonce_field('lm_email_settings', 'lm_email_settings_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="lm_smtp_host">Servidor SMTP</label></th>
                        <td>
                            <input type="text" name="lm_smtp_host" id="lm_smtp_host"
                                value="<?php echo esc_attr($settings['smtp_host']); ?>" class="regular-text">
                            <p class="description">Ex: smtp.gmail.com</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_smtp_port">Porta SMTP</label></th>
                        <td>
                            <input type="text" name="lm_smtp_port" id="lm_smtp_port"
                                value="<?php echo esc_attr($settings['smtp_port']); ?>" class="regular-text">
                            <p class="description">Ex: 587 para TLS, 465 para SSL</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_smtp_user">Usuário SMTP</label></th>
                        <td>
                            <input type="text" name="lm_smtp_user" id="lm_smtp_user"
                                value="<?php echo esc_attr($settings['smtp_user']); ?>" class="regular-text">
                            <p class="description">Seu endereço de email completo</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_smtp_pass">Senha SMTP</label></th>
                        <td>
                            <input type="password" name="lm_smtp_pass" id="lm_smtp_pass" class="regular-text">
                            <p class="description">Para Gmail, use uma senha de app</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_smtp_secure">Segurança</label></th>
                        <td>
                            <select name="lm_smtp_secure" id="lm_smtp_secure">
                                <option value="tls" <?php selected($settings['smtp_secure'], 'tls'); ?>>TLS</option>
                                <option value="ssl" <?php selected($settings['smtp_secure'], 'ssl'); ?>>SSL</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_from_email">Email de Envio</label></th>
                        <td>
                            <input type="email" name="lm_from_email" id="lm_from_email"
                                value="<?php echo esc_attr($settings['from_email']); ?>" class="regular-text">
                            <p class="description">Seu endereço de email completo</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="lm_from_name">Nome de Envio</label></th>
                        <td>
                            <input type="text" name="lm_from_name" id="lm_from_name"
                                value="<?php echo esc_attr($settings['from_name']); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Salvar Alterações">
                </p>
            </form>

            <div class="card">
                <h3>Como configurar com Gmail</h3>
                <ol>
                    <li>Use smtp.gmail.com como servidor</li>
                    <li>Use a porta 587</li>
                    <li>Selecione TLS como segurança</li>
                    <li>Use seu email Gmail completo como usuário</li>
                    <li>Para a senha, você precisa criar uma "Senha de App":
                        <ol>
                            <li>Vá para sua Conta Google</li>
                            <li>Ative a verificação em duas etapas se ainda não estiver ativa</li>
                            <li>Vá para "Senhas de App"</li>
                            <li>Gere uma nova senha para "Email"</li>
                            <li>Use essa senha gerada no campo senha acima</li>
                        </ol>
                    </li>
                    <li>Veja este <a href="https://www.youtube.com/watch?v=4Qgz2c7yR7s" target="_blank">v&iacute;deo no YouTube</a> para mais dicas</li>

                </ol>
            </div>
        </div>
<?php
    }
}
