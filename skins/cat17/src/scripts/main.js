(function ($) {

	var init = function () {
		smoothScroll();

		ContactInfo();

		formInfosManager();

		stateBarMenu();

		heightInfo();

		// replace all form elements with modified default options
		jcf.replaceAll();

		selectedSelect();


	};

	$(document).ready(function () {
		init();
	});

	$(window).resize(function () {
		init();
	});

	var selectedSelect = function () {

		$("#treatment").change(function()  {
			console.log("hola");
		});

		if($("#treatment option:selected").length) {
		//	console.log("selected");
		}
	};

	var heightInfo = function () {
		$(".info-text").on("change", "input, select", function () {
			var formHeight = $(this).closest(".info-text").outerHeight();
			$(this).closest(".show-info").css("min-height", formHeight + "px");
		})
		/*$(".personal .wrap-input").on("change", "input", function() {
		 var formHeight = $(this).next(".wrap-info .info-text.opened").outerHeight();
		 $(this).closest(".show-info").css("min-height", formHeight + "px");
		 })*/

	};

	var formInfosManager = function () {
		//console.log("infos");
		var $element = $("section.donation-amount");
		var rt = ($(window).width() - ($element.offset().left + $element.outerWidth()));

		if ($(window).width() < 660) {

			$(".wrap-field").css({"margin-right": -rt, "margin-left": -rt});
			$(".wrap-field  .wrap-input").css({"padding-right": rt, "padding-left": rt});
			$(".wrap-field  .wrap-check").css({"padding-right": rt, "padding-left": rt + 34});
			$(".wrap-field  .info-text").css({"padding-right": rt, "padding-left": rt + 36});
			$(".wrap-field  .info-text-bottom").css({"padding-right": rt, "padding-left": rt});
			$(".wrap-field .border-bt").css({"width": rt + 38});
			$("#overview .wrap-field .border-bt").css({"width": rt});

			//console.log(rt);
		} else {
			rt = 0;
			$(".wrap-field").css({"margin-right": -rt, "margin-left": -rt});
			$(".wrap-field  .wrap-input").css({"padding-right": rt, "padding-left": rt});
			$(".wrap-field  .wrap-check").css({"padding-right": rt, "padding-left": rt + 4});
			$(".wrap-field  .info-text").css({"padding-right": rt, "padding-left": rt + 36});
			$(".wrap-field  .info-text-bottom").css({"padding-right": rt, "padding-left": rt});
			$(".wrap-field .border-bt").css({"width": rt + 34});
			$("#overview .wrap-field .border-bt").css({"width": rt});
		}

		$(".show-info input[type=radio]").on("click", function () {

			var id = this.id
			var fieldsetId = $(this).parents("fieldset").prop("id");

			var bodyId = $("body").prop("id");

			if (fieldsetId != 'type-membership') {
				$('fieldset#' + fieldsetId + ' .wrap-field').removeClass("selected notselected");
				$('fieldset#' + fieldsetId + ' .info-text').removeClass("opened");
				$('[data-info="' + id + '"]').toggleClass("opened");
				$(this).parents(".wrap-field").toggleClass("selected");
				$(this).parents(".selected").prevAll('.wrap-field:first').toggleClass("notselected");
			}

		});

		if ($(window).width() < 1200) {
			$("#overview").on("click", ".wrap-field.completed .wrap-input, .wrap-field.invalid .wrap-input", function (e) {
				e.preventDefault();
				$(this).next(".info-text-bottom").toggleClass("opened");
			});
		} else {

		}


	};


	var stateBarMenu = function () {

		var ACTIVE_THRESHOLD = 55;

		$.fn.isVisible = function (type) {
			// Current distance from the top of the page
			var windowScrollTopView = $(window).scrollTop();
			// Current distance from the top of the page, plus the height of the window
			var windowBottomView = windowScrollTopView + $(window).height();
			// Element distance from top
			var elemTop = $(this).offset().top;
			// Element distance from top, plus the height of the element
			if (type == "top") {
				offset = 50;
			} else {
				offset = +380;
			}
			var elemBottom = elemTop + $(this).height() + offset;
			return ((elemBottom <= windowBottomView) && (elemTop >= windowScrollTopView));
		};

		if ($('.state-bar').length == 0) return;
		var fixBarTop = $('.state-bar').offset().top;
		var donationSection = $("#donation-amount").offset().top;
		var donationPaymentSection = $("#donation-payment").offset().top;
		var donationTypeSection = $("#donation-type").offset().top;


		if ($(window).width() < 1023) {
			$(window).scroll(function () {
				var currentScroll = $(window).scrollTop();
				if (currentScroll + 70 >= fixBarTop) {
					$('.state-bar').addClass('active');
					$('.menu-main').addClass('under-bar');
					if ($('.footer').isVisible('top')) {
						$(".state-bar").removeClass('active');
						$('.menu-main').removeClass('under-bar');
					} else {
						$(".state-bar").addClass('active');
						$('.menu-main').addClass('under-bar');
					}
				} else {
					$('.state-bar').removeClass('active');
					$('.menu-main').removeClass('under-bar');
				}
				if (currentScroll >= donationSection - ACTIVE_THRESHOLD) {
					$('.state-overview .amount').addClass('enabled');
				} else {
					$('.state-overview .amount').removeClass('enabled');
				}
			});
		} else {
			$(window).scroll(function () {
				var currentScroll = $(window).scrollTop();
				var initialTop = 200;
				if (currentScroll >= initialTop) {
					$('.state-overview .wrap-bar').addClass('fixed');
				} else {
					$('.state-overview .wrap-bar').removeClass('fixed');
				}
				;
				if ($(".overview").isVisible('lateral') || $('#other-info').isVisible('lateral')) {
					$(".state-bar-lateral").removeClass('active');
				} else {
					$(".state-bar-lateral").addClass('active');
				}
			});
		}


		if ($("body#membership").length) {
			var memberTypeSection = $("#membership-type").offset().top;
			$(window).scroll(function () {
				var currentScroll = $(window).scrollTop();
				var typeMemberElements = $('.state-overview .member-type');
				var donatorElements = $('.state-overview .donator-type');
				var amountElements = $('.state-overview .amount');
				var paymentElemnts = $('.state-overview .payment-method');

				typeMemberElements.removeClass('enabled');
				donatorElements.removeClass('enabled');
				amountElements.removeClass('enabled');
				paymentElemnts.removeClass('enabled');

				if (currentScroll >= donationPaymentSection - ACTIVE_THRESHOLD) {
					paymentElemnts.addClass('enabled');
				}
				else if (currentScroll >= donationSection - ACTIVE_THRESHOLD) {
					amountElements.addClass('enabled');
				}
				else if (currentScroll >= donationTypeSection - ACTIVE_THRESHOLD) {
					donatorElements.addClass('enabled');
				}
				else if (currentScroll >= memberTypeSection - ACTIVE_THRESHOLD) {
					typeMemberElements.addClass('enabled');
				}

			});

		} else {
			$(window).scroll(function () {
				var currentScroll = $(window).scrollTop();
				var amountElements = $('.state-overview .amount');
				var paymentElemnts = $('.state-overview .payment-method');
				var donatorElements = $('.state-overview .donator-type');

				amountElements.removeClass('enabled');
				paymentElemnts.removeClass('enabled');
				donatorElements.removeClass('enabled');
				if (currentScroll >= donationTypeSection - ACTIVE_THRESHOLD) {
					donatorElements.addClass('enabled');
					donatorElements.removeClass('disabled');
				}
				else if (currentScroll >= donationPaymentSection - ACTIVE_THRESHOLD) {
					paymentElemnts.addClass('enabled');
					paymentElemnts.removeClass('disabled');
				}
				else if (currentScroll >= donationSection - ACTIVE_THRESHOLD) {
					amountElements.addClass('enabled');
					amountElements.removeClass('disabled');
				}
			});
		}

	};

	var ContactInfo = function () {
		if ($(window).width() < 678) {
			// $(".other-info-lateral").insertAfter($(".menu-main > ul"));
		}

	};

	var smoothScroll = function () {
		$('a[href*="#"]')
			// Remove links that don't actually link to anything
			.not('[href="#"]')
			.not('[href="#0"]')
			.click(function (event) {
				if (!$(this).closest(".wrap-field.completed").length) {
					// On-page links
					if (
						location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
						&&
						location.hostname == this.hostname
					) {
						// Figure out element to scroll to
						var target = $(this.hash);
						target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
						// Does a scroll target exist?
						if (target.length) {
							// Only prevent default if animation is actually gonna happen
							event.preventDefault();
							$('html, body').animate({
								scrollTop: target.offset().top - 55
							}, 1000, function () {
								// Callback after animation
								// Must change focus!
								var $target = $(target);
								$target.focus();
								if ($target.is(":focus")) { // Checking if the target was focused
									return false;
								} else {
									$target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
									$target.focus(); // Set focus again
								}
							});
						}
					}
				}
			});
	};


})(jQuery);
