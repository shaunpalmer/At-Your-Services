jQuery(function ($) {
  function money(n) { return `$${Number(n || 0).toFixed(2)}`; }
  function updatePreview() {
    $('#prev-date').text($('#ays-issue-date').val() || '—');
    $('#prev-due').text($('#ays-due-date').val() || '—');
    $('#prev-client').text($('#ays-client option:selected').text() || '—');
    $('#prev-notes').text($('#ays-notes').val() || '');

    let subtotal = 0;
    const $tbody = $('#preview-items').empty();
    $('#ays-items .item-row').each(function () {
      const desc = $(this).find('.item-desc').val() || '';
      const qty = parseFloat($(this).find('.item-qty').val()) || 0;
      const rate = parseFloat($(this).find('.item-rate').val()) || 0;
      const total = qty * rate;
      subtotal += total;
      $tbody.append(`<tr><td>${_.escape(desc)}</td><td>${qty}</td><td>${money(rate)}</td><td>${money(total)}</td></tr>`);
    });

    const tax = subtotal * 0.15;
    const grand = subtotal + tax;
    $('#subtotal').text(money(subtotal));
    $('#tax').text(money(tax));
    $('#grand-total').text(money(grand));
  }

  // Debounced update on input changes
  let t;
  $(document).on('input change', '#ays-invoice-builder-wrap input, #ays-invoice-builder-wrap select, #ays-invoice-builder-wrap textarea', function () {
    clearTimeout(t); t = setTimeout(updatePreview, 50);
  });

  $('#add-item').on('click', function (e) {
    e.preventDefault();
    $('#ays-items').append(
      '<div class="item-row">\
        <input type="text" class="item-desc" placeholder="Description">\
        <input type="number" class="item-qty" value="1" min="1">\
        <input type="number" class="item-rate" value="0" step="0.01">\
        <button class="remove-item" type="button">✖</button>\
      </div>'
    );
  });

  $(document).on('click', '.remove-item', function (e) {
    e.preventDefault();
    $(this).closest('.item-row').remove();
    updatePreview();
  });

  // Toggle New Client Form
  $('#ays-toggle-new-client').on('click', function() {
    $('#ays-new-client-form').slideToggle();
  });

  $('#ays-cancel-client-btn').on('click', function() {
    $('#ays-new-client-form').slideUp();
  });

  // Create Client Inline
  $('#ays-create-client-btn').on('click', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const originalText = $btn.text();
    const name = $('#new-client-name').val();
    
    if (!name) {
        alert('Client name is required');
        return;
    }

    $btn.prop('disabled', true).text(ays_builder.saving);

    const data = {
        action: 'ays_create_client_inline',
        nonce: ays_builder.nonce,
        name: name,
        email: $('#new-client-email').val(),
        phone: $('#new-client-phone').val(),
        address: $('#new-client-address').val()
    };

    $.post(ays_builder.ajax_url, data, function(response) {
        if (response.success) {
            // Add to select and select it
            const client = response.data.client;
            $('#ays-client').append(new Option(client.name, client.id));
            $('#ays-client').val(client.id).trigger('change');
            
            // Reset and hide form
            $('#new-client-name').val('');
            $('#new-client-email').val('');
            $('#new-client-phone').val('');
            $('#new-client-address').val('');
            $('#ays-new-client-form').slideUp();
            
            $btn.prop('disabled', false).text(originalText);
        } else {
            alert(response.data.message || ays_builder.error);
            $btn.prop('disabled', false).text(originalText);
        }
    }).fail(function() {
        alert(ays_builder.error);
        $btn.prop('disabled', false).text(originalText);
    });
  });

  // Save Handler
  $('#ays-save-invoice').on('click', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const originalText = $btn.text();
    
    $btn.prop('disabled', true).text(ays_builder.saving);

    // Collect Items
    const items = [];
    $('#ays-items .item-row').each(function() {
      items.push({
        description: $(this).find('.item-desc').val(),
        qty: $(this).find('.item-qty').val(),
        rate: $(this).find('.item-rate').val(),
        taxable: true // Default for now, or add checkbox to builder UI
      });
    });

    const data = {
      action: 'ays_save_invoice_builder',
      nonce: ays_builder.nonce,
      invoice_id: $('#ays-invoice-id').val(),
      client_id: $('#ays-client').val(),
      issue_date: $('#ays-issue-date').val(),
      due_date: $('#ays-due-date').val(),
      notes: $('#ays-notes').val(),
      items: JSON.stringify(items)
    };

    $.post(ays_builder.ajax_url, data, function(response) {
      if (response.success) {
        $btn.text(ays_builder.saved);
        setTimeout(() => {
          if (response.data.redirect) {
            window.location.href = response.data.redirect;
          } else {
            $btn.prop('disabled', false).text(originalText);
          }
        }, 1000);
      } else {
        alert(response.data.message || ays_builder.error);
        $btn.prop('disabled', false).text(originalText);
      }
    }).fail(function() {
      alert(ays_builder.error);
      $btn.prop('disabled', false).text(originalText);
    });
  });

  // Initial
  updatePreview();
});
