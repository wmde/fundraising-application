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
      WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
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
        bankNameDisplayElement: $( '#bank-name' )
      } ),

		WMDE.Components.createTextComponent( store, $( '#date-of-birth' ), 'dateOfBirth' ),

		// fill hidden form fields with values to match backend
		WMDE.Components.createTextComponent( store, $( 'input[name="account_number"]' ), 'accountNumber' ),
		WMDE.Components.createTextComponent( store, $( 'input[name="bank_code"]' ), 'bankCode' ),

		WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt' ), 'donationReceipt' ),
		WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt-company' ), 'donationReceipt' )
    ],
    store,
    'membershipFormContent'
  );

  WMDE.StoreUpdates.connectValidatorsToStore(
    function ( initialValues ) {
      return [
				WMDE.ValidationDispatchers.createFeeValidationDispatcher(
					WMDE.FormValidation.createFeeValidator(
						initData.data( 'validate-fee-url' ),
						WMDE.IntegerCurrency.createCurrencyFormatter( 'de' )
					),
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
		{
			viewHandler: WMDE.View.createFeeOptionSwitcher(
				[
					$( '#amount1' ),
					$( '#amount2' ),
					$( '#amount3' ),
					$( '#amount4' ),
					$( '#amount5' ),
					$( '#amount6' ),
					$( '#amount7' ),
					$( '#amount8' )
				],
				{ // minimum annual amount in cents
					person: 2400,
					firma: 10000
				}
			),
			stateKey: 'membershipFormContent'
		},
		{
			viewHandler: WMDE.View.createShySubmitButtonHandler( $( 'form input[type="submit"]' ) ),
			stateKey: [ WMDE.StateAggregation.Membership.allValiditySectionsAreValid ]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createMembershipTypeSectionInfo(
				$( '.state-bar-lateral .member-type, .state-bar-detailed .member-type' ),
				{
					'sustaining': 'icon-favorite',
					'active': 'icon-flash_on'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#type-membership .wrap-input' ) ),
				{ 'sustaining': '', 'active': '' }
			),
			stateKey: [
				'membershipFormContent.membershipType',
				WMDE.StateAggregation.Membership.membershipTypeIsValid
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createMembershipTypeSectionInfo(
				$( '.state-bar .member-type' ),
				{
					'sustaining': 'icon-favorite',
					'active': 'icon-flash_on'
				},
				WMDE.FormDataExtractor.mapFromRadioLabelsShort( $( '#type-membership .wrap-input' ) ),
				{ 'sustaining': '', 'active': '' }
			),
			stateKey: [
				'membershipFormContent.membershipType',
				WMDE.StateAggregation.Membership.membershipTypeIsValid
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createDonorTypeSectionInfo(
				$( '.state-bar-lateral .donor-type, .state-bar-detailed .donor-type' ),
				{
					'person': 'icon-account_circle',
					'firma': 'icon-work'
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
				WMDE.StateAggregation.Membership.donorTypeAndAddressAreValid
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createDonorTypeSectionInfo(
				$( '.state-bar .donor-type' ),
				{
					'person': 'icon-account_circle',
					'firma': 'icon-work'
				},
				WMDE.FormDataExtractor.mapFromRadioLabelsShort( $( '#type-donor .wrap-input' ) ),
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
				WMDE.StateAggregation.Membership.donorTypeAndAddressAreValid
			]
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
				WMDE.StateAggregation.Membership.amountAndFrequencyAreValid
			]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createPaymentTypeSectionInfo(
				$( '.payment-method' ),
				{
					'PPL': 'icon-payment-paypal',
					'MCP': 'icon-payment-credit_card',
					'BEZ': 'icon-payment-debit',
					'UEB': 'icon-payment-transfer',
					'SUB': 'icon-payment-sofort'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#payment-method .wrap-input' ) ),
				WMDE.FormDataExtractor.mapFromRadioInfoTexts( $( '#payment-method .wrap-field' ) )
			),
			stateKey: [
				'membershipFormContent.paymentType',
				'membershipFormContent.iban',
				'membershipFormContent.bic',
				WMDE.StateAggregation.Membership.paymentAndBankDataAreValid
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
      },
		// Show house number warning
		{
			viewHandler: WMDE.View.createSimpleVisibilitySwitcher(
				$( '#street, #adress-company' ).nextAll( '.warning-text' ),
				/^\D+$/
			),
			stateKey: 'membershipFormContent.street'
		}
    ],
    store
  );

	$( 'form' ).on( 'submit', function () {
		return WMDE.StateAggregation.Membership.allValiditySectionsAreValid( store.getState() );
	} );

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