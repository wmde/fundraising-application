$( function () {
  /** global: WMDE */

  var initData = $( '#init-form' ),
	store = WMDE.membershipStore = WMDE.Store.createMembershipStore(),
	scroller = WMDE.Scrolling.createAnimatedScroller( $( '.wrap-header, .state-bar' ) ),
	animationTime = 1,
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
			WMDE.IntegerCurrency.createCurrencyParser( 'de', false ),
			WMDE.IntegerCurrency.createCurrencyFormatter( 'de' )
		),
      WMDE.Components.createRadioComponent( store, $( '#recurrence .wrap-input input' ), 'paymentIntervalInMonths' ),

      WMDE.Components.createRadioComponent( store, $( 'input[name="adresstyp"]' ), 'addressType' ),

		//Personal Data
		WMDE.Components.createSelectMenuComponent( store, $( '#treatment' ), 'salutation' ),
		WMDE.Components.createSelectMenuComponent( store, $( '#title' ), 'title' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#surname' ), 'lastName' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ) ),
		WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),

		//Company Data
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ) ),
		WMDE.Components.createValidatingTextComponent( store, $( '#email-company' ), 'email' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#adress-company' ), 'street' ),
		WMDE.Components.createValidatingTextComponent( store, $( '#post-code-company' ), 'postcode' ),
		WMDE.Components.addEagerChangeBehavior( WMDE.Components.createValidatingTextComponent( store, $( '#city-company' ), 'city' ) ),
		WMDE.Components.createSelectMenuComponent( store, $( '#country-company' ), 'country' ),

		//Payment Data
		WMDE.Components.createRadioComponent( store, $('input[name="payment_type"]'), 'paymentType' ),
		WMDE.Components.createBankDataComponent( store, {
			ibanElement: $( '#iban' ),
			bicElement: $( '#bic' ),
			bankNameFieldElement: $( '#field-bank-name' ),
			bankNameDisplayElement: $( '#bank-name' )
		} ),

		WMDE.Components.createTextComponent( store, $( '#date-of-birth' ), 'dateOfBirth' ),

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
		// Active membership is not an option for companies
		{
			viewHandler: WMDE.View.createElementClassSwitcher( $('#company').parent(), /active/, 'disabled' ),
			stateKey: 'membershipFormContent.membershipType'
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
				WMDE.FormDataExtractor.mapFromRadioInfoTexts( $( '#type-membership .wrap-field' ) )
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
			viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-salutation' ) ),
			stateKey: [ WMDE.StateAggregation.Membership.salutationIsValid ]
		},
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-firstname' ) ),
        stateKey: 'membershipInputValidation.firstName'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-lastname' ) ),
        stateKey: 'membershipInputValidation.lastName'
      },
		{
			viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-company' ) ),
			stateKey: 'membershipInputValidation.companyName'
		},
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-street' ) ),
        stateKey: 'membershipInputValidation.street'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-postcode' ) ),
        stateKey: 'membershipInputValidation.postcode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-city' ) ),
        stateKey: 'membershipInputValidation.city'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-email' ) ),
        stateKey: 'membershipInputValidation.email'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.field-dob' ) ),
        stateKey: 'membershipInputValidation.dateOfBirth'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.wrap-amounts' ) ),
        stateKey: 'membershipInputValidation.amount'
      },
		// Show house number warning
		{
			viewHandler: WMDE.View.createSimpleVisibilitySwitcher(
				$( '#street, #adress-company' ).nextAll( '.warning-text' ),
				/^\D+$/
			),
			stateKey: 'membershipFormContent.street'
		},
		// Adjust height of address form field set
		{
			viewHandler: new WMDE.View.HeightAdjuster( $( '#type-donor' )  ),
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
		}
    ],
    store
  );

	function forceValidateBankData() {
		var state = store.getState();
		if ( state.membershipFormContent.paymentType === 'BEZ' && state.validity.bankData === WMDE.Validity.INCOMPLETE ) {
			store.dispatch(WMDE.Actions.newFinishBankDataValidationAction( { status: WMDE.ValidationStates.ERR } ) );
		}
		return WMDE.Promise.resolve();
	}

	function forceValidateAddressData() {
		var transport = new WMDE.JQueryTransport();
		if ( store.getState().validity.address === WMDE.Validity.INCOMPLETE ) {
			return transport.postData( initData.data( 'validate-address-url' ), store.getState().membershipFormContent ).then(
				function( resp ) {
					store.dispatch( WMDE.Actions.newFinishAddressValidationAction( resp ) );
					store.dispatch( WMDE.Actions.newMarkEmptyFieldsInvalidAction( Object.keys( resp.messages ) ) );
				}
			);
		}
		return WMDE.Promise.resolve();
	}
	$( '.btn-donation' ).on( 'click', function () {
		if ( WMDE.StateAggregation.Membership.allValiditySectionsAreValid( store.getState() ) ) {
			$( 'form' ).submit();
		}
		else if ( WMDE.StateAggregation.Membership.someValiditySectionsAreIncomplete( store.getState() ) ) {
			WMDE.Promise.all( [ forceValidateBankData(), forceValidateAddressData() ] ).then( function() {
				scroller.scrollTo( $( $( '.field-grp.invalid' ).get( 0 ) ), { elementStart: WMDE.Scrolling.ElementStart.MARGIN }, animationTime );
			});
		}
		return false;
	} );

	// Set initial form values
	var initSetup = initData.data( 'initial-form-values' );
	if ( typeof initSetup.amount === 'string' ) {
		initSetup.amount = WMDE.IntegerCurrency.createCurrencyParser( 'de' ).parse( initSetup.amount );
	}
	store.dispatch( actions.newInitializeContentAction( initSetup ) );

	// Set initial validation state
	if ( initSetup.amount === 0 ) {
		delete initSetup.amount;
	}
	store.dispatch( actions.newInitializeValidationStateAction(
		initData.data( 'violatedFields' ),
		initSetup,
		initData.data( 'initial-validation-result' )
	) );

	// Non-state-changing event behavior

	WMDE.Scrolling.addScrollToLinkAnchors( $( 'a[href^="#"]' ), scroller);
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="membership_fee_interval"]' ), $( '#recurrence' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="adresstyp"]' ), $( '#type-donor' ), scroller );
	WMDE.Scrolling.scrollOnSuboptionChange( $( 'input[name="payment_type"]' ), $( '#donation-payment' ), scroller );

	var bankDataValidator = WMDE.FormValidation.createBankDataValidator(
		initData.data( 'validate-iban-url' ),
		initData.data( 'generate-iban-url' )
	);
	function mapStateToProps( state ) {
		return {
			iban: state.membershipFormContent.iban,
			bic: state.membershipFormContent.bic,
			bankName: state.membershipFormContent.bankName,
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