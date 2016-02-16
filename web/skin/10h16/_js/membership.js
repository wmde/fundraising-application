
$(function() {

    /* become-member-submit */
    $('#become-member-submit').each(function () {
        var $container = $(this);

        /* change title and show related content */
        $container.find('a.button.slide-toggle').click(function () {
            $container.find('.box-footer').addClass('border-top');
        });
    });

    $( "#commentForm" ).on( "submit", function( event ) {
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

    $('.amount-list').each(function(){
        if ( $( ".amount-custom :text" ).val() !== "" ) {
            $( ".amount-custom :text" ).trigger( "change" );
        }

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
    });

    /* remove dots from custom amount */
    var customAmount = $( "#amount-8" ).val();
    if ( customAmount && customAmount.indexOf(".") >= 0 ) {
        $( "#amount-8" ).val( customAmount.replace( ".", "" ) );
    }

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

}); //END onload init

function getMembershipMinFee() {
    var feeInterval = 12 / parseInt( $( "input:radio[name=membership_fee_interval]:checked" ).val() );
    var minFee = 24;

    if ( $( "input:radio[name=adresstyp]:checked" ).val() === 'firma' ) {
        minFee = 100;
    }
    return minFee / feeInterval;
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

var clearFields = function( $formFields ) {
    $formFields.val( '' ).removeClass( 'invalid' );
    $formFields.next().removeClass( 'icon-bug' ).removeClass( 'icon-ok' ).addClass( 'icon-placeholder' );
};