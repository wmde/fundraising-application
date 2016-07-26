$( function () {
	/** global: WMDE */

	var initData = $( '#init-form' ),
		store = WMDE.Store.createDonationStore( WMDE.createInitialStateFromViolatedFields( initData.data( 'violatedFields' ) ) ),
		actions = WMDE.Actions
		;

	WMDE.StoreUpdates.connectComponentsToStore(
		[
			WMDE.Components.createAmountComponent( store, $( '.amount-input' ), $( '.amount-select' ), $( '.amount-hidden' ) ),
			WMDE.Components.createRadioComponent( store, $( '.payment-type-select' ), 'paymentType' ),
			WMDE.Components.createRadioComponent( store, $( '.interval-type-select' ), 'paymentIntervalInMonths' ),
			WMDE.Components.createRadioComponent( store, $( '.payment-period-select' ), 'paymentIntervalInMonths' ),
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
			WMDE.Components.createRadioComponent( store, $( '.personal-title' ), 'title' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
			WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
			WMDE.Components.createTextComponent( store, $( '#email' ), 'email' ),
			WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirm_sepa' ), 'confirmSepa' ),
			WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirm_shortterm' ), 'confirmShortTerm' )
		],
		store,
		'donationFormContent'
	);

	WMDE.StoreUpdates.connectValidatorsToStore(
		function ( initialValues ) {
			return [
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createAmountValidator( initData.data( 'validate-amount-url' ) ),
					actions.newFinishAmountValidationAction,
					[ 'amount', 'paymentType' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createAddressValidator( 
						initData.data( 'validate-address-url' ),
						WMDE.FormValidation.DefaultRequiredFieldsForAddressType
					),
					actions.newFinishAddressValidationAction,
					[ 'addressType', 'salutation', 'title', 'firstName', 'lastName', 'companyName', 'street', 'postcode', 'city', 'country', 'email' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createEmailAddressValidator( initData.data( 'validate-email-address-url' ) ),
					actions.newFinishEmailAddressValidationAction,
					[ 'email' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createBankDataValidator( initData.data( 'validate-iban-url' ), initData.data( 'generate-iban-url' ) ),
					actions.newFinishBankDataValidationAction,
					[ 'iban', 'accountNumber', 'bankCode' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createSepaConfirmationValidator(),
					actions.newFinishSepaConfirmationValidationAction,
					[ 'confirmSepa', 'confirmShortTerm' ],
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
					account: 'Kontonummer',
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
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#finishFormSubmit2' ), /^MCP|PPL|UEB/ ),
				stateKey: 'donationFormContent.paymentType'
			},
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#continueFormSubmit2' ), 'BEZ' ),
				stateKey: 'donationFormContent.paymentType'
			},
			// Hide anonymous payment when doing direct debit
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '.anonymous-payment-select' ), /^MCP|PPL|UEB/ ),
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
			{
				viewHandler: WMDE.View.createPaymentIntervalAndAmountDisplayHandler(
					$( '.interval-text' ),
					$( '.amount-formatted'),
					{
						'0': 'einmalig',
						'1': 'monatlich',
						'3': 'quartalsweise',
						'6': 'halbjährlich',
						'12': 'jährlich'
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
				viewHandler: WMDE.View.createCountrySpecificAttributesHandler( $( '#post-code' ), $( '#city' ), $( '#email' ) ),
				stateKey: 'countrySpecifics'
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
				stateKey: 'donationInputValidation.account'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bank-code' ) ),
				stateKey: 'donationInputValidation.bankCode'
			},
			{
				viewHandler: WMDE.View.createCountrySpecificAttributesHandler( $( '#post-code' ), $( '#city' ), $( '#email' ) ),
				stateKey: 'countrySpecifics'
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
		return !hasInvalidFields() && validity.amount && addressIsValid() && bankDataIsValid();
	}

	function triggerValidityCheckForPaymentPage() {
		if ( !paymentDataIsValid() ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'amount' ] ) );
		}
	}

	function triggerValidityCheckForPersonalDataPage() {
		var formContent = store.getState().donationFormContent;

		if ( !addressIsValid() ) {
			if ( formContent.addressType === 'person' ) {
				store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
					[ 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
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
		return currentState.validity.amount ||
			( currentState.donationFormContent.amount && currentState.donationFormContent.paymentType ) ;
	}

	function displayErrorBox() {
		$( '#validation-errors' ).show();
		$( 'html, body' ).animate( { scrollTop: $( '#validation-errors' ).offset().top } );
	}

	// connect DOM elements to actions

	$( '#continueFormSubmit1' ).click( function () {
		if ( paymentDataIsValid() ) {
			store.dispatch( actions.newNextPageAction() );
		} else {
			triggerValidityCheckForPaymentPage();
			displayErrorBox();
		}
	} );

	$( '#continueFormSubmit2' ).click( function () {
		if ( personalDataPageIsValid() ) {
			store.dispatch( actions.newNextPageAction() );
		} else {
			triggerValidityCheckForPersonalDataPage();
			displayErrorBox();
		}
	} );

	$( '#finishFormSubmit2' ).click( function () {
		if ( personalDataPageIsValid() ) {
			$( '#donForm2' ).submit();
		} else {
			triggerValidityCheckForPersonalDataPage();
			displayErrorBox();
		}
	} );

	$( '.back-button' ).click( function () {
		store.dispatch( actions.newPreviousPageAction() );
	} );

	$( '#finishFormSubmit3' ).click( function () {
		var validity = store.getState().validity;
		if ( validity.amount && validity.address && validity.bankData && validity.sepaConfirmation ) {
			$( '#donForm2' ).submit();
		} else {
			triggerValidityCheckForSepaPage();
			displayErrorBox();
		}
	} );

	// Set initial form values
	store.dispatch( actions.newInitializeContentAction( initData.data( 'initial-form-values' ) ) );

	// Initialize form pages
	store.dispatch( actions.newAddPageAction( 'payment' ) );
	store.dispatch( actions.newAddPageAction( 'personalData' ) );
	store.dispatch( actions.newAddPageAction( 'bankConfirmation' ) );

	// switch to personal page if payment data is filled in
	if ( paymentDataIsValid() ) {
		store.dispatch( actions.newNextPageAction() );
	}

} );
