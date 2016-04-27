var bankCheckPending = false;
var mailCheckPending = false;
var memCheckPending = false;

$( document ).ready( function() {
	$( document ).bind( 'change', function( e ) {
		if ( ( $( e.target ).hasClass( "required" ) || $( e.target ).hasClass( "optional" ) )
				&& !$( e.target ).hasClass( "bank-check" ) ) {

			var vIcon = $( e.target ).next();
			if( e.target.validity.valid ){
				$( e.target ).addClass( 'valid' );
        		$( e.target ).removeClass( 'invalid' );
        		vIcon.addClass( 'icon-ok' );
				vIcon.removeClass( 'icon-bug' );
				vIcon.removeClass( 'icon-placeholder' );
      		} else {
        		$( e.target ).addClass( 'invalid' );
        		$( e.target ).removeClass( 'valid' );
        		vIcon.addClass( 'icon-bug' );
        		vIcon.removeClass( 'icon-ok' );
        		vIcon.removeClass( 'icon-placeholder' );
      		}
    	}
  	});

	var $donationForm = $( '#donForm' ),
		$membershipForm = $( '#memForm' ),
		$customAmount = $( '#amount-8' );

	$donationForm.on( 'submit', function( e ) {
		if ( mailCheckPending ) {
			$( '#donFormSubmit' ).trigger( 'click' );
			return false;
		}
	} );
	
	$membershipForm.on( 'submit', function( e ) {
		if ( mailCheckPending || memCheckPending ) {
			$( '#memFormSubmit' ).trigger( 'click' );
			return false;
		}
	} );

	$( '#donFormSubmit, #memFormSubmit' ).on( 'click', function( e ) {
		if ( mailCheckPending ) {
			checkMailAddress( true );
			return false;
		}
	});

	$( '#memFormSubmit' ).on( 'click', function( e ) {
		if ( memCheckPending ) {
			checkMembershipFee( true );
			return false;
		}
	});

	$( "#email" ).on( 'change', function( evt ) {
		mailCheckPending = true;
		checkMailAddress( false );
	});

	if ( inMembershipForm() ) {
		$( "input:radio[name=membership_fee], input:radio[name=membership_fee_interval], input:radio[name=addressType], #amount-8" ).on( 'change', function ( evt ) {
			memCheckPending = true;
			checkMembershipFee( false );
		} );
		$customAmount.on( 'blur', function () {
			if ( !amountSpecified() ) {
				memCheckPending = true;
				checkMembershipFee( false );
			}
		} );
	}

	function checkMailAddress( submit ) {
		var url = "../validate-email?email=" + encodeURIComponent($( "#email" ).val());
		$.getJSON( url, function( response ) {
			var $email = $( "#email" );
			if ( response.status === "OK" ) {
				$email.removeClass( "invalid" ).addClass( "valid" );
				$email.next().removeClass( "icon-bug" ).addClass( "icon-ok" );
				$email.get( 0 ).setCustomValidity( "" );
			} else {
				$email.removeClass( "valid" ).addClass( "invalid" );
				$email.next().removeClass( "icon-ok" ).addClass( "icon-bug" );
				$email.get( 0 ).setCustomValidity( "E-Mail-Adresse nicht korrekt" );
			}
			mailCheckPending = false;
			if ( submit ) {
				$( '#donFormSubmit, #memFormSubmit' ).trigger( "click" );
			}
		});
	}

	function checkMembershipFee( submit ) {
		var $activeFeeField, fee, minFee;
		resetMembershipFeeFields();

		$activeFeeField = $( "input:radio[name=membership_fee]:checked" );
		fee = $activeFeeField.val();
		if ( !fee || fee === 'custom' ) {
			fee = $( ".amount-custom :text" ).val() ? $( ".amount-custom :text" ).val() : 0;
		}

		minFee = getMembershipMinFee();

		if ( parseInt( fee ) < minFee && formInitialized ) {
			if ( $activeFeeField.length > 0 && $activeFeeField.val() != 'custom' ) {
				$activeFeeField.get( 0 ).setCustomValidity( "Der Mindestbetrag beträgt " + minFee + " Euro" );
			} else {
				$customAmount.get( 0 ).setCustomValidity( "Der Mindestbetrag beträgt " + minFee + " Euro" );
			}
		}

		memCheckPending = false;
		if ( submit ) {
			$( '#memFormSubmit' ).trigger( 'click' );
		}
	}

	function resetMembershipFeeFields() {
		var $feeRadios = $( "input:radio[name=membership_fee]" );
		$customAmount.get( 0 ).setCustomValidity( '' );

		for ( var i = 0; i < $feeRadios.length; i++ ) {
			$feeRadios.get( i ).setCustomValidity( "" );
		}
	}

	function amountSpecified() {
		if ( $donationForm.length > 0 ) {
			return $customAmount.val() !== '' || $( 'input:radio[name=betrag_auswahl]' ).is( ':checked' );
		}
		if ( $membershipForm.length > 0 ) {
			return $customAmount.val() !== '' || $( 'input:radio[name=membership_fee]' ).is( ':checked' );
		}
		return false;
	}

	function inDonationForm() {
		return $donationForm.length > 0;
	}

	function inMembershipForm() {
		return $membershipForm.length > 0;
	}

});
