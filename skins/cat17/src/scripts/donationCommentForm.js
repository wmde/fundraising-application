(function ($) {

	function updateElementValidationState() {
		if ($(this).val() === "" || !this.checkValidity()) {
			$(this).removeClass('valid');
			$(this).parent().removeClass('valid');
			$(this).addClass('invalid');
			$(this).parent().addClass('invalid');
		}
		else {
			$(this).removeClass('invalid');
			$(this).parent().removeClass('invalid');
			$(this).addClass('valid');
			$(this).parent().addClass('valid');
		}
	}

	function elementIsInvalid() {
		return $(this).val() === "" || !this.checkValidity();
	}

	function submitValidation() {
		var inputElements = $('form').find('input, textarea');
		inputElements.each(updateElementValidationState);

		return inputElements.filter(elementIsInvalid).length === 0;
	}

	function setupFormValidation( form ) {
		var submitButton = form.find('input[type="submit"]');
		form.submit(submitValidation);
		submitButton.click(submitValidation);

		form.find('input, textarea').keypress(function () {
			$(this).data('data-entered', true);
		});

		form.find('input, textarea').blur(function () {
			if ($(this).data('data-entered')) {
				updateElementValidationState.apply( this );
			}
		});
	}

	function handleFormSubmission( event ) {
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
	}

	$(document).ready(function () {
		var form = $('#comment-form');
		if (form.length === 0) return;

		setupFormValidation( form );

		form.bind( 'submit', handleFormSubmission );
	});

})(jQuery);
