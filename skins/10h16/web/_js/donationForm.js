$( function () {
	/** global: WMDE */

	var initData = $( '#init-form' ),
		store = WMDE.Store.createDonationStore(),
		actions = WMDE.Actions
		;

	WMDE.StoreUpdates.connectComponentsToStore(
		[
			WMDE.Components.createAmountComponent( store, $( '.amount-input' ), $( '.amount-select' ), $( '.amount-hidden' ) ),
			WMDE.Components.createRadioComponent( store, $( '.payment-type-select' ), 'paymentType' ),
			WMDE.Components.createPaymentIntervalComponent( store, $( '.interval-type-select' ), $( '.payment-period-select' ) ),
			WMDE.Components.createBankDataComponent( store, {
				ibanElement: $( '#iban' ),
				bicElement: $( '#bic' ),
				accountNumberElement: $( '#account-number' ),
				bankCodeElement: $( '#bank-code' ),
				bankNameFieldElement: $( '#field-bank-name' ),
				bankNameDisplayElement: $( '#bank-name' ),
				debitTypeElement: $( '.debit-type-select' )
			} ),
			WMDE.Components.createRadioComponent( store, $( '.address-type-select' ), 'addressType' ),
			WMDE.Components.createRadioComponent( store, $( '.salutation' ), 'salutation' ),
			WMDE.Components.createSelectMenuComponent( store, $( '.personal-title' ), 'title' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
			WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
			WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirm_sepa' ), 'confirmSepa' ),
			WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirm_shortterm' ), 'confirmShortTerm' )
		],
		store,
		'donationFormContent'
	);

	WMDE.StoreUpdates.connectValidatorsToStore(
		function ( initialValues ) {
			return [
				WMDE.ValidationDispatchers.createAmountValidationDispatcher(
					WMDE.FormValidation.createAmountValidator( initData.data( 'validate-amount-url' ) ),
					initialValues
				),
				WMDE.ValidationDispatchers.createAddressValidationDispatcher(
					WMDE.FormValidation.createAddressValidator( 
						initData.data( 'validate-address-url' ),
						WMDE.FormValidation.DefaultRequiredFieldsForAddressType
					),
					initialValues
				),
				WMDE.ValidationDispatchers.createEmailValidationDispatcher(
					WMDE.FormValidation.createEmailAddressValidator( initData.data( 'validate-email-address-url' ) ),
					initialValues
				),
				WMDE.ValidationDispatchers.createBankDataValidationDispatcher(
					WMDE.FormValidation.createBankDataValidator(
						initData.data( 'validate-iban-url' ),
						initData.data( 'generate-iban-url' )
					),
					initialValues
				),
				WMDE.ValidationDispatchers.createSepaConfirmationValidationDispatcher(
					WMDE.FormValidation.createSepaConfirmationValidator(),
					initialValues
				)
			];
		},
		store,
		initData.data( 'initial-form-values' ),
		'donationFormContent'
	);

	// Connect view handlers to changes in specific parts in the global state, designated by 'stateKey'
	WMDE.StoreUpdates.connectViewHandlersToStore(
		[
			{
				viewHandler: WMDE.View.createFormPageVisibilityHandler( {
					payment: $( "#paymentPage" ),
					personalData: $( "#personalDataPage" ),
					bankConfirmation: $( '#bankConfirmationPage' )
				} ),
				stateKey: 'formPagination'
			},
			{
				viewHandler: WMDE.View.createErrorBoxHandler( $( '#validation-errors' ), {
					amount: 'Betrag',
					paymentType: 'Zahlungsart',
					salutation: 'Anrede',
					title: 'Titel',
					firstName: 'Vorname',
					lastName: 'Nachname',
					companyName: 'Firma',
					street: 'Straße',
					postcode: 'PLZ',
					city: 'Ort',
					country: 'Land',
					email: 'E-Mail',
					iban: 'IBAN',
					bic: 'BIC',
					accountNumber: 'Kontonummer',
					bankCode: 'Bankleitzahl',
					confirmSepa: 'SEPA-Lastschrift',
					confirmShortTerm: 'SEPA-Informationsfrist'
				} ),
				stateKey: 'donationInputValidation'
			},
			// show payment periods if interval payment is selected
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.periode-2-list' ), /^(1|3|6|12)$/ ),
				stateKey: 'donationFormContent.paymentIntervalInMonths'
			},
			// Show bank data input when doing direct debit
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '#bank-data' ), 'BEZ' ),
				stateKey: 'donationFormContent.paymentType'
			},
			// Show the right submit buttons on page 2, depending on payment type
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#finishFormSubmit2' ), /^MCP|PPL|UEB|SUB/ ),
				stateKey: 'donationFormContent.paymentType'
			},
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#continueFormSubmit2' ), 'BEZ' ),
				stateKey: 'donationFormContent.paymentType'
			},
			// Hide anonymous payment when doing direct debit
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '.anonymous-payment-select, #tooltip-icon-addresstype' ), /^MCP|PPL|UEB|SUB/ ),
				stateKey: 'donationFormContent.paymentType'
			},
			// Switch bank data input between IBAN/BIC and Account Number/Bank code
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.slide-sepa' ), 'sepa' ),
				stateKey: 'donationFormContent.debitType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.slide-non-sepa' ), 'non-sepa' ),
				stateKey: 'donationFormContent.debitType'
			},
			// Show only the right data fields for personal data
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.personal-data-person' ), 'person' ),
				stateKey: 'donationFormContent.addressType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.personal-data-company' ), 'firma' ),
				stateKey: 'donationFormContent.addressType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.personal-data-full, #notice-unsubscribe' ), /firma|person/ ),
				stateKey: 'donationFormContent.addressType'
			},
			// Show notice for anonymous donations
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.notice-anonymous' ), 'anonym' ),
				stateKey: 'donationFormContent.addressType'
			},
			// Show warning when street contains no house number
			{
				viewHandler: WMDE.View.createWarningBox(
					$( '#street-warning-box' ),
					function( fieldValue ) {
						return fieldValue.trim() !== '' && fieldValue.match(/\d+/g) === null;
					}
				),
				stateKey: 'donationFormContent.street'
			},
			{
				viewHandler: WMDE.View.createPaymentSummaryDisplayHandler(
					$( '.interval-text' ),
					$( '.amount-formatted'),
					$( '#payment-display'),
					{
						'0': 'einmalig',
						'1': 'monatlich',
						'3': 'quartalsweise',
						'6': 'halbjährlich',
						'12': 'jährlich'
					},
					{
						'BEZ': 'Lastschrift',
						'UEB': 'Überweisung',
						'MCP': 'Kreditkarte',
						'PPL': 'PayPal',
						'SUB': 'Sofortüberweisung'
					},
					WMDE.CurrencyFormatter.createCurrencyFormatter( 'de' )
				),
				stateKey: 'donationFormContent'
			},
			{
				viewHandler: WMDE.View.createDisplayAddressHandler( {
					fullName: $( '.confirm-name' ),
					street: $( '.confirm-street' ),
					postcode: $( '.confirm-postcode' ),
					city: $( '.confirm-city' ),
					country: $( '.confirm-country' ),
					email: $( '.confirm-email' )
				} ),
				stateKey: 'donationFormContent'
			},
			{
				viewHandler: WMDE.View.createBankDataDisplayHandler(
					$( '.confirm-iban' ),
					$( '.confirm-bic' ),
					$( '.confirm-bank-name' )
				),
				stateKey: 'donationFormContent'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#first-name' ) ),
				stateKey: 'donationInputValidation.firstName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#last-name' ) ),
				stateKey: 'donationInputValidation.lastName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#street' ) ),
				stateKey: 'donationInputValidation.street'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#post-code' ) ),
				stateKey: 'donationInputValidation.postcode'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#city' ) ),
				stateKey: 'donationInputValidation.city'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#email' ) ),
				stateKey: 'donationInputValidation.email'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#company-name' ) ),
				stateKey: 'donationInputValidation.companyName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#iban' ) ),
				stateKey: 'donationInputValidation.iban'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bic' ) ),
				stateKey: 'donationInputValidation.bic'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#account-number' ) ),
				stateKey: 'donationInputValidation.accountNumber'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bank-code' ) ),
				stateKey: 'donationInputValidation.bankCode'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.amount-input' ) ),
				stateKey: 'donationInputValidation.amount'
			}
		],
		store
	);

	// Validity checks for different form parts

	function addressIsValid() {
		var validity = store.getState().validity,
			formContent = store.getState().donationFormContent;
		return formContent.addressType === 'anonym' || validity.address;
	}

	function bankDataIsValid() {
		var validity = store.getState().validity,
			formContent = store.getState().donationFormContent;
		return formContent.paymentType !== 'BEZ' || validity.bankData;
	}

	function personalDataPageIsValid() {
		var validity = store.getState().validity;
		return !hasInvalidFields() && paymentDataIsValid() && addressIsValid() && bankDataIsValid();
	}

	function triggerValidityCheckForPaymentPage() {
		if ( !amountIsValid() && !paymentTypeIsValid() ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'amount', 'paymentType' ] ) );
		}
		else if ( !amountIsValid() ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'amount'] ) );
		}
		else if ( !paymentTypeIsValid() ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'paymentType' ] ))
		}
	}

	function triggerValidityCheckForPersonalDataPage() {
		var formContent = store.getState().donationFormContent;

		if ( !addressIsValid() ) {
			if ( formContent.addressType === 'person' ) {
				store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
					[ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
					[ 'companyName' ]
				) );
			} else if ( formContent.addressType === 'firma' ) {
				store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
					[ 'companyName', 'street', 'postcode', 'city', 'email' ],
					[ 'firstName', 'lastName' ]
				) );
			}
		}

		if ( !bankDataIsValid() ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
				[ 'iban', 'bic' ]
			) );
		}
	}

	function triggerValidityCheckForSepaPage() {
		if ( !store.getState().validity.sepaConfirmation ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
				[ 'confirmSepa', 'confirmShortTerm' ]
			) );
		}
	}

	function hasInvalidFields() {
		var invalidFields = false;
		$.each( store.getState().donationInputValidation, function( key, value ) {
			if ( value.isValid === false ) {
				invalidFields = true;
			}
		} );

		return invalidFields;
	}

	function paymentDataIsValid() {
		var currentState = store.getState();
		return amountIsValid() && paymentTypeIsValid() && currentState.validity.paymentData;
	}

	function amountIsValid() {
		var currentState = store.getState();
		return Number( currentState.donationFormContent.amount.replace(/,/g, '.') ) > 0;
	}

	function paymentTypeIsValid() {
		var currentState = store.getState();
		return currentState.donationFormContent.paymentType;
	}

	function displayErrorBox() {
		$( '#validation-errors' ).show();
		$( 'html, body' ).animate( { scrollTop: $( '#validation-errors' ).offset().top } );
	}

	function triggerPiwikEvent( eventData ) {
		if ( typeof _paq !== 'undefined' ) {
			_paq.push( eventData );
		}
	}

	function handlePaymentDataSubmit() {
		if ( paymentDataIsValid() ) {
			store.dispatch( actions.newNextPageAction() );
			triggerPiwikEvent( [ 'trackGoal', 2 ] );
		} else {
			triggerValidityCheckForPaymentPage();
			displayErrorBox();
		}
	}

	function handlePersonalDataSubmitForDirectDebit() {
		if ( personalDataPageIsValid() ) {
			store.dispatch( actions.newNextPageAction() );
			triggerPiwikEvent( [ 'trackGoal', 4 ] );
		} else {
			triggerValidityCheckForPersonalDataPage();
			displayErrorBox();
		}
	}

	function handlePersonalDataSubmitForNonDirectDebit() {
		if ( personalDataPageIsValid() ) {
			$( '#donForm2' ).submit();
		} else {
			triggerValidityCheckForPersonalDataPage();
			displayErrorBox();
		}
	}

	// connect DOM element events to actions

	$( '#continueFormSubmit1' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handlePaymentDataSubmit, store ) );

	$( '#continueFormSubmit2' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handlePersonalDataSubmitForDirectDebit, store )  );

	$( '#finishFormSubmit2' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handlePersonalDataSubmitForNonDirectDebit, store ) );

	$( '.back-button' ).click( function () {
		store.dispatch( actions.newResetFieldValidityAction( [ 'confirmSepa', 'confirmShortTerm' ] ) );
		store.dispatch( actions.newPreviousPageAction() );
	} );

	$( '#finishFormSubmit3' ).click( function () {
		var validity = store.getState().validity;
		// we use validity directly here because SEPA really needs these values to be valid
		if ( validity.paymentData && validity.address && validity.bankData && validity.sepaConfirmation ) {
			$( '#donForm2' ).submit();
		} else {
			triggerValidityCheckForSepaPage();
			displayErrorBox();
		}
	} );


	$( '#donForm1 .amount-input' ).keypress( function ( evt ) {
		if( evt.which === 13 ) {
			$( '#continueFormSubmit1' ).click();
			evt.preventDefault();
		}
	} );

	// Set initial form values
	store.dispatch( actions.newInitializeContentAction( initData.data( 'initial-form-values' ) ) );

	// Set initial validation state
	store.dispatch( actions.newInitializeValidationStateAction(
		initData.data( 'violatedFields' ),
		initData.data( 'initial-validation-result' )
	) );

	// Initialize form pages
	store.dispatch( actions.newAddPageAction( 'payment' ) );
	store.dispatch( actions.newAddPageAction( 'personalData' ) );
	store.dispatch( actions.newAddPageAction( 'bankConfirmation' ) );

	// switch to personal page if payment data is filled in
	if ( paymentDataIsValid() ) {
		store.dispatch( actions.newNextPageAction() );
	}

} );
