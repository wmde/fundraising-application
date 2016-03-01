var formInitialized = false;

$(function() {
	/* country-specific validation patterns for zip codes */
	var countrySpecifics = {
		generic: {
			'post-code': {
				pattern: '{1,}',
				placeholder: 'z. B. 10117',
				title: 'Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.com'
			}
		},
		DE: {
			'post-code': {
				pattern: '\\s*[0-9]{5}\\s*',
				placeholder: 'z. B. 10117',
				title: 'Fünfstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.de'
			}
		},
		AT: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 4020',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Linz'
			},
			email: {
				placeholder: 'z. B. name@domain.at'
			}
		},
		CH: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 3556',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Trub'
			},
			email: {
				placeholder: 'z. B. name@domain.ch'
			}
		}
	};

  $(document).ready( function () {
    if ( $( ".amount-custom :text" ).val() !== "" ) {
      $( ".display-amount" ).text( $( ".amount-custom :text" ).val() );
    }

    if ( ($( '#membership-type-2' ).length > 0) && $( "#membership-type-2" ).is(':checked')) {
      $( "#address-type-2" ).parent().hide();
      $( "#address-type-1" ).trigger( 'click' );
    }

    /* slide toggle */
    function initSlideToggle() {
      $( 'a.slide-toggle').click(function( e ) {
        var $toggle = $(this);

        if ($toggle.hasClass('active')) {
          $($toggle.attr('data-slide-rel'))
            .removeClass('opened')
            .slideUp( 600, checkInvisibleInput )
            .animate(
              { opacity: 0 },
              { queue: false, duration: 600 }
            );

          $toggle.removeClass('active');
        } else {
          $($toggle.attr('data-slide-rel'))
            .addClass('opened')
            .slideDown( 600, checkInvisibleInput )
            .animate(
              { opacity: 1 },
              { queue: false, duration: 600 }
            );

          $toggle.addClass('active');
        }

        e.preventDefault();
      });
    }


    /* check invisible input elems */
    function checkInvisibleInput() {
      // remove required attribute for hidden inputs
      $(':input.required:hidden').removeAttr('required');
      $(':input.required:visible').prop('required',true);
    }


    /* tab toggle */
    function initTabToggle() {
      $( 'a.tab-toggle').click(function( e ) {

        $($(this).attr('data-tab-group-rel')).find('.tab').addClass('no-display');
        $($(this).attr('data-tab-rel')).removeClass('no-display');

        checkInvisibleInput();

        e.preventDefault();
      });
    }


    /* tooltip */
    function initToolTip() {
      /* tooltip */
      $('.tooltip').tooltip({ position: { my: "right-15 center", at: "left center" } });
      $('.tooltip').click(function(e){
        e.preventDefault();
      });
    }


      /* wlightbox inline */
      function initWLightbox() {
          $('.rtcol, footer').each(function(){
              var $container = $(this);

              if ($container.find('a.wlightbox').length < 1) return true;

              $container.addClass('temp-show-for-js-calculating');

              $container.find('a.wlightbox').each(function(){
                  var element = $( this );
                  element.wlightbox(
                      getLightboxOptions(
                          element, isFooterElement( element )
                      )
                  );
                  $( this ).on('click',
                      function(){
                          triggerLightboxPiwikTrack( element )
                      }
                  );
              });

              $container.removeClass('temp-show-for-js-calculating')
          });
      }

      function triggerLightboxPiwikTrack( $lBoxLink ) {
          var lightboxCode = $lBoxLink.attr( 'data-href' ).replace('#', '');
          var piwikImgUrl = 'https://tracking.wikimedia.de/piwik.php?idsite=1&url=https://spenden.wikimedia.de/lightbox-clicked/' + lightboxCode + '&rec=1';

          $lBoxLink.prepend( '<img src="' + piwikImgUrl + '" width="0" height="0" border="0" />' );
      }

	function getLightboxOptions( $lBoxLink, isFooterElement ) {
		var elementOptions = $lBoxLink.data( 'wlightbox-options' ),
			options;
		if( isFooterElement ) {
			options = getOptionsForFooterLink( $lBoxLink );
		} else {
			options = getOptionsForSidebarLink( $lBoxLink );
		}
		if ( typeof elementOptions !== 'undefined' ) {
			$.extend( options, elementOptions );
		}
		return options;
	}

	function getOptionsForFooterLink( $lBoxLink ) {
		var $containerWLightbox = $('#main > .container');

		return {
			container: $containerWLightbox,
			top: '150px',
			left: '128px',
			maxWidth: '686px'
		};
	}

	function getOptionsForSidebarLink( $lBoxLink ) {
		var $containerWLightbox = $('#main > .container');

		return {
			container: $containerWLightbox,
			top: 0,
			left: '128px',
			maxWidth: '686px'
		};
	}

	/* determine whether the lightbox link is a child of the footer bar */
	function isFooterElement( $lBoxLink ) {
		return $( '#footer' ).has( $lBoxLink ).length > 0;
	}


    /* radio button toggle */
    function initRadioBtnToggle() {
      $( ':radio' ).change( function( e ){
        var slides = [];
        $(':radio.slide-toggle').each(function(){
          slides.push($(this).attr('data-slide-rel'));
        });

        $(':radio.slide-toggle').each(function(){
          var $slide = $($(this).attr('data-slide-rel'));

          if ($(this).is(':checked') == !$(this).hasClass('slide-toggle-invert')) {

            // show child if child is slide in another slide, prevent flickering / blopping slide children
            $.each(slides, function(index, item){
              if ($slide.has($(item)).length > 0 && $(':radio[data-slide-rel="' + item + '"]').is(':checked') && !$slide.hasClass('opened')) {
                $(item).stop().clearQueue().show().removeAttr('style');
              }
            });

            // open
            $slide
              .addClass('opened')
              .slideDown( 600, checkInvisibleInput )
              .animate(
                { opacity: 1 },
                { queue: false, duration: 600 }
              );
          } else {

            //close
            $slide
              .removeClass('opened')
              .slideUp( 600, checkInvisibleInput )
              .animate(
                { opacity: 0 },
                { queue: false, duration: 600 }
              );
          }

        });

        $( ':radio.tab-toggle').each(function(){
          if ($(this).is(':checked')) {
            $($(this).attr('data-tab-rel')).removeClass('no-display');
          } else {
            $($(this).attr('data-tab-rel'))
              .addClass('no-display');
          }

          checkInvisibleInput();
        });
        $( ':radio.tab-toggle:checked').each(function(){
          $($(this).attr('data-tab-rel'))
            .removeClass('no-display');

          checkInvisibleInput();
        });

      });

    }


    /* styled select boxes */
    function initStyledSelect() {
      $('select').selectmenu({
        positionOptions: {
          collision: 'none'
        }
      })
      .on('change', function(evt, params) {
        var $option = $(this).find('[value="' + $(this).find('option:selected').val() + '"]');

        if ($option.attr('data-behavior') == 'placeholder') {
          $('#' + $(this).attr('id') + '-button').addClass('placeholder');
        } else {
          $('#' + $(this).attr('id') + '-button').removeClass('placeholder');
        }
      })
      .change();

      // adjust position, margins & dimension
      $('.ui-selectmenu').each(function(){
        var newWidth = $(this).width() * 2 - $(this).outerWidth();
        $(this).width(newWidth);
      });
      $('.ui-selectmenu-menu').each(function(){
        var $dropDown = $(this).find('.ui-selectmenu-menu-dropdown');
        $dropDown.width($dropDown.width() - 2);
      });
    }

	  $( '#country' ).selectmenu({ change: function() {
		  var countryCode = 'generic';
		  if( countrySpecifics[$( this ).val()] ) {
			  countryCode = $( this ).val();
		  }

		  $.each( countrySpecifics[countryCode], function( id, values ) {
			  var $field = $( '#' + id );
			  $.each( values, function( key, value ) {
				  $field.attr( key, value );
			  });
		  });
	  }} );


    /* ajax form */
    function initAjaxForm() {
      $('form.ajax-form').each( function(){
        var $form = $(this);

        $form.ajaxForm({
          error: function(){
            $form.find('.message').remove();

            $form.append('<div class="message error">Die Nachricht konnte auf Grund eines Fehlers nicht verschickt werden!</div>');
          },
          success: function(e){
            $form.find('.message').remove();

            $form.append('<div class="message success">Vielen Dank! Die Nachricht wurde verschickt!</div>');
          }
        });
      });
    }



    /* amount-list */
    $('.amount-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        $('.display-amount').text($container.find(':radio:checked').val());
      });

      $container.find('.amount-custom :text').on('load change keyup paste focus', function() {
        var val = $.trim($(this).val());
        if (val == '') val = 0;
        //val = isNaN(parseInt(val)) ? 0 : parseInt(val);
        $('.display-amount').text(val);
      });
    });


    /* personal data */
    $('#personal-data').each(function(){
      var $container = $(this);

      $container.find( ':radio' ).change( function( e ){
        // check #address-type-3 enable or disable #send-information
        $('#send-information').prop('disabled', $('#address-type-3').is(':checked'));
      });
    });


    /* donation-payment */
    $('#donation-payment').each(function(){
      var $container = $(this);

      /* change title and show related content */
      $container.find('.payment-type-list :radio' ).change( function( e ){

        $container.find('.section-title .h2').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');

        $container.find('.tab-group .payment-type .tab').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');
      });
    });


    /* become-member-submit */
    $('#become-member-submit').each(function(){
      var $container = $(this);

      /* change title and show related content */
      $container.find('a.button.slide-toggle' ).click(function() {
        $container.find('.box-footer').addClass('border-top');
      });
    });


  $( '.tooltip-track' ).hover( function() {
    $this = $( this );
    var tooltipTitle = $this.attr( 'data-title' );
    if( tooltipTitle !== '' ) {
      var url = 'https://tracking.wikimedia.de/piwik.php?idsite=1&url=https://spenden.wikimedia.de/tooltip/' + tooltipTitle + '&rec=1';
      if( $this.parent().find( 'img[data-title=' + tooltipTitle + ']' ).length === 0 ) {
        $this.parent().append( '<img src="' + url + '" data-title="' + tooltipTitle + '" width="0" height="0" border="0" />' );
      }
    }
  } );

    /* iOS fix - label onclick, see http://stackoverflow.com/questions/7358781/tapping-on-label-in-mobile-safari */
    if ( navigator.userAgent.match( /Safari/ ) ) {
      $( 'label' ).click( function( evt ) {
          evt.stopPropagation();
      } );
    }


    initSlideToggle();
    initTabToggle();
    initToolTip();
    initWLightbox();
    initRadioBtnToggle();
    initStyledSelect();
    //initAjaxForm();

    formInitialized = true;
  });

//additional methods for form controlling

  $( ".interval-radio" ).click( function() {
    $( "#interval-hidden" ).val( $( "input[name='recurring']:checked" ).val() );
  });

  $( "#periode-1" ).click( function() {
    $( "#interval-hidden" ).val( "0" );
  });

  if( !$( "#periode-2" ).attr( "checked" ) ) $( "#periode-1" ).trigger( "click" );

	$( ".radio-payment" ).change( function(e) {
		if ( e.target.checked ) {
			switch ( $( this ).val() ) {
				case "UEB":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:ueberweisung" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "" );
					break;
				case "BEZ":
					$( "#donFormSubmit" ).html( 'Weiter um Spende abzuschließen <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:einzug" );
                  $( "input[name='form']" ).attr( "value", $( '#sepaConfirmForm' ).val() || "10h16_Confirm" );
					$( "#address-type-3" ).parent().hide();
					$( "#tooltip-icon-addresstype" ).hide();
					$( "#val-iframe" ).val( "" );
					break;
				case "PPL":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:paypal" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "" );
					break;
				case "MCP":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:micropayment-i" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "micropayment-iframe" );
					break;
		}
	}
});


  $( document.commentForm ).on( "submit", function( event ) {
    event.preventDefault();
    var url = "../ajax.php?module=action&action=addComment";
    $.ajax( url, {
        data: $( this ).serialize(),
        dataType: "json",
        type: "POST",
        error: function(e){
          $( "#feedback" ).find('.message').remove();
          $( "#feedback" ).append('<div id="negative-feedback" class="message error">Die Nachricht konnte auf Grund eines Fehlers nicht verschickt werden!</div>');
        },
        success: function( response ) {
          $( "#feedback" ).find('.message').remove();
          $( "#feedback" ).append('<div id="positive-feedback" class="message success">' + response.message + '</div>');
        }
    });
  });

  /* trigger hidden membership fee on custom field */
  $('#amount-8').change(function() {
    $('#amount-custom').trigger( 'click' );
  });

  $('#periode-1').click(function() {
    $('#periode-2-list').find(':radio:checked').attr( 'checked', false );
  });

  $('#periode-2').click(function() {
    $('#periode-2-1').trigger( 'click' );
	$('#periode-2-1').attr( 'checked', 'checked' );
	$( "#interval-hidden" ).val( $( "input[name='recurring']:checked" ).val() );
  });

  $( "#donForm" ).bind("reset", function() {
    $( "span.validation" ).each( function() {
      $( this ).removeClass('icon-bug icon-ok');
      $( this ).addClass('icon-placeholder');
      $( '#bank-name' ).text( '' );
    });
    $( "input.invalid, input.valid" ).each( function() {
      $( this ).removeClass('invalid valid');
    });
  });

    /* periode-1 */
    $('#periode-1').change(function(e){
      if ( e.target.checked ) {
        $('.interval-radio').prop('checked', false);
        $('#interval-display').text($( "label[for='periode-1']" ).text());
      }
    });

    /* periode-2-list */
    $('.periode-2-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( e.target.checked ) {
          $('#interval-display').text($( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });

    /* periode-2-list */
    $('.payment-type-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( $container.find( ':radio:checked' ).length > 0 ) {
            $('#payment-display').text(" per " + $( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });


  $( "#address-type-1" ).click( function() {
    clearFields( $( '#company-name' ) );
  });

  $( "#address-type-2" ).click( function() {
    clearFields( $( '#first-name, #last-name' ) );
  });

  $( "#address-type-3" ).click( function( e ) {
    clearFields( $( '#street, #first-name, #last-name, #company-name, #post-code, #email, #city' ) );
    $( "#email" ).get( 0 ).setCustomValidity( "" );
  });

  // unset firma on adresstype person
  $( "#address-type-1" ).click( function( e ) {
    if ( $( "#salutation-3" ).length > 0 && e.target.checked ) {
      $( "#salutation-3" ).prop('checked', false);
    }
  });

  /* trigger hidden company salutation on adresstype company */
	$( "input:radio[name=membership_fee_interval]" ).on( 'change', function( evt ) {
		checkMembershipFeeFields();
	});

  $( "#address-type-2" ).click( function( e ) {
    if ( $( "#salutation-3" ).length > 0 && e.target.checked ) {
      $( "#salutation-3" ).trigger( 'click' );
    }
    /* disable amounts less than 100 euros for institutional/corporate members */
    if ( $( '#become-member' ).length > 0 ) {
		checkMembershipFeeFields();
    }
  });
  $( "#address-type-1" ).click( function( e ) {
    if ( $( '#become-member' ).length > 0 ) {
		checkMembershipFeeFields();
    }
  });
  if ( $( '#become-member' ).length > 0 && $( "#address-type-2" ).attr( "checked" ) === "checked" ) {
    $( "#amount-1, #amount-2, #amount-3" ).attr( 'disabled', "disabled" );
  }
  if ( $( '#membership-type-2' ).length > 0 ) {
	  $( "#membership-type-2" ).click( function( e ) {
		  $( "#address-type-2" ).parent().hide();
		  $( "#address-type-1" ).trigger( 'click' );
	  });
	  $( "#membership-type-1" ).click( function( e ) {
		  $( "#address-type-2" ).parent().show();
	  });
  }

	/* initially slide down payment options if params are missing */
	if( $( location ).attr( 'search' ).indexOf( 'expPayOpts=true' ) > 0 ) {
		$('.periode-2-list' ).parent()
			.addClass( 'opened' )
			.slideDown( 100 )
			.animate( { opacity: 1 }, { queue: false, duration: 100 } );
	}
});

var clearFields = function( $formFields ) {
  $formFields.val( '' ).removeClass( 'invalid' );
  $formFields.next().removeClass( 'icon-bug' ).removeClass( 'icon-ok' ).addClass( 'icon-placeholder' );
};

function getMembershipMinFee() {
	var feeInterval = 12 / parseInt( $( "input:radio[name=membership_fee_interval]:checked" ).val() );
	var minFee = 24;

	if ( $( "input:radio[name=adresstyp]:checked" ).val() === 'firma' ) {
		minFee = 100;
	}
	return minFee /= feeInterval;
}

function checkMembershipFeeFields() {
	var $feeRadios = $( "input:radio[name=membership_fee]" );
	for ( var i = 0; i < $feeRadios.length; i++ ) {
		if( parseInt($feeRadios.get( i ).value) < getMembershipMinFee() ) {
			$($feeRadios.get( i )).attr( 'disabled', 'disabled' );
			$($feeRadios.get( i )).prop( 'checked', false );
		} else {
			$($feeRadios.get( i )).attr( 'disabled', false );
		}
	}
}