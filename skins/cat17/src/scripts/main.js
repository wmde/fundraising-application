(function ($) {

    var init = function () {
        smoothScroll();

        contactInfo();

        commentForm();

        formInfosManager();

        stateBarMenu();

        heightInfo();

        containersManager()

        openCommentItem();


        // replace select elements
        jcf.replaceAll();

        selectedSelect();

        // TODO Move this into view handler
        $("#amount-typed").on("focus", function() {
           $(this).closest(".wrap-amount-typed").addClass("focused");

        });
        $("#amount-typed").on("focusout", function() {
            $(this).closest(".wrap-amount-typed").removeClass("focused");
        });

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
            $('select#title').children('option.hideme').prop('disabled',true);
            $('select#treatment').children('option.hideme').prop('disabled',true);
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

            $(".show-info input[type=radio]").on("click", function () {

                var id = this.id
                var fieldsetId = $(this).parents("fieldset").prop("id");

                $('fieldset#' + fieldsetId).css("min-height", 0);

                if (fieldsetId != 'type-membership') {
                    $('fieldset#' + fieldsetId + ' .wrap-field').removeClass("selected notselected");
                    $('fieldset#' + fieldsetId + ' .info-text').removeClass("opened");
                    $('[data-info="' + id + '"]').toggleClass("opened");
                    $(this).parents(".wrap-field").toggleClass("selected");
                    $(this).parents(".selected").prevAll('.wrap-field:first').toggleClass("notselected");

                    if ($('[data-info="' + id + '"]').hasClass("opened")) {
                        var formHeight = $('[data-info="' + id + '"]').prop('scrollHeight');
                        $(this).closest(".show-info").css("min-height", formHeight + "px");
                    }
                    ;

                }
            });

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
                if ($('body#donation').length) {
                    var initialTop = 200;
                } else if ($('body#membership').length) {
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
                ACTIVE_THRESHOLD = 60;
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
            // TODO include only on donation pages?
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
            if ($(this).val() == "" || !this.checkValidity()) {
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

    var contactInfo = function () {
        var form = $('#contact-form');
        if (form.length == 0) return;
        form.submit(submitValidation);
        form.find('input[type="submit"]').click(submitValidation);

        form.find('input, textarea').keypress(function () {
            $(this).data('data-entered', true);
        });

        form.find('input, textarea').blur(function () {
            if (!$(this).data('data-entered')) return;

            if ($(this).val() == "" || !this.checkValidity()) {
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
    };

    var commentForm = function () {
        var form = $('#comment-form');
        if (form.length == 0) return;
        form.submit(submitValidation);
        form.find('input[type="submit"]').click(submitValidation);

        form.find('input, textarea').keypress(function () {
            $(this).data('data-entered', true);
        });

        form.find('input, textarea').blur(function () {
            if (!$(this).data('data-entered')) return;

            if ($(this).val() == "" || !this.checkValidity()) {
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
    };

    var smoothScroll = function () {
        $('a[href*="#"]')
            // Remove links that don't actually link to anything
            .not('[href="#"]')
            .not('[href="#0"]')
            .not('.state-overview .wrap-field.completed .wrap-input')
            .click(function (event) {

                if ((!$(this).closest(".wrap-field.completed .wrap-input").length) || ($(window).width() > 1200)) {
                    // On-page links
                    if (
                        location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '')
                        &&
                        location.hostname == this.hostname
                    ) {
                        // Figure out element to scroll to
                        var target = $(this.hash);
                        target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                        console.log("link2");
                        // Does a scroll target exist?
                        if (target.length) {
                            // Only prevent default if animation is actually gonna happen
                            console.log("link3");
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
                } else {
                    event.preventDefault();
                    console.log("link no");
                }
            });
    };

})(jQuery);
