jQuery(document).ready(function ($) {
  // Adiciona confirmação ao link de desativação do plugin
  $('tr[data-plugin="leads-manager/leads-manager.php"] .deactivate a').on(
    "click",
    function (e) {
      var hasSubscribers = window.lmHasSubscribers || false;
      if (hasSubscribers) {
        if (
          !confirm(
            "ATENÇÃO: Ao desativar o plugin, todos os Leads e os inscritos da newsletter serão removidos permanentemente do banco de dados.\n\nTem certeza que deseja continuar?"
          )
        ) {
          e.preventDefault();
        }
      }
    }
  );
});
