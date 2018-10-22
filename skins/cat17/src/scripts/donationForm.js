$( function () {
  /** global: WMDE */

  var initData = $( '#init-form' ),
    store = WMDE.donationStore = WMDE.Store.createDonationStore(),
    actions = WMDE.Actions
    ;

  WMDE.StoreUpdates.connectComponentsToStore(
    [
      WMDE.Components.createAmountComponent(
          store,
          $( '#amount-typed' ),
          $( '.wrap-amounts input[type="radio"]' ),
          $( '#amount-hidden'),
          WMDE.IntegerCurrency.createCurrencyParser( 'de' ),
		  WMDE.IntegerCurrency.createCurrencyFormatter( 'de' )
      ),
		WMDE.Components.createRadioComponent( store, $('input[name="zahlweise"]'), 'paymentType' ),
		WMDE.Components.createRadioComponent( store, $('input[name="periode"]' ), 'paymentIntervalInMonths' ),
		WMDE.Components.createRadioComponent( store, $( 'input[name="addressType"]' ), 'addressType' ),
		WMDE.Components.createSelectMenuComponent( store, $( 'select[name="salutation"]' ), 'salutation' ),
		WMDE.Components.createSelectMenuComponent( store, $( '#title' ), 'title' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ) ),
		WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#adress-company' ), 'street' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#post-code-company' ), 'postcode' ),
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ) ),
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city-company' ), 'city' ) ),
		WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
		WMDE.Components.createSelectMenuComponent( store, $( '#country-company' ), 'country' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#email-company' ), 'email' ),
		WMDE.Components.createCheckboxComponent( store, $( '#newsletter' ), 'confirmNewsletter' ),
		WMDE.Components.createCheckboxComponent( store, $( '#newsletter-company' ), 'confirmNewsletter' ),
		WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt' ), 'donationReceipt' ),
		WMDE.Components.createCheckboxComponent( store, $( '#donation-receipt-company' ), 'donationReceipt' )
    ],
    store,
    'donationFormContent'
  );

  WMDE.StoreUpdates.connectValidatorsToStore(
    function ( initialValues ) {
      return [
        WMDE.ValidationDispatchers.createAmountValidationDispatcher(
          WMDE.FormValidation.createAmountValidator(
              initData.data( 'validate-amount-url' )
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
		// Hide anonymous payment when doing direct debit
		{
			viewHandler: WMDE.View.createElementClassSwitcher( $( '#type-donor .wrap-field.anonym .wrap-input' ), /BEZ/, 'disabled' ),
			stateKey: 'donationFormContent.paymentType'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#recurrence' )
			),
			stateKey: 'donationFormContent.paymentIntervalInMonths'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#donation-payment' )
			),
			stateKey: 'donationFormContent.paymentType'
		},
		{
			viewHandler: WMDE.View.createSuboptionDisplayHandler(
				$( '#type-donor' )
			),
			stateKey: 'donationFormContent.addressType'
		},
		{
			viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-salutation' ) ),
			stateKey: [ WMDE.StateAggregation.Donation.salutationIsValid ]
		},
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-firstname' ) ),
        stateKey: 'donationInputValidation.firstName'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-lastname' ) ),
        stateKey: 'donationInputValidation.lastName'
      },
		{
			viewHandler: WMDE.View.createFieldValueValidityIndicator( $('.field-company') ),
			stateKey: 'donationInputValidation.companyName'
		},
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-street' ) ),
        stateKey: 'donationInputValidation.street'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-postcode' ) ),
        stateKey: 'donationInputValidation.postcode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-city' ) ),
        stateKey: 'donationInputValidation.city'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-email' ) ),
        stateKey: 'donationInputValidation.email'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $('.wrap-amounts') ),
        stateKey: 'donationInputValidation.amount'
      },
		{
			viewHandler: WMDE.View.createCustomAmountField( $('#amount-typed') ),
			stateKey: 'donationInputValidation.amount'
		},
		{
			viewHandler: WMDE.View.createShySubmitButtonHandler( $( 'form input[type="submit"]' ) ),
			stateKey: [ WMDE.StateAggregation.Donation.allValiditySectionsAreValid ]
		},
		{
			viewHandler: WMDE.View.SectionInfo.createFrequencySectionInfo(
				$( '.banner .frequency' ),
				{
					'0': 'icon-unique',
					'1': 'icon-repeat_1',
					'3': 'icon-repeat_3',
					'6': 'icon-repeat_6',
					'12': 'icon-repeat_12'
				},
				WMDE.FormDataExtractor.mapFromRadioLabels( $( '#recurrence .wrap-input' ) ),
				WMDE.FormDataExtractor.mapFromRadioInfoTexts( $( '#recurrence .wrap-field' ) )
			),
			stateKey: [
				'donationFormContent.paymentIntervalInMonths'
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
				'donationFormContent.amount',
				'donationFormContent.paymentIntervalInMonths',
				WMDE.StateAggregation.Donation.amountAndFrequencyAreValid
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
				'donationFormContent.paymentType',
				'donationFormContent.iban',
				'donationFormContent.bic',
				WMDE.StateAggregation.Donation.paymentAndBankDataAreValid
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
				'donationFormContent.addressType',
				'donationFormContent.salutation',
				'donationFormContent.title',
				'donationFormContent.firstName',
				'donationFormContent.lastName',
				'donationFormContent.companyName',
				'donationFormContent.street',
				'donationFormContent.postcode',
				'donationFormContent.city',
				'donationFormContent.country',
				'donationFormContent.email',
				WMDE.StateAggregation.Donation.donorTypeAndAddressAreValid
			]
		},
		// Show house number warning
      {
        viewHandler: WMDE.View.createSimpleVisibilitySwitcher(
          $( '#street, #adress-company' ).nextAll( '.warning-text' ),
          /^\D+$/
        ),
        stateKey: 'donationFormContent.street'
      }
    ],
    store
  );

	$('form').on( 'submit', function () {
		return WMDE.StateAggregation.Donation.allValiditySectionsAreValid( store.getState() );
	} );

  // Set initial form values
  var initSetup = initData.data( 'initial-form-values' );
  // backend delivers amount as a german-formatted "float" string
  initSetup.amount = WMDE.IntegerCurrency.createCurrencyParser( 'de' ).parse( initSetup.amount );
	// this or touch INITIALIZE_VALIDATION again (values identical to default donation_form_content flagged as changed)
	if ( typeof initSetup.paymentType === 'string' && initSetup.paymentType === '' ) {
		delete initSetup.paymentType;
	}
	if ( initSetup.amount === 0 ) {
		delete  initSetup.amount;
	}
  store.dispatch( actions.newInitializeContentAction( initSetup ) );

	// Set initial validation state
	store.dispatch( actions.newInitializeValidationStateAction(
		initData.data( 'violatedFields' ),
		initSetup,
		initData.data( 'initial-validation-result' )
	) );

	// Non-state-changing event behavior

	var scroller = WMDE.Scrolling.createAnimatedScroller( $( '.wrap-header, .state-bar' ) );

	// Scroll to first required element that needs to be filled
	var currentState = store.getState();
	if ( WMDE.StateAggregation.Donation.formIsPrefilled( currentState ).dataEntered ) {
		// We can assume the validity of amount and interval here, so next section is either payment method or personal data
		var nextRequired = currentState.donationFormContent.paymentType === 'BEZ' ? $( '#payment-method' ) : $( '#donation-type .legend:first' ),
			$introBanner = $('.introduction-banner'),
			animationTime = 1;
		$introBanner.insertBefore( nextRequired ).removeClass( 'hidden' );

		scroller.scrollTo( $introBanner, { elementStart: WMDE.Scrolling.ElementStart.MARGIN }, animationTime );
	}

	// Add scroll behaviors to links/input elements

	WMDE.Scrolling.addScrollToLinkAnchors( $( 'a[href^="#"]' ), scroller);
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="periode"]' ), $( '#recurrence' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="addressType"]' ), $( '#type-donor' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="zahlweise"]' ), $( '#donation-payment' ), scroller );

	var bankDataValidator = WMDE.FormValidation.createBankDataValidator(
		initData.data( 'validate-iban-url' ),
		initData.data( 'generate-iban-url' )
	);

	function mapStateToProps( state ) {
		return {
			iban: state.donationFormContent.iban,
			bic: state.donationFormContent.bic,
			isValid: state.validity.bankData !== false,

			// The validator does not come from the store and should be passed
			// in as a prop the initialization code,
			// see https://github.com/nadimtuhin/redux-vue/issues/6
			// and https://phabricator.wikimedia.org/T207493
			bankDataValidator: bankDataValidator
		}
	}

	function mapActionToProps( dispatch ) {
		return {
			changeBankDataValidity( validity ) {
				dispatch( WMDE.Actions.newFinishBankDataValidationAction( validity ) );
			}
		}
	}

	WMDE.Vue.use(WMDE.VueRedux.reduxStorePlugin);
	WMDE.Vue.use(WMDE.VueTranslate);

	WMDE.Vue.locales( {
		'de_DE': JSON.parse( initData.data( 'messages' ) )
	} );

	var ConnectedBankData = WMDE.VueRedux.connect( mapStateToProps, mapActionToProps )( WMDE.BankData );

	new WMDE.Vue( {
		// FIXME Import and create store directly when we no longer use the global variable anywhere else
		store: store,
		render: (h) => h( ConnectedBankData ),
		created() {
			this.$translate.setLang('de_DE');
		}

	} ).$mount( '#bankdata-app' );

} );
