jQuery(document).ready(function($) {
    // Toast para notificações
    const toast = new LMToast();

    // Atualização de status via AJAX
    $('.lead-status-select').change(function() {
        const select = $(this);
        const leadId = select.data('lead-id');
        const newStatus = select.val();
        const oldStatus = select.data('original-status');

        // Confirma a mudança de status
        if (confirm('Deseja realmente alterar o status deste lead?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_lead_status',
                    lead_id: leadId,
                    status: newStatus,
                    _ajax_nonce: lmAjax.nonce
                },
                beforeSend: function() {
                    select.prop('disabled', true);
                    toast.show('Atualizando status...', 'info');
                },
                success: function(response) {
                    if (response.success) {
                        toast.show('Status atualizado com sucesso!', 'success');
                        select.data('original-status', newStatus);
                        // Recarrega a página após 1 segundo
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toast.show(response.data || 'Erro ao atualizar status.', 'error');
                        select.val(oldStatus);
                    }
                },
                error: function() {
                    toast.show('Erro ao atualizar status.', 'error');
                    select.val(oldStatus);
                },
                complete: function() {
                    select.prop('disabled', false);
                }
            });
        } else {
            // Se cancelou, volta ao status anterior
            select.val(oldStatus);
        }
    });

    // Função para atualizar os contadores do dashboard
    function updateDashboardStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_lead_stats',
                _ajax_nonce: lmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('.lm-stat-number').each(function() {
                        const stat = $(this).closest('.lm-stat-box').find('h3').text().toLowerCase();
                        if (stat.includes('total')) {
                            $(this).text(stats.total);
                        } else if (stat.includes('novos')) {
                            $(this).text(stats.new);
                        } else if (stat.includes('contatados')) {
                            $(this).text(stats.contacted);
                        } else if (stat.includes('convertidos')) {
                            $(this).text(stats.converted);
                        }
                    });
                }
            }
        });
    }
});
