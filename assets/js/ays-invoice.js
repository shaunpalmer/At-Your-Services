jQuery(function ($) {
  // Intercept the Create Invoice button when present and use AJAX for faster UX
  $(document).on('click', '#ays-create-invoice', function (e) {
    // Only intercept if the quick-create form is present
    const $form = $(this).closest('form');
    if ($form.length === 0) { return; }
    e.preventDefault();

    const data = {
      action: 'ays_create_invoice',
      client_id: $('#client_id').val(),
      issue_date: $('#issue_date').val(),
      due_date: $('#due_date').val(),
      notes: $('#notes').val(),
      _ajax_nonce: (window.ays_invoice && ays_invoice.nonce) ? ays_invoice.nonce : ''
    };

    // Basic validation
    if (!data.client_id) {
      window.alert('Please select a client.');
      return;
    }

    $.post((window.ays_invoice && ays_invoice.ajax_url) ? ays_invoice.ajax_url : ajaxurl, data)
      .done(function (resp) {
        if (resp && resp.success && resp.data && resp.data.redirect) {
          window.location.href = resp.data.redirect;
        } else {
          const msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error creating invoice.';
          window.alert(msg);
        }
      })
      .fail(function () {
        window.alert('Error creating invoice.');
      });
  });
});
