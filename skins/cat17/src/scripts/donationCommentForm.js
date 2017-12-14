(function ($) {

	$(document).ready(function () {
		commentForm();
	});

	// TODO move into view handlers
	var submitValidation = function () {
		var isValid = true;
		$('form').find('input, textarea').each(function () {
			if ($(this).val() === "" || !this.checkValidity()) {
				$(this).addClass('invalid');
				$(this).parent().addClass('invalid');
				isValid = false;
			}
			else {
				$(this).removeClass('invalid');
				$(this).parent().removeClass('invalid');
				$(this).addClass('valid');
				$(this).parent().addClass('valid');
			}
		});
		return isValid;
	};

	var commentForm = function () {
		var form = $('#comment-form');
		if (form.length === 0) return;

		var submitButton = form.find('input[type="submit"]');
		form.submit(submitValidation);
		submitButton.click(submitValidation);

		form.find('input, textarea').keypress(function () {
			$(this).data('data-entered', true);
		});

		form.find('input, textarea').blur(function () {
			if (!$(this).data('data-entered')) return;

			if ($(this).val() === "" || !this.checkValidity()) {
				$(this).addClass('invalid');
				$(this).parent().addClass('invalid');
			}
			else {
				$(this).removeClass('invalid');
				$(this).parent().removeClass('invalid');
				$(this).addClass('valid');
				$(this).parent().addClass('valid');
			}
		});

		form.bind( 'submit', function ( event ) {
			event.preventDefault();

			$.ajax( '../add-comment', {
				data: $( this ).serialize(),
				dataType: 'json',
				type: 'POST',
				success: function( response ) {
					// TODO: fix feedback display
					var $feedback = $( '#comment-form' );
					$feedback.find( '.message' ).remove();
					$feedback.append(
						$( '<div />' )
							.addClass( 'message' )
							.addClass( response.status === 'ERR' ? 'error' : 'success' )
							.text( response.message || 'Vielen Dank! Die Nachricht wurde verschickt!' )
					);
				},
				error: function ( e ){
					var $feedback = $( '#comment-form' );
					$feedback.find( '.message' ).remove();
					$feedback.append(
						$( '<div />' )
							.addClass( 'message' )
							.addClass( 'error' )
							.text( 'Die Nachricht konnte auf Grund eines Fehlers nicht verschickt werden.' )
					);
				}
			});
		});
	};

})(jQuery);
