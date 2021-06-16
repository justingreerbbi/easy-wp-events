jQuery(function($) {
  /*
  var stripe = Stripe('pk_test_rByFEf6MAqwryISgnzBU1AQd');
  var elements = stripe.elements();
  var style = {
    base: {
      color: '#32325d',
      lineHeight: '24px',
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '16px',
      '::placeholder': {
        color: '#aab7c4'
      }
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a'
    }
  };

  var card = elements.create('card', {
    style: style
  });

  card.mount('#card-element');

  card.addEventListener('change', function(event) {
    var displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
  });


  var form = document.getElementById('payment-form');
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    stripe.createToken(card).then(function(result) {
      if (result.error) {
        var errorElement = document.getElementById('card-errors');
        errorElement.textContent = result.error.message;
      } else {
        stripeTokenHandler(result.token);
      }
    });
  });

  function stripeTokenHandler(token) {
    var form = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    form.submit();
  }
  */

  jQuery(document).on('submit', '#frontend-ticket-form', function(e) {
    e.preventDefault();

    var data = {
      'action': 'ewp_events_create_checkout_session',
      'formData': jQuery('#frontend-ticket-form').serialize()
    };

    console.log(data);

    jQuery.ajax({
      url: ewp_events.ajaxurl,
      type: 'POST',
      data: data,
      success: function(response) {
        var response = jQuery.parseJSON(response);
        if (response.hasOwnProperty('error')) {
          alert(response.error_description);
        } else {
          window.location.href = response.checkout_url;
        }
      }
    });
  });

});