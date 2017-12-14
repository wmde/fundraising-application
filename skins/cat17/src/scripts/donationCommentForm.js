(function ($) {

	var form = $('#comment-form');

	var inputElements = form.find('input, textarea');
	var submitButton = form.find('input[type="submit"]');

	setupForm();

	function setupForm() {
		setupFormValidation();
		form.bind( 'submit', handleFormSubmission );
	}

	function setupFormValidation() {
		form.submit(onFormSubmit);
		submitButton.click(onFormSubmit);

		inputElements.keypress(function () {
			$(this).data('data-entered', true);
		});

		inputElements.blur(function () {
			if ($(this).data('data-entered')) {
				updateElementValidationState.apply( this );
			}
		});
	}

	function onFormSubmit() {
		inputElements.each(updateElementValidationState);
		return inputElements.filter(elementIsInvalid).length === 0;
	}

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

})(jQuery);
