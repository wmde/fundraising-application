$( function () {
  /** global: WMDE */

  var initData = $( '#init-form' ),
	store = WMDE.Store.createMembershipStore(),
    actions = WMDE.Actions;

  WMDE.StoreUpdates.connectComponentsToStore(
    [
      //MemberShipType
      WMDE.Components.createRadioComponent( store, $( 'input[name="membership_type"]' ), 'membershipType' ),

      //Amount and periodicity
		WMDE.Components.createAmountComponent(
			store,
			$( '#amount-typed' ),
			$( '.wrap-amounts input[type="radio"]' ),
			$( '#amount-hidden'),
			WMDE.IntegerCurrency.createCurrencyParser( 'de' ),
			WMDE.IntegerCurrency.createCurrencyFormatter( 'de' )
		),
      WMDE.Components.createRadioComponent( store, $( '#recurrence .wrap-input input' ), 'paymentIntervalInMonths' ),

      //Personal data
      WMDE.Components.createRadioComponent( store, $( 'input[name="adresstyp"]' ), 'addressType' ),
      //Personal Data
      WMDE.Components.createSelectMenuComponent( store, $( '#treatment' ), 'salutation' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#title' ), 'title' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#surname' ), 'lastName' ),
      WMDE.Components.createTextComponent( store, $( '#email' ), 'email' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),

      //Company Data
      WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#email-company' ), 'email' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#adress-company' ), 'street' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#post-code-company' ), 'postcode' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#city-company' ), 'city' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#country-company' ), 'country' ),

      //Payment Data
      WMDE.Components.createRadioComponent( store, $('input[name="payment_type"]'), 'paymentType' ),
      WMDE.Components.createBankDataComponent( store, {
        ibanElement: $( '#iban' ),
        bicElement: $( '#bic' ),
        accountNumberElement: $( '#account-number' ),
        bankCodeElement: $( '#bank-code' ),
        bankNameFieldElement: $( '#field-bank-name' ),
        bankNameDisplayElement: $( '#bank-name' ),
      } ),

		WMDE.Components.createTextComponent( store, $( '#date-of-birth' ), 'dateOfBirth' ),

		// fill hidden form fields with values to match backend
		WMDE.Components.createTextComponent( store, $( 'input[name="account_number"]' ), 'accountNumber' ),
		WMDE.Components.createTextComponent( store, $( 'input[name="bank_code"]' ), 'bankCode' )
    ],
    store,
    'membershipFormContent'
  );

  WMDE.StoreUpdates.connectValidatorsToStore(
    function ( initialValues ) {
      return [
        WMDE.ValidationDispatchers.createFeeValidationDispatcher(
          WMDE.FormValidation.createFeeValidator( initData.data( 'validate-fee-url' ) ),
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
          iban: 'IBAN',
          bic: 'BIC',
          accountNumber: 'Kontonummer',
          bankCode: 'Bankleitzahl'
        } ),
        stateKey: 'membershipInputValidation'
      },
		// Active membership is not an option for companies
		{
			viewHandler: WMDE.View.createElementClassSwitcher( $('#company').parent(), /active/, 'disabled' ),
			stateKey: 'membershipFormContent.membershipType'
		},
		{
			viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#payment-method .info-text .info-recurrent' ), /^(1|3|6|12)/ ),
			stateKey: 'membershipFormContent.paymentIntervalInMonths'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#type-donor' )
			),
			stateKey: 'membershipFormContent.addressType'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#recurrence' )
			),
			stateKey: 'membershipFormContent.paymentIntervalInMonths'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#payment-method' )
			),
			stateKey: 'membershipFormContent.paymentType'
		},

		// todo verify FeeOptionSwitcher does its thing
      {
        viewHandler: WMDE.View.createFeeOptionSwitcher( [ $( '#amount-1' ), $( '#amount-2' ), $( '#amount-3' ), $( '#amount-4' ), $( '#amount-5' ), $( '#amount-6' ), $( '#amount-7' ) ], { person: 24, firma: 100 } ),
        stateKey: 'membershipFormContent'
      },

		{
			viewHandler: WMDE.View.createShySubmitButtonHandler( $( 'form input[type="submit"]' ) ),
			stateKey: [ WMDE.StateAggregation.allValiditySectionsAreValid ]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createAmountFrequencySectionInfo(
				$( '.amount' ),
				{
					'0': 'icon-unique',
					'1': 'icon-repeat_1',
					'3': 'icon-repeat_3',
					'6': 'icon-repeat_6',
					'12': 'icon-repeat_12'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#recurrence .wrap-input' ) ),
				WMDE.FormDataExtractor.mapFromRadioInfoTexts( $( '#recurrence .wrap-field' ) ),
				WMDE.IntegerCurrency.createCurrencyFormatter( 'de' )
			),
			stateKey: [
				'membershipFormContent.amount',
				'membershipFormContent.paymentIntervalInMonths',
				//WMDE.StateAggregation.amountAndFrequencyAreValid
				function () { return { isValid: false, dataEntered: false } }
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createPaymentTypeSectionInfo(
				$( '.payment-method' ),
				{
					'PPL': 'icon-paypal',
					'MCP': 'icon-credit_card2',
					'BEZ': 'icon-SEPA-2',
					'UEB': 'icon-ubeiwsung-1',
					'SUB': 'icon-TODO' // @todo Find icon for SUB
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#payment-method .wrap-input' ) ),
				WMDE.FormDataExtractor.mapFromRadioInfoTexts( $( '#payment-method .wrap-field' ) )
			),
			stateKey: [
				'membershipFormContent.paymentType',
				'membershipFormContent.iban',
				'membershipFormContent.bic',
				//WMDE.StateAggregation.paymentAndBankDataAreValid
				function () { return { isValid: false, dataEntered: false } }
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createDonorTypeSectionInfo(
				$( '.donor-type' ),
				{
					'person': 'icon-account_circle',
					'firma': 'icon-work',
					'anonym': 'icon-visibility_off'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#type-donor .wrap-input' ) ),
				WMDE.FormDataExtractor.mapFromSelectOptions( $( '#country' ) )
			),
			stateKey: [
				'membershipFormContent.addressType',
				'membershipFormContent.salutation',
				'membershipFormContent.title',
				'membershipFormContent.firstName',
				'membershipFormContent.lastName',
				'membershipFormContent.companyName',
				'membershipFormContent.street',
				'membershipFormContent.postcode',
				'membershipFormContent.city',
				'membershipFormContent.country',
				'membershipFormContent.email',
				//WMDE.StateAggregation.donorTypeAndAddressAreValid
				function () { return { isValid: false, dataEntered: false } }
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createMembershipTypeSectionInfo(
				$( '.member-type' ),
				{
					'sustaining': 'icon-favorite',
					'active': 'icon-flash_on'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#type-membership .wrap-input' ) ),
				{ 'sustaining': '', 'active': '' }
			),
			stateKey: [
				'membershipFormContent.membershipType'
			]
		},
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#first-name' ) ),
        stateKey: 'membershipInputValidation.firstName'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#surname' ) ),
        stateKey: 'membershipInputValidation.lastName'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.street' ) ),
        stateKey: 'membershipInputValidation.street'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.post-code' ) ),
        stateKey: 'membershipInputValidation.postcode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.city' ) ),
        stateKey: 'membershipInputValidation.city'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.email' ) ),
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
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#iban' ) ),
        stateKey: 'membershipInputValidation.iban'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bic' ) ),
        stateKey: 'membershipInputValidation.bic'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#account-number' ) ),
        stateKey: 'membershipInputValidation.accountNumber'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bank-code' ) ),
        stateKey: 'membershipInputValidation.bankCode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#amount-typed' ) ),
        stateKey: 'membershipInputValidation.amount'
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
    var state = store.getState();
    // fixme move check to store/validator
    return state.membershipFormContent.paymentType !== 'BEZ' ||
    (
      state.membershipInputValidation.bic.dataEntered && state.membershipInputValidation.bic.isValid &&
      state.membershipInputValidation.iban.dataEntered && state.membershipInputValidation.iban.isValid
    ) ||
    (
      state.membershipInputValidation.accountNumber.dataEntered && state.membershipInputValidation.accountNumber.isValid &&
      state.membershipInputValidation.bankCode.dataEntered && state.membershipInputValidation.bankCode.isValid
    );
  }

  function formDataIsValid() {
    var validity = store.getState().validity;
    var state = store.getState();
    //console.log(validity.paymentData + " " + addressIsValid() + " " + bankDataIsValid());
    return validity.paymentData && state.membershipFormContent.membershipType && addressIsValid() && bankDataIsValid();
  }


  function triggerValidityCheckForPersonalDataPage() {
    var formContent = store.getState().membershipFormContent;

    if ( !addressIsValid() ) {
      if ( formContent.addressType === 'person' ) {
        store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
          [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
          [ 'companyName' ]
        ) );
      } else if ( formContent.addressType === 'firma' ) {
        store.dispatch( actions.newMarkEmptyFieldsInvalidAction(
          [ 'companyName', 'street', 'postcode', 'city', 'email' ],
          [ 'firstName', 'lastName', 'salutation' ]
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
// fixme Move validation to store again, handle class changes in view handlers
  handleGroupValidations = function () {
    var state = store.getState();

    //1st Group Amount & Periodicity
    var memberType = $('.member-type'),
      amount = $('.amount'),
      paymentMethod = $('.payment-method'),
      donorType = $('.donor-type');

    if (state.membershipFormContent.membershipType) {
      memberType.addClass('completed').removeClass('disabled invalid');
      donorType.removeClass('disabled');
    }

    if (state.membershipFormContent.paymentIntervalInMonths >= 0) {
      amount.addClass('completed').removeClass('disabled invalid');
      paymentMethod.removeClass('disabled');
      if (state.membershipInputValidation.amount.dataEntered && !state.membershipInputValidation.amount.isValid) {
        amount.removeClass('completed').addClass('invalid');
        amount.find('.periodicity-icon').removeClass().addClass('periodicity-icon icon-error');
      }
    }
    else {
      paymentMethod.addClass('disabled');
    }

    if (state.membershipFormContent.paymentType) {
      paymentMethod.addClass('completed').removeClass('disabled invalid');
      if (
        (
        state.membershipFormContent.debitType == 'sepa' &&
        state.membershipInputValidation.iban.dataEntered && !state.membershipInputValidation.iban.isValid ||
        state.membershipInputValidation.bic.dataEntered && !state.membershipInputValidation.bic.isValid
        )
        ||
        (state.membershipFormContent.debitType == 'non-sepa' &&
        state.membershipInputValidation.bankCode.dataEntered && !state.membershipInputValidation.bankCode.isValid ||
        state.membershipInputValidation.accountNumber.dataEntered && !state.membershipInputValidation.accountNumber.isValid
        )
      ){
        paymentMethod.addClass('invalid');
        paymentMethod.find('.payment-icon').removeClass().addClass('payment-icon icon-error');
      }
      else {
        paymentMethod.removeClass('invalid');
      }
    }
    else {
      donorType.addClass('disabled');
    }

    if (state.membershipFormContent.addressType) {
      donorType.addClass('completed').removeClass('disabled invalid');
      var validators = state.membershipInputValidation;
      if (
        state.membershipFormContent.addressType == 'person' &&
        (
        (validators.email.dataEntered && !validators.email.isValid) ||
		(validators.dateOfBirth.dataEntered && !validators.dateOfBirth.isValid) ||
        (validators.city.dataEntered && !validators.city.isValid) ||
        (validators.firstName.dataEntered && !validators.firstName.isValid) ||
        (validators.lastName.dataEntered && !validators.lastName.isValid) ||
        (validators.street.dataEntered && !validators.street.isValid) ||
        (validators.postcode.dataEntered && !validators.postcode.isValid) ||
        (validators.salutation.dataEntered && !validators.salutation.isValid) ||
        (validators.firstName.dataEntered && !validators.firstName.isValid)
        )
        ||
        state.membershipFormContent.addressType == 'firma' &&
        (
        (validators.companyName.dataEntered && !validators.companyName.isValid) ||
        (validators.firstName.dataEntered && !validators.firstName.isValid) ||
        (validators.email.dataEntered && !validators.email.isValid) ||
        (validators.city.dataEntered && !validators.city.isValid) ||
        (validators.street.dataEntered && !validators.street.isValid) ||
        (validators.postcode.dataEntered && !validators.postcode.isValid)
        )){
        donorType.addClass('invalid');
        donorType.find('.payment-icon').removeClass().addClass('payment-icon icon-error');
      }
    }
  };
  $('input').on('click, change', WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handleGroupValidations, store ) );
  setInterval(handleGroupValidations, 1000);

  $('form').on('submit', function () {
    triggerValidityCheckForPersonalDataPage();
    handleGroupValidations();

    if (formDataIsValid()) {
      return true;
    }
    return false;
  });

	// Set initial form values
	var initSetup = initData.data( 'initial-form-values' );
	if ( typeof initSetup.amount === 'string' ) {
		// backend delivers amount as a german-formatted "float" string
		initSetup.amount = WMDE.IntegerCurrency.createCurrencyParser( 'de' ).parse( initSetup.amount );
	}
	store.dispatch( actions.newInitializeContentAction( initSetup ) );

	// Set initial validation state
	store.dispatch( actions.newInitializeValidationStateAction(
		initData.data( 'violatedFields' ),
		{} // membership form has no pages and does not get validation group information
	) );

	// Non-state-changing event behavior

	// TODO Test if the scrolling behaviors still work, the following lines were just copy-pasted from donation
	var scroller = WMDE.Scrolling.createAnimatedScroller( $( '.wrap-header, .state-bar' ) );
	WMDE.Scrolling.addScrollToLinkAnchors( $( 'a[href*="#"]' ), scroller);
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="periode"]' ), $( '#recurrence' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="addressType"]' ), $( '#type-donor' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="paymentType"]' ), $( '#donation-payment' ), scroller );

} );