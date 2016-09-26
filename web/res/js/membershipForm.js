$( function () {
	/** global: WMDE */

	var initData = $( '#init-form' ),
		store = WMDE.Store.createMembershipStore(
			WMDE.createInitialStateFromViolatedFields( initData.data( 'violatedFields' ), {} )
		),
		actions = WMDE.Actions;

	WMDE.StoreUpdates.connectComponentsToStore(
		[
			WMDE.Components.createRadioComponent( store, $( '.membership-type-select' ), 'membershipType' ),
			WMDE.Components.createRadioComponent( store, $( '.address-type-select' ), 'addressType' ),
			WMDE.Components.createRadioComponent( store, $( '.salutation-select' ), 'salutation' ),
			WMDE.Components.createRadioComponent( store, $( '#personal-title' ), 'title' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
			WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
			WMDE.Components.createTextComponent( store, $( '#email' ), 'email' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#date-of-birth' ), 'dateOfBirth' ),
			WMDE.Components.createValidatingTextComponent( store, $( '#phone' ), 'phoneNumber' ),
			WMDE.Components.createRadioComponent( store, $( '.payment-period-select' ), 'paymentIntervalInMonths' ),
			WMDE.Components.createAmountComponent( store, $( '.amount-input' ), $( '.amount-select' ), $( '#amount-hidden' ) ),
			WMDE.Components.createBankDataComponent( store, {
				ibanElement: $( '#iban' ),
				bicElement: $( '#bic' ),
				accountNumberElement: $( '#account-number' ),
				bankCodeElement: $( '#bank-code' ),
				bankNameFieldElement: $( '#field-bank-name' ),
				bankNameDisplayElement: $( '#bank-name' ),
				debitTypeElement: $( '.debit-type-select' )
			} ),
			WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirmSepa' ), 'confirmSepa' )
		],
		store,
		'membershipFormContent'
	);

	WMDE.StoreUpdates.connectValidatorsToStore(
		function ( initialValues ) {
			return [
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createFeeValidator( initData.data( 'validate-fee-url' ) ),
					actions.newFinishPaymentDataValidationAction,
					[ 'amount', 'paymentIntervalInMonths', 'addressType' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createAddressValidator(
						initData.data( 'validate-address-url' ),
						WMDE.FormValidation.DefaultRequiredFieldsForAddressType
					),
					actions.newFinishAddressValidationAction,
					[
						'addressType',
						'salutation',
						'title',
						'firstName',
						'lastName',
						'companyName',
						'street',
						'postcode',
						'city',
						'country',
						'email'
					],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createEmailAddressValidator( initData.data( 'validate-email-address-url' ) ),
					actions.newFinishEmailAddressValidationAction,
					[ 'email' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createBankDataValidator(
						initData.data( 'validate-iban-url' ),
						initData.data( 'generate-iban-url' )
					),
					actions.newFinishBankDataValidationAction,
					[ 'iban', 'accountNumber', 'bankCode', 'debitType', 'paymentType' ],
					initialValues
				),
				WMDE.ReduxValidation.createValidationDispatcher(
					WMDE.FormValidation.createSepaConfirmationValidator(),
					actions.newFinishSepaConfirmationValidationAction,
					[ 'confirmSepa' ],
					initialValues
				)
			];
		},
		store,
		initData.data( 'initial-form-values' ),
		'membershipFormContent'
	);

	// Connect view handlers to changes in specific parts in the global state, designated by 'stateKey'
	WMDE.StoreUpdates.connectViewHandlersToStore(
		[
			{
				viewHandler: WMDE.View.createFormPageVisibilityHandler( {
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
					dateOfBirth: 'Geburtsdatum',
					phone: 'Telefonnummer',
					iban: 'IBAN',
					bic: 'BIC',
					confirmSepa: 'SEPA-Lastschrift'
				} ),
				stateKey: 'membershipInputValidation'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.slide-sepa' ), 'sepa' ),
				stateKey: 'membershipFormContent.debitType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.slide-non-sepa' ), 'non-sepa' ),
				stateKey: 'membershipFormContent.debitType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.person-name' ), 'person' ),
				stateKey: 'membershipFormContent.addressType'
			},
			{
				viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.company-name' ), 'firma' ),
				stateKey: 'membershipFormContent.addressType'
			},
			{
				viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#address-type-2' ).parent(), 'sustaining' ),
				stateKey: 'membershipFormContent.membershipType'
			},
			{
				viewHandler: WMDE.View.createFeeOptionSwitcher( [ $( '#amount-1' ), $( '#amount-2' ), $( '#amount-3' ), $( '#amount-4' ), $( '#amount-5' ), $( '#amount-6' ), $( '#amount-7' ) ], { person: 24, firma: 100 } ),
				stateKey: 'membershipFormContent'
			},
			{
				viewHandler: WMDE.View.createPaymentSummaryDisplayHandler(
					$( '#membership-confirm-interval' ),
					$( '#membership-confirm-fee'),
					$( '#membership-payment-type' ), // does not exist yet
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
						'PPL': 'PayPal'
					},
					WMDE.CurrencyFormatter.createCurrencyFormatter( 'de' )
				),
				stateKey: 'membershipFormContent'
			},
			{
				viewHandler: WMDE.View.createDisplayAddressHandler( {
					fullName: $( '#membership-confirm-name' ),
					street: $( '#membership-confirm-street' ),
					postcode: $( '#membership-confirm-postcode' ),
					city: $( '#membership-confirm-city' ),
					country: $( '#membership-confirm-country' ),
					email: $( '#membership-confirm-mail' )
				} ),
				stateKey: 'membershipFormContent'
			},
			{
				viewHandler: WMDE.View.createBankDataDisplayHandler(
					$( '#membership-confirm-iban' ),
					$( '#membership-confirm-bic' ),
					$( '#membership-confirm-bankname' )
				),
				stateKey: 'membershipFormContent'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#first-name' ) ),
				stateKey: 'membershipInputValidation.firstName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#last-name' ) ),
				stateKey: 'membershipInputValidation.lastName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#street' ) ),
				stateKey: 'membershipInputValidation.street'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#post-code' ) ),
				stateKey: 'membershipInputValidation.postcode'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#city' ) ),
				stateKey: 'membershipInputValidation.city'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#email' ) ),
				stateKey: 'membershipInputValidation.email'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#company-name' ) ),
				stateKey: 'membershipInputValidation.companyName'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#date-of-birth' ) ),
				stateKey: 'membershipInputValidation.dateOfBirth'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#phone' ) ),
				stateKey: 'membershipInputValidation.phoneNumber'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#iban' ) ),
				stateKey: 'membershipInputValidation.iban'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bic' ) ),
				stateKey: 'membershipInputValidation.bic'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#account-number' ) ),
				stateKey: 'membershipInputValidation.account'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bank-code' ) ),
				stateKey: 'membershipInputValidation.bankCode'
			},
			{
				viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.amount-input' ) ),
				stateKey: 'membershipInputValidation.amount'
			},
			{
				viewHandler: WMDE.View.createCountrySpecificAttributesHandler( $( '#post-code' ), $( '#city' ), $( '#email' ) ),
				stateKey: 'countrySpecifics'
			}
		],
		store
	);

	// Validity checks for different form parts

	function displayErrorBox() {
		$( '#validation-errors' ).show();
		$( 'html, body' ).animate( { scrollTop: $( '#validation-errors' ).offset().top } );
	}

	function addressIsValid() {
		return store.getState().validity.address;
	}

	function bankDataIsValid() {
		return store.getState().validity.bankData;
	}

	function formDataIsValid() {
		var validity = store.getState().validity;
		return !hasInvalidFields() && validity.paymentData && addressIsValid() && bankDataIsValid();
	}

	function triggerValidityCheckForPersonalDataPage() {
		var formContent = store.getState().membershipFormContent;

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

		if ( !store.getState().validity.amount ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'amount' ] ) );
		}
	}

	function hasInvalidFields() {
		var invalidFields = false;
		$.each( store.getState().membershipInputValidation, function( key, value ) {
			if ( value.isValid === false ) {
				invalidFields = true;
			}
		} );

		return invalidFields;
	}

	function triggerValidityCheckForSepaPage() {
		if ( !store.getState().validity.sepaConfirmation ) {
			store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'confirmSepa' ] ) );
		}
	}

	$( '#continueFormSubmit' ).click( function () {
		if ( formDataIsValid() ) {
			store.dispatch( actions.newNextPageAction() );
			$( 'section#donation-amount, section#donation-sheet' ).hide();
		} else {
			triggerValidityCheckForPersonalDataPage();
			displayErrorBox();
		}
	} );

	$( '.back-button' ).click( function () {
		// TODO check if page is valid
		store.dispatch( actions.newResetFieldValidityAction( [ 'confirmSepa' ] ) );
		store.dispatch( actions.newPreviousPageAction() );
	} );

	$( '#finishFormSubmit' ).click( function () {
		if ( store.getState().validity.sepaConfirmation ) {
			$( '#memForm' ).submit();
		} else {
			triggerValidityCheckForSepaPage();
			displayErrorBox();
		}
	} );

	// Initialize form pages
	store.dispatch( actions.newAddPageAction( 'personalData' ) );
	store.dispatch( actions.newAddPageAction( 'bankConfirmation' ) );

	// Set initial form values
	store.dispatch( actions.newInitializeContentAction( initData.data( 'initial-form-values' ) ) );

} );
