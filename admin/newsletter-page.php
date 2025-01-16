<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . '../includes/class-helper.php';
$newsletter = new LM_Newsletter();

// Processar atualização de status se houver
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    check_admin_referer('update_newsletter_status');
    
    $id = intval($_POST['subscriber_id']);
    $status = sanitize_text_field($_POST['status']);
    
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

// Pegar status atual do filtro
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'active';
$subscribers = $newsletter->get_subscribers($current_status);

// Mostrar mensagens de erro/sucesso
settings_errors('newsletter_messages');
?>

<div class="wrap">
    <h1>Newsletter</h1>

    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="leads-newsletter">
                <select name="status">
                    <?php foreach (LM_Helper::get_status_options() as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="Filtrar">
            </form>
        </div>
        <div class="alignright">
            <button type="button" class="button button-primary" id="export-subscribers">Exportar Lista</button>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th>Email</th>
                <th>Status</th>
                <th>Data de Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($subscribers)) : ?>
                <?php foreach ($subscribers as $subscriber) : ?>
                    <?php $status_pt = LM_Helper::get_status_pt($subscriber->status); ?>
                    <tr>
                        <td><input type="checkbox" name="subscriber[]" value="<?php echo $subscriber->id; ?>"></td>
                        <td><?php echo esc_html($subscriber->email); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($subscriber->status); ?>">
                                <?php echo esc_html($status_pt); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($subscriber->created_at)); ?></td>
                        <td>
                            <button type="button" 
                                    class="button edit-status" 
                                    data-id="<?php echo $subscriber->id; ?>"
                                    data-email="<?php echo esc_attr($subscriber->email); ?>"
                                    data-status="<?php echo esc_attr($subscriber->status); ?>">
                                Editar Status
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">Nenhum inscrito encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Edição de Status -->
<div id="status-modal" class="lm-modal">
    <div class="lm-modal-content">
        <span class="lm-close">&times;</span>
        <h2>Editar Status</h2>
        <form method="post" action="">
            <?php wp_nonce_field('update_newsletter_status'); ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="subscriber_id" id="subscriber_id">
            
            <p>
                <strong>Email:</strong> <span id="subscriber_email"></span>
            </p>
            
            <p>
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                    <option value="blocked">Bloqueado</option>
                </select>
            </p>
            
            <p>
                <button type="submit" class="button button-primary">Salvar</button>
                <button type="button" class="button cancel-modal">Cancelar</button>
            </p>
        </form>
    </div>
</div>

<!-- Modal de Exportação -->
<div id="export-modal" class="lm-modal">
    <div class="lm-modal-content">
        <span class="lm-close">&times;</span>
        <h2>Exportar Lista</h2>
        <form method="post" action="">
            <?php wp_nonce_field('export_newsletter', 'export_nonce'); ?>
            <input type="hidden" name="action" value="export_subscribers">
            
            <p>
                <label for="export_status">Status dos Inscritos:</label>
                <select name="export_status" id="export_status">
                    <option value="all">Todos</option>
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                    <option value="blocked">Bloqueados</option>
                </select>
            </p>
            
            <p>
                <button type="submit" class="button button-primary">Exportar CSV</button>
                <button type="button" class="button cancel-modal">Cancelar</button>
            </p>
        </form>
    </div>
</div>
