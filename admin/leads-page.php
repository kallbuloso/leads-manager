<?php
if (!defined('ABSPATH')) {
    exit;
}

$leads = new LM_Leads();

// Debug
error_log('Iniciando busca de leads...');

// Processar ações
if (isset($_POST['action']) && isset($_POST['lead_id'])) {
    check_admin_referer('lead_action_' . $_POST['lead_id']);
    
    $lead_id = intval($_POST['lead_id']);
    $action = sanitize_text_field($_POST['action']);
    
    switch ($action) {
        case 'update_status':
            if (isset($_POST['status'])) {
                $leads->update_status($lead_id, sanitize_text_field($_POST['status']));
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>Status atualizado com sucesso!</p></div>';
                });
            }
            break;
        case 'delete':
            $leads->delete_lead($lead_id);
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Lead removido com sucesso!</p></div>';
            });
            break;
    }
}

// Filtros
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Buscar leads
$args = array(
    'status' => $status,
    'limit' => $per_page,
    'offset' => $offset
);

if (!empty($search)) {
    $args['search'] = $search;
}

$current_leads = $leads->get_leads($args);
$total_leads = $leads->count_leads($status, $search);
$total_pages = ceil($total_leads / $per_page);

// Debug
error_log('Leads encontrados: ' . print_r($current_leads, true));

// Dashboard Stats
$stats = array(
    'total' => $leads->count_leads('all'),
    'new' => $leads->count_leads('new'),
    'contacted' => $leads->count_leads('contacted'),
    'converted' => $leads->count_leads('converted')
);

// Debug
error_log('Stats: ' . print_r($stats, true));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Gerenciador de Leads</h1>
    
    <!-- Dashboard -->
    <div class="lm-dashboard">
        <div class="lm-stat-box">
            <h3>Total de Leads</h3>
            <span class="lm-stat-number"><?php echo number_format_i18n($stats['total']); ?></span>
        </div>
        <div class="lm-stat-box">
            <h3>Leads Novos</h3>
            <span class="lm-stat-number"><?php echo number_format_i18n($stats['new']); ?></span>
        </div>
        <div class="lm-stat-box">
            <h3>Leads Contatados</h3>
            <span class="lm-stat-number"><?php echo number_format_i18n($stats['contacted']); ?></span>
        </div>
        <div class="lm-stat-box">
            <h3>Leads Convertidos</h3>
            <span class="lm-stat-number"><?php echo number_format_i18n($stats['converted']); ?></span>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" class="lm-filter-form">
                <input type="hidden" name="page" value="leads-manager">
                <select name="status" class="lm-select">
                    <option value="all" <?php selected($status, 'all'); ?>>Todos os Status</option>
                    <option value="new" <?php selected($status, 'new'); ?>>Novos</option>
                    <option value="contacted" <?php selected($status, 'contacted'); ?>>Contatados</option>
                    <option value="converted" <?php selected($status, 'converted'); ?>>Convertidos</option>
                    <option value="archived" <?php selected($status, 'archived'); ?>>Arquivados</option>
                </select>
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Buscar leads...">
                <input type="submit" class="button" value="Filtrar">
                <?php 
                // Debug
                echo '<span style="margin-left: 10px;">Total: ' . $total_leads . ' leads</span>';
                ?>
            </form>
        </div>
        
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num"><?php printf(_n('%s lead', '%s leads', $total_leads), number_format_i18n($total_leads)); ?></span>
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $page
                ));
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lista de Leads -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="column-primary">Nome</th>
                <th>Contato</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Debug
            echo '<!-- Leads encontrados: ' . count($current_leads) . ' -->';
            
            if (!empty($current_leads)) : 
                foreach ($current_leads as $lead) : 
                    // Debug
                    error_log('Processando lead: ' . print_r($lead, true));
            ?>
                    <tr>
                        <td class="column-primary">
                            <strong><?php echo esc_html($lead->nome_completo); ?></strong>
                            <?php if (!empty($lead->nome_preferido)) : ?>
                                <br><small>(<?php echo esc_html($lead->nome_preferido); ?>)</small>
                            <?php endif; ?>
                            <div class="row-actions">
                                <span class="view">
                                    <a href="#" class="view-lead" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                        Ver detalhes
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td>
                            <strong>Email:</strong> <?php echo esc_html($lead->email); ?><br>
                            <strong>Tel:</strong> <?php echo esc_html($lead->telefone); ?>
                            <?php if ($lead->whatsapp) : ?>
                                <span class="dashicons dashicons-whatsapp"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select class="lead-status-select" 
                                    data-lead-id="<?php echo esc_attr($lead->id); ?>"
                                    data-original-status="<?php echo esc_attr($lead->status); ?>">
                                <option value="new" <?php selected($lead->status, 'new'); ?>>Novo</option>
                                <option value="contacted" <?php selected($lead->status, 'contacted'); ?>>Contatado</option>
                                <option value="converted" <?php selected($lead->status, 'converted'); ?>>Convertido</option>
                                <option value="archived" <?php selected($lead->status, 'archived'); ?>>Arquivado</option>
                            </select>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->data_criacao))); ?>
                        </td>
                        <td class="actions">
                            <button type="button" class="button button-small view-lead" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <button type="button" class="button button-small delete-lead" data-lead-id="<?php echo esc_attr($lead->id); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">Nenhum lead encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Detalhes do Lead -->
