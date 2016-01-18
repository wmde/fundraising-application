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
		if ( bankCheckPending || mailCheckPending ) {
			$( '#donFormSubmit' ).trigger( 'click' );
			return false;
		}
		if ( !amountSpecified() ) {
			$customAmount.get( 0 ).setCustomValidity( "Der Mindestbetrag beträgt 1 Euro" );
			return false;
		}
	} );
	$membershipForm.on( 'submit', function( e ) {
		if ( bankCheckPending || mailCheckPending || memCheckPending ) {
			$( '#memFormSubmit' ).trigger( 'click' );
			return false;
		}
		if ( !amountSpecified() ) {
			checkMembershipFee( false );
			return false;
		}
	} );

	$( '#donFormSubmit, #memFormSubmit' ).on( 'click', function( e ) {
		if ( mailCheckPending ) {
			checkMailAddress( true );
			return false;
		}
		if ( bankCheckPending ) {
			checkBankData( true );
			return false;
		}
	});

	$( '#memFormSubmit' ).on( 'click', function( e ) {
		if ( memCheckPending ) {
			checkMembershipFee( true );
			return false;
		}
	});

	if ( inDonationForm()) {
		$( 'input[name=betrag_auswahl], #amount-8' ).on( 'change', function() {
			$customAmount.get( 0 ).setCustomValidity( '' );
		} );
		$customAmount.on( 'blur', function() {
			if ( !amountSpecified() ) {
				$customAmount.get( 0 ).setCustomValidity( 'Der Mindestbetrag beträgt 1 Euro' );
			}
		} );
	}

	$( '#payment-type-1, #payment-type-2, #payment-type-4' ).on( 'click', function() {
		$( 'section#donation-payment' ).find( 'input[type=text]' ).each( function() {
			$( this )[0].setCustomValidity( "" );
			$( this ).val( "" );
		} );
	} );

	$( ".iban-check, .bank-check" ).on( 'change', function( evt ) {
		checkBankData( false );
	});

    $( "#bic" ).on( 'change', function( evt ) {
        evt.target.value = cleanAccountData(evt.target.value, true);
    });

	$( "#email" ).on( 'change', function( evt ) {
		mailCheckPending = true;
		checkMailAddress( false );
	});

	var $iban = $( "#iban" );
	if ( $iban.val() && $iban.val().length > 0 ) {
		$iban.trigger( 'change' );
		$( "#debit-type-1" ).trigger( 'click' );
	}

	if ( inMembershipForm() ) {
		$( "input:radio[name=membership_fee], input:radio[name=membership_fee_interval], input:radio[name=adresstyp], #amount-8" ).on( 'change', function ( evt ) {
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

	function checkBankData( submit ) {
		bankCheckPending = true;
		var url = '';

		if ( $( '#debit-type-2' ).is( ':checked' ) ) {
            var accNumElm = $( '#account-number' );
            var bankCodeElm = $( '#bank-code' );

			if( accNumElm.val() === '' || bankCodeElm.val() === '' ) {
				return;
			}

            accNumElm.val( cleanAccountData( accNumElm.val(), false ) );
            bankCodeElm.val( cleanAccountData( bankCodeElm.val(), false ) );

			url = "../generate-iban?bankCode=" + bankCodeElm.val() + "&accountNumber=" + accNumElm.val();
			$( '#iban, #bic' ).val( '' );
			$( '#bank-name' ).text( '' );
		} else {
            $( '#iban' ).val( cleanAccountData( $( '#iban' ).val(), true) );

			url = "../check-iban?iban=" + $( "#iban" ).val();
			$( '#account-number, #bank-code' ).val( '' );
			$( '#bank-name' ).text( '' );
		}

		$.getJSON( url, function( data ) {
			if ( data.status === "OK" ) {
				$( '#iban' ).val( data.iban ? data.iban : '' );
				$( '#bank-name' ).text( data.bankName ? data.bankName : '' );
				$( '#field-bank-name' ).val( data.bankName ? data.bankName : '' );
				$( '#account-number' ).val( data.account ? data.account : '' );
				$( '#bank-code' ).val( data.bankCode ? data.bankCode : '' );

				setFieldsValid( $( '#bank-code, #account-number, #iban' ) );

				var $bic = $( '#bic' );
				if( $bic.hasClass( 'invalid' ) || data.bic ) {
					$bic.val( data.bic );
					setFieldsValid( $bic );
				} else if( !$bic.hasClass( 'valid' ) ) {
					$( "#bic" ).next().removeClass( "icon-bug icon-ok" );
				}
			} else {
				var $bankFields = $( "#bank-code, #account-number" );
				$bankFields.removeClass( "valid" ).addClass( "invalid" );
				$bankFields.next().removeClass( "icon-ok icon-placeholder" ).addClass( "icon-bug" );
				if( $( "#non-sepa" ).css( 'display' ) === 'block' ) {
					$bankFields.each( function( index, elmId ) {
						$( elmId )[0].setCustomValidity( "Die angegebene Bankverbindung ist nicht korrekt." );
					} );
				} else {
					$( "#bic, #iban" ).each( function( index, elmId ) {
						$( elmId )[0].setCustomValidity( "Die angegebene Bankverbindung ist nicht korrekt." );
					} );
				}
			}
			bankCheckPending = false;

			if( submit ) {
				$( '#donFormSubmit, #memFormSubmit' ).trigger( 'click' );
			}
		});
	}

    function cleanAccountData( data, isIBAN ) {
        data = data.toString();
        if ( isIBAN ) {
            data = data.toUpperCase();
            return data.replace( /[^0-9A-Z]/g, "" );
        } else {
            return data.replace( /[^0-9]/g, "" );
        }
    }

	function setFieldsValid( $fields ) {
		$fields.removeClass( "invalid" ).addClass( "valid" );
		$fields.next().removeClass( "icon-bug icon-placeholder" ).addClass( "icon-ok" );
		$fields.each( function( index, elmId ) {
			$( elmId )[0].setCustomValidity( "" );
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
