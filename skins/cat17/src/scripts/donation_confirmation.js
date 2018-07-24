(function ($) {
	$( '#membership-info-button' ).on( 'click', function() {
		$('.membership-benefits-box').show();
		$('html, body').animate({
			scrollTop: $(this).offset().top - $('.header').height()
		}, 1000);
	} );
})(jQuery);
