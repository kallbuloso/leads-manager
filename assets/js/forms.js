jQuery(document).ready(function ($) {
  // Inicializa o toast
  const toast = new LMToast();

  function showToast(message, type) {
    toast.show(message, type);
  }

  // Remove handlers existentes antes de adicionar novos
  $("#lm-newsletter").off("submit");
  $("#lm-contact").off("submit");

  // Formulário de Newsletter
  $("#lm-newsletter").on("submit", function (e) {
    e.preventDefault();

    var form = $(this);
    var email = form.find('input[name="email"]').val();

    $.ajax({
      url: lmAjax.ajaxurl,
      type: "POST",
      data: {
        action: "lm_subscribe",
        email: email,
        _ajax_nonce: lmAjax.nonce,
      },
      beforeSend: function () {
        form.find('button[type="submit"]').prop("disabled", true);
        showToast("Enviando...", "info");
      },
      success: function (response) {
        if (response.success) {
          showToast(response.data, "success");
          form[0].reset();
        } else {
          showToast(response.data || "Erro ao processar inscrição.", "error");
        }
      },
      error: function () {
        showToast("Erro ao processar inscrição.", "error");
      },
      complete: function () {
        form.find('button[type="submit"]').prop("disabled", false);
      },
    });
  });

  // Formulário de Contato
  $("#lm-contact").on("submit", function (e) {
    e.preventDefault();

    var form = $(this);
    var data = {
      action: "lm_save_lead",
      nonce: lmAjax.nonce,
      origem: 'form'
    };

    // Coleta todos os campos do formulário
    form.find("input, textarea, select").each(function () {
      if ($(this).attr("type") === "checkbox") {
        data[$(this).attr("name")] = $(this).prop("checked") ? 1 : 0;
      } else {
        data[$(this).attr("name")] = $(this).val();
      }
    });

    $.ajax({
      url: lmAjax.ajaxurl,
      type: "POST",
      data: data,
      beforeSend: function () {
        form.find('button[type="submit"]').prop("disabled", true);
        showToast("Enviando...", "info");
      },
      success: function (response) {
        if (response.success) {
          showToast(response.data, "success");
          form[0].reset();
        } else {
          showToast(response.data || "Erro ao enviar mensagem.", "error");
        }
      },
      error: function () {
        showToast("Erro ao enviar mensagem.", "error");
      },
      complete: function () {
        form.find('button[type="submit"]').prop("disabled", false);
      },
    });
  });
});