<div id="lead-details-modal" class="lm-modal">
    <div class="lm-modal-content">
        <span class="lm-modal-close">&times;</span>
        <h2>Detalhes do Lead</h2>
        <div id="lead-details-content" class="lead-details-grid">
            <!-- Conteúdo carregado via AJAX -->
        </div>
    </div>
</div>

<style>
/* Dashboard */
.lm-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.lm-stat-box {
    background: white;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
}

.lm-stat-box h3 {
    margin: 0 0 10px;
    color: #23282d;
}

.lm-stat-number {
    font-size: 24px;
    font-weight: 600;
    color: #0073aa;
}

/* Filtros */
.lm-filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.lm-select {
    min-width: 150px;
}

/* Status */
.lead-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.status-new {
    background: #e7f5ff;
    color: #0073aa;
}

.status-contacted {
    background: #fff8e5;
    color: #d98500;
}

.status-converted {
    background: #ecf9ec;
    color: #1e7e34;
}

.status-archived {
    background: #f5f5f5;
    color: #666;
}

/* Modal */
.lm-modal {
    display: none;
    position: fixed;
    z-index: 999999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.lm-modal-content {
    position: relative;
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border-radius: 4px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.lm-modal-close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.lead-details-grid {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 15px;
    margin-top: 20px;
}

.lead-details-grid strong {
    color: #23282d;
}

/* Responsivo */
@media screen and (max-width: 782px) {
    .lm-filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .lm-select, 
    .lm-filter-form input[type="search"] {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .lead-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Atualização de status
    $('.lead-status-select').change(function() {
        if ($(this).val()) {
            var leadId = $(this).data('lead-id');
            var originalStatus = $(this).data('original-status');
            var newStatus = $(this).val();
            
            if (newStatus !== originalStatus) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_lead_status',
                        lead_id: leadId,
                        status: newStatus,
                        _ajax_nonce: '<?php echo wp_create_nonce("update_lead_status"); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $(this).data('original-status', newStatus);
                            console.log('Status atualizado com sucesso!');
                        } else {
                            console.log('Erro ao atualizar status!');
                        }
                    }
                });
            }
        }
    });

    // Modal de detalhes
    $('.view-lead').click(function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_lead_details',
                lead_id: leadId,
                _ajax_nonce: '<?php echo wp_create_nonce("get_lead_details"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var lead = response.data;
                    var content = `
                        <div><strong>Nome Completo:</strong></div>
                        <div>${lead.nome_completo}</div>
                        
                        ${lead.nome_preferido ? `
                            <div><strong>Nome Preferido:</strong></div>
                            <div>${lead.nome_preferido}</div>
                        ` : ''}
                        
                        <div><strong>Email:</strong></div>
                        <div>${lead.email}</div>
                        
                        <div><strong>Telefone:</strong></div>
                        <div>${lead.telefone}</div>
                        
                        <div><strong>WhatsApp:</strong></div>
                        <div>${lead.whatsapp ? 'Sim' : 'Não'}</div>
                        
                        <div><strong>Aceita contato WhatsApp:</strong></div>
                        <div>${lead.aceita_whatsapp ? 'Sim' : 'Não'}</div>
                        
                        <div><strong>Departamento:</strong></div>
                        <div>${lead.departamento}</div>
                        
                        <div><strong>Tipo de Contato:</strong></div>
                        <div>${lead.tipo_contato}</div>
                        
                        <div><strong>Mensagem:</strong></div>
                        <div>${lead.mensagem}</div>
                        
                        <div><strong>Origem:</strong></div>
                        <div>${lead.origem}</div>
                        
                        <div><strong>Data de Criação:</strong></div>
                        <div>${lead.data_criacao}</div>
                        
                        <div><strong>Última Atualização:</strong></div>
                        <div>${lead.data_atualizacao}</div>
                    `;
                    
                    $('#lead-details-content').html(content);
                    $('#lead-details-modal').show();
                }
            }
        });
    });

    // Fechar modal
    $('.lm-modal-close').click(function() {
        $('#lead-details-modal').hide();
    });

    $(window).click(function(e) {
        if ($(e.target).hasClass('lm-modal')) {
            $('#lead-details-modal').hide();
        }
    });

    // Excluir lead
    $('.delete-lead').click(function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        
        if (confirm('Tem certeza que deseja excluir este lead?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_lead',
                    lead_id: leadId,
                    _ajax_nonce: '<?php echo wp_create_nonce("delete_lead"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Lead excluído com sucesso!');
                        location.reload();
                    } else {
                        console.log('Erro ao excluir lead!');
                    }
                }
            });
        }
    });
});
</script>
