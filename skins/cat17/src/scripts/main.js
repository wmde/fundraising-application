(function ($) {

    var init = function () {

        commentForm();

        formInfosManager();

        stateBarMenu();

        heightInfo();

        containersManager();

        openCommentItem();


        // replace select elements
        jcf.replaceAll();

        selectedSelect();
    };

    $(document).ready(function () {
        init();
    });

    $(window).resize(function () {
        containersManager();
    });


    var openCommentItem = function () {
        var $element = $('.supporter-item.wrap-field.commented');
        if ($element.length) {
            $($element).on("click", function () {
                $(this).toggleClass("selected");
                $(this).children(".info-text").toggleClass("opened");
            });
        }
    };

    var containersManager = function () {
        var $element = $('.switch-container');
        if ($element.length) {
            if ($(window).width() < 660) {
                $($element).addClass("container-fluid no-gutter");
                $($element).removeClass("container");
            } else {
                $($element).addClass("container");
                $($element).removeClass("container-fluid no-gutter");
            }
        }
    };

    // TODO Move into View Handler
    var selectedSelect = function () {

        if ($(window).width() < 1024) {
            $('select#salutation').children('option.hideme').prop('disabled',true);
         }
        $("select").change(function () {
            $(this).closest("span.jcf-select").addClass("selected-item");
            $(this).next("span").addClass("selected-item");
        });
        $(".country-select").closest("span.jcf-select").addClass("selected-item");
        $(".country-select").next("span").addClass("selected-item");
    };

    var heightInfo = function () {

        $(".info-text").on("change", "input, select", function () {
            $(this).closest("fieldset").css("min-height", 0);
            var fieldsetHeight = $(this).closest("fieldset").height();
            var formHeight = $(this).closest(".info-text").prop('scrollHeight');
            $(this).closest("fieldset").css("min-height", formHeight + "px");
        });
    };

	// TODO Move into View Handler
    var formInfosManager = function () {

        var $element = $("section.donation-amount");
        if ($element.length) {
            if ($(window).width() < 1200) {
                $("#overview").on("click", ".wrap-field.completed .wrap-input,  .wrap-field.invalid .wrap-input", function (e) {
                    e.preventDefault();
                    $(this).closest(".wrap-field").toggleClass("opened");
                    $(this).toggleClass("opened");
                    $(this).next(".info-text-bottom").toggleClass("opened");
                });
            }
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
            } else if (type == "button") {
                offset = 15;
            } else {
                offset = +380;
            }
            var elemBottom = elemTop + $(this).height() + offset;
            //console.log("wTop " + windowScrollTopView + " wB " + windowBottomView  + " eTop" + elemTop);
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
                    $('.fixed-button').addClass('active');
                    $('.menu-main').addClass('under-bar');

                    if ($('.footer').isVisible('top')) {
                        $('.menu-main').removeClass('under-bar');
                    } else {
                        $(".state-bar").addClass('active');
                        $('.menu-main').addClass('under-bar');
                    }

                    if ($('#submit-bottom').isVisible('button')) {
                        $('.fixed-button').removeClass('active');
                    } else {
                        $('.fixed-button').addClass('active');
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
                if ($('.page-donation').length) {
                    var initialTop = 200;
                } else if ($('.page-membership').length) {
                    var initialTop = 650;
                }


                if (currentScroll >= initialTop) {
                    $('.state-overview .wrap-bar').addClass('fixed');
                    //console.log("wrap bar" + currentScroll);
                } else {
                    $('.state-overview .wrap-bar').removeClass('fixed');
                }


                if (($('.state-bar-lateral .wrap-bar').outerHeight() + $('.state-bar-lateral .wrap-bar').offset().top ) > ( $('.form-shadow-wrap').offset().top + $('.form-shadow-wrap').outerHeight() + 150)) {
                    $('.state-bar-lateral').removeClass('active');
                } else {
                    $('.state-bar-lateral').addClass('active');
                }
            });
        }

        // TODO Include this only on mebership pages
        if ($(".page-membership").length) {
            var memberTypeSection = $("#membership-type").offset().top;
            $(window).scroll(function () {
                var currentScroll = $(window).scrollTop();
                var typeMemberElements = $('.state-overview .member-type');
                var donorElements = $('.state-overview .donor-type');
                var amountElements = $('.state-overview .amount');
                var paymentElemnts = $('.state-overview .payment-method');

                typeMemberElements.removeClass('enabled');
                donorElements.removeClass('enabled');
                amountElements.removeClass('enabled');
                paymentElemnts.removeClass('enabled');
                ACTIVE_THRESHOLD = 60;
                if (currentScroll >= donationPaymentSection - ACTIVE_THRESHOLD) {
                    paymentElemnts.addClass('enabled');
                }
                else if (currentScroll >= donationSection - ACTIVE_THRESHOLD) {
                    amountElements.addClass('enabled');
                }
                else if (currentScroll >= donationTypeSection - ACTIVE_THRESHOLD) {
                    donorElements.addClass('enabled');
                }
                else if (currentScroll >= memberTypeSection - ACTIVE_THRESHOLD) {
                    typeMemberElements.addClass('enabled');
                }
            });
        } else {
            // TODO include only on donation pages?
            $(window).scroll(function () {
                var currentScroll = $(window).scrollTop();
                var amountElements = $('.state-overview .amount');
                var paymentElemnts = $('.state-overview .payment-method');
                var donorElements = $('.state-overview .donor-type');

                amountElements.removeClass('enabled');
                paymentElemnts.removeClass('enabled');
                donorElements.removeClass('enabled');
                if (currentScroll >= donationTypeSection - ACTIVE_THRESHOLD) {
                    donorElements.addClass('enabled');
                }
                else if (currentScroll >= donationPaymentSection - ACTIVE_THRESHOLD) {
                    paymentElemnts.addClass('enabled');
                }
                else if (currentScroll >= donationSection - ACTIVE_THRESHOLD) {
                    amountElements.addClass('enabled');
                }
            });
        }

    };

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
        form.submit(submitValidation);
        form.find('input[type="submit"]').click(submitValidation);

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
