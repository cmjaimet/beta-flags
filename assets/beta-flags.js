jQuery( document ).ready( function( $ ) {
  $( 'span.status-marker' ).on( 'click', function( e ) {
    var $button = e.target;
    var betaKey = $button.parentElement.parentElement.querySelector( 'pre' ).innerHTML;
		var nonce_value = document.getElementById( 'betaflagsnonce' ).value;
    if ( betaKey ) {
      $.ajax( {
        type: "POST",
        url: ajaxurl,
        data: {
					action: 'betaFlag_enable',
					betaflagsnonce: nonce_value,
					betaKey: betaKey
				}
      } ).done(function ( msg ) {
				if ( true === msg.state ) {
					$( '#beta-flag-' + betaKey ).addClass('status-marker-enabled');
				} else {
					$( '#beta-flag-' + betaKey ).removeClass('status-marker-enabled');
				}
      } ).fail( function( error ) {
        $( '.notice-container' ).html('<div class="notice notice-error is-dismissible"><p>Error cannot process <code>' + error.responseJSON.response + '</code></p></div>')
      } );
    } else {
      $( '.notice-container' ).html( '<div class="notice notice-error is-dismissible"><p>Error: missing betaKey</p></div>' );
    }
  } );
} );
