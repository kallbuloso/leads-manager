<?php
class LM_Mailer
{
    private static function get_template($content)
    {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Newsletter</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                }
                .content {
                    background: #fff;
                    padding: 20px;
                    border-radius: 5px;
                }
                .footer {
                    text-align: center;
                    padding: 20px 0;
                    color: #666;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . get_bloginfo('name') . '</h1>
            </div>
            <div class="content">
                ' . $content . '
            </div>
            <div class="footer">
                <p>Este é um email automático, por favor não responda.</p>
                <p>&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. Todos os direitos reservados.</p>
            </div>
        </body>
        </html>';

        return $template;
    }

    public static function send_welcome_email($to)
    {
        $subject = 'Bem-vindo à nossa Newsletter!';

        $content = '
        <p>Olá!</p>
        <p>Obrigado por se inscrever em nossa newsletter.<br>
        Você receberá nossas novidades e atualizações diretamente em seu email.</p>
        <p>Atenciosamente,<br>' . get_bloginfo('name') . '</p>';

        return self::send($to, $subject, $content);
    }

    public static function send_reactivation_email($to)
    {
        $subject = 'Sua inscrição foi reativada!';

        $content = '
        <p>Olá!</p>
        <p>Sua inscrição em nossa newsletter foi reativada com sucesso.</p>
        <p>Você voltará a receber nossas novidades e atualizações.</p>
        <p>Atenciosamente,<br>' . get_bloginfo('name') . '</p>';

        return self::send($to, $subject, $content);
    }

    public static function send_lead_notification($to, $subject, $content)
    {
        return self::send($to, $subject, $content);
    }

    public static function send_contact_form_notification($to)
    {
        $subject = 'Recebemos seu contato!';

        $content = '
        <p>Olá!</p>
        <p>Recebemos seu contato e estamos trabalhando para responder o mais breve possivel.<br>
        Obrigado por entrar em contato conosco!</p>
        <p>Atenciosamente,<br>' . get_bloginfo('name') . '</p>';

        return self::send($to, $subject, $content);
    }

    private static function send($to, $subject, $content)
    {
        // Pega as configurações de SMTP
        $settings = LM_Email_Settings::get_smtp_settings();

        // Configura os cabeçalhos
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>'
        );

        // Aplica o template
        $html = self::get_template($content);

        // Adiciona log antes do envio
        error_log('Tentando enviar email para: ' . $to);
        error_log('Assunto: ' . $subject);
        error_log('De: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>');

        // Configura o PHPMailer
        add_action('phpmailer_init', function ($phpmailer) use ($settings) {
            $phpmailer->isSMTP();
            $phpmailer->Host = $settings['smtp_host'];
            $phpmailer->Port = $settings['smtp_port'];

            if (!empty($settings['smtp_user']) && !empty($settings['smtp_pass'])) {
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $settings['smtp_user'];
                $phpmailer->Password = $settings['smtp_pass'];
            }

            $phpmailer->SMTPSecure = $settings['smtp_secure'];

            // Debug SMTP
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = function ($str, $level) {
                error_log('SMTP Debug: ' . $str);
            };
        });

        // Tenta enviar o email
        $sent = wp_mail($to, $subject, $html, $headers);

        // Adiciona log do resultado
        if ($sent) {
            error_log('Email enviado com sucesso para: ' . $to);
        } else {
            error_log('Falha ao enviar email para: ' . $to);

            // Verifica se o wp_mail está funcionando
            global $phpmailer;
            if (isset($phpmailer)) {
                error_log('Erro do PHPMailer: ' . $phpmailer->ErrorInfo);
            }
        }

        return $sent;
    }
}
