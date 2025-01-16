jQuery(document).ready(function($) {
    // Manipulação dos modais
    $('.edit-status').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var email = $(this).data('email');
        var status = $(this).data('status');
        
        $('#subscriber_id').val(id);
        $('#subscriber_email').text(email);
        $('#status').val(status);
        $('#status-modal').show();
    });

    // Exportação
    $('#export-subscribers').on('click', function(e) {
        e.preventDefault();
        $('#export-modal').show();
    });

    // Fechar modais
    $('.lm-close, .cancel-modal').on('click', function(e) {
        e.preventDefault();
        $('.lm-modal').hide();
    });

    $(window).on('click', function(e) {
        if ($(e.target).hasClass('lm-modal')) {
            $('.lm-modal').hide();
        }
    });

    // Seleção em massa
    $('#select-all').on('change', function() {
        $('input[name="subscriber[]"]').prop('checked', $(this).prop('checked'));
    });
});
