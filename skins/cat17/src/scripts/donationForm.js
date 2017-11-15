$( function () {
  /** global: WMDE */

  var initData = $( '#init-form' ),
    store = WMDE.Store.createDonationStore( WMDE.createInitialStateFromViolatedFields(
        initData.data( 'violatedFields' ),
        initData.data( 'initial-validation-result' ) )
    ),
    actions = WMDE.Actions
    ;

  WMDE.StoreUpdates.connectComponentsToStore(
    [
      WMDE.Components.createAmountComponent( store, $('#amount-typed'), $( '.wrap-amounts input[type="radio"]' ), $( '#amount-hidden' ) ),
      WMDE.Components.createRadioComponent( store, $('input[name="zahlweise"]'), 'paymentType' ),
      WMDE.Components.createPaymentIntervalComponent( store, $('input[name="intervalType"]'), $('input[name="periode"]') ),
      WMDE.Components.createBankDataComponent( store, {
        ibanElement: $( '#iban' ),
        bicElement: $( '#bic' ),
        accountNumberElement: $( '#account-number' ),
        bankCodeElement: $( '#bank-code' ),
        bankNameFieldElement: $( '#field-bank-name' ),
        bankNameDisplayElement: $( '#bank-name' )
      } ),
      WMDE.Components.createRadioComponent( store, $( 'input[name="addressType"]' ), 'addressType' ),
      WMDE.Components.createSelectMenuComponent( store, $( 'select[name="salutation"]' ), 'salutation' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#title' ), 'title' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#first-name' ), 'firstName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#last-name' ), 'lastName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#street' ), 'street' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#adress-company' ), 'street' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#post-code-company' ), 'postcode' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#city-company' ), 'city' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#country-company' ), 'country' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#email' ), 'email' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#email-company' ), 'email' ),
      WMDE.Components.createCheckboxComponent( store, $( '#newsletter' ), 'confirmNewsletter' ),
      WMDE.Components.createCheckboxComponent( store, $( '#newsletter-company' ), 'confirmNewsletter' )
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
      // Show "credit card required" notice for recurrent payments via Paypal
      {
        viewHandler: WMDE.View.createRecurrentPaypalNoticeHandler(
          WMDE.View.Animator.createSlidingElementAnimator( $( '.notice-ppl-recurrent' ) )
        ),
        stateKey: 'donationFormContent'
      },
      {
        viewHandler: WMDE.View.createPaymentSummaryDisplayHandler(
          $( '.interval-text' ),
          $( '.amount .text'),
          $( '.payment-method .text'),
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
          WMDE.CurrencyFormatter.createCurrencyFormatter( 'de' ),
          $('.periodicity-icon'),
          {
            '0': 'icon-unique',
            '1': 'icon-repeat_1',
            '3': 'icon-repeat_3',
            '6': 'icon-repeat_6',
            '12': 'icon-repeat_12'
          },
          $('.payment-icon'),
          {
            'PPL': 'icon-paypal',
            'MCP': 'icon-credit_card2',
            'BEZ': 'icon-SEPA-2',
            'UEB': 'icon-ubeiwsung-1'
          },
          $('.amount .info-detail'),
          {
            '0': 'Ihr Konto wird einmal belastet.',
            '1': 'Ihr Konto wird jeden Monat belastet.<br />Ihre monatliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '3': 'Ihr Konto wird alle drei Monate belastet.<br />Ihre vierteljahrliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '6': 'Ihr Konto wird alle sechs Monate belastet.<br />Ihre halbjahrliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '12': 'Ihr Konto wird jährlich belastet.<br />Ihre jährliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.'
          },
          $('.payment-method .info-detail'),
          {
            'PPL': 'Nach der Möglichkeit der Adressangabe werden Sie zu PayPal weitergeleitet, wo Sie die Spende abschließen müssen.',
            'MCP': 'Nach der Möglichkeit der Adressangabe werden Sie zu unserem Partner Micropayment weitergeleitet, wo Sie Ihre Kreditkarteninformationen eingeben können.',
            'BEZ': 'Ich ermächtige die gemeinnützige Wikimedia Fördergesellschaft mbH (Gläubiger-ID: DE25ZZZ00000448435) Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von der gemeinnützigen Wikimedia Fördergesellschaft mbH auf mein Konto gezogenen Lastschriften einzulösen. <br />Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.',
              // @fixme: This is in English. Find out what this should be in German
            'UEB': 'On the conclusion of the donation process, you will be provided with the Wikimedia bank data so you can transfer the money.'
          },
          $('.address-icon'),
          {
            'person': 'icon-account_circle',
            'firma': 'icon-work',
            'anonym': 'icon-visibility_off'
          },
          $('.donator-type .text'),
          {
            'person': 'Privat',
            'firma': 'Firma',
            'anonym': 'Anonymous'
          },
          $('.donator-type .info-detail'),
          $('.frequency .text'),
          {
            '0': 'Einmalig',
            '1': 'Monatlich',
            '3': 'Vierteljährlich',
            '6': 'Halbjährlich',
            '12': 'Jährlich'
          }
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
				$( '#type-donator' )
			),
			stateKey: 'donationFormContent.addressType'
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
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $('#adress-company') ),
        stateKey: 'donationInputValidation.street'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#post-code' ) ),
        stateKey: 'donationInputValidation.postcode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $('#post-code-company') ),
        stateKey: 'donationInputValidation.postcode'
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
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#city-company' ) ),
        stateKey: 'donationInputValidation.city'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#email' ) ),
        stateKey: 'donationInputValidation.email'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#email-company' ) ),
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
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $('#amount-typed') ),
        stateKey: 'donationInputValidation.amount'
      }
    ],
    store
  );

  // Validity checks for different form parts

  function addressIsValid() {
    var validity = store.getState().validity,
      formContent = store.getState().donationFormContent;
    return formContent.addressType === 'anonym' || (
		// @fixme: Move checking of salutation and title into reducer/store/validator
      validity.address &&
      formContent.salutation != ''
    );
  }

  function bankDataIsValid() {
    var state = store.getState();
	  // @fixme: Move special handling of BEZ into reducer/store/validator
    return state.donationFormContent.paymentType !== 'BEZ' ||
    (
    state.donationInputValidation.bic.dataEntered && state.donationInputValidation.bic.isValid &&
    state.donationInputValidation.iban.dataEntered && state.donationInputValidation.iban.isValid
    ) ||
    (
    state.donationInputValidation.accountNumber.dataEntered && state.donationInputValidation.accountNumber.isValid &&
    state.donationInputValidation.bankCode.dataEntered && state.donationInputValidation.bankCode.isValid
    );
  }

  function formDataIsValid() {
    var validity = store.getState().validity;
    //console.log(validity.paymentData + " " + addressIsValid() + " " + bankDataIsValid());
    return validity.paymentData && addressIsValid() && bankDataIsValid();
  }

  function personalDataPageIsValid() {
    var validity = store.getState().validity;
    return !hasInvalidFields() && paymentDataIsValid() && addressIsValid() && bankDataIsValid();
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
    return currentState.validity.paymentData;
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

// @fixme Compare how the old skin called the handleXXX functions and restore that state. Refactor handleGroupValidations
// @fixme Move checks from handleGroupValidations into store validator
// @fixme Restore Piwik triggers

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

  handleGroupValidations = function () {
    var state = store.getState();

    //1st Group Amount & Periodicity
    var amount = $('.amount'),
      paymentMethod = $('.payment-method'),
      donatorType = $('.donator-type');

    if (state.donationFormContent.paymentIntervalInMonths >= 0) {
      amount.addClass('completed').removeClass('disabled invalid');
      paymentMethod.removeClass('disabled');
      if (state.donationInputValidation.amount.dataEntered && !state.donationInputValidation.amount.isValid) {
        amount.removeClass('completed').addClass('invalid');
        amount.find('.periodicity-icon').removeClass().addClass('periodicity-icon icon-error');
      }
    }
    else {
      paymentMethod.addClass('disabled');
    }

    if (state.donationFormContent.paymentType) {
      paymentMethod.addClass('completed').removeClass('disabled invalid');
      donatorType.removeClass('disabled');
      if (state.donationInputValidation.paymentType.dataEntered && !state.donationInputValidation.paymentType.isValid) {
        paymentMethod.removeClass('completed').addClass('invalid');
        paymentMethod.find('.payment-icon').removeClass().addClass('payment-icon icon-error');
      }
    }
    else {
      donatorType.addClass('disabled');
    }

    if (state.donationFormContent.addressType) {
      donatorType.addClass('completed').removeClass('disabled invalid');
      var validators = state.donationInputValidation;
      if (
        state.donationFormContent.addressType == 'personal' &&
        (
          (validators.email.dataEntered && !validators.email.isValid) ||
          (validators.city.dataEntered && !validators.city.isValid) ||
          (validators.firstName.dataEntered && !validators.firstName.isValid) ||
          (validators.lastName.dataEntered && !validators.lastName.isValid) ||
          (validators.street.dataEntered && !validators.street.isValid) ||
          (validators.postcode.dataEntered && !validators.postcode.isValid) ||
          (validators.salutation.dataEntered && !validators.salutation.isValid) ||
          (validators.firstName.dataEntered && !validators.firstName.isValid)
        )
        ||
        state.donationFormContent.addressType == 'firma' &&
        (
          (validators.companyName.dataEntered && !validators.companyName.isValid) ||
          (validators.firstName.dataEntered && !validators.firstName.isValid) ||
          (validators.email.dataEntered && !validators.email.isValid) ||
          (validators.city.dataEntered && !validators.city.isValid) ||
          (validators.street.dataEntered && !validators.street.isValid) ||
          (validators.postcode.dataEntered && !validators.postcode.isValid)
        )){
        donatorType.removeClass('completed').addClass('invalid');
        donatorType.find('.payment-icon').removeClass().addClass('payment-icon icon-error');
      }
    }


    if (formDataIsValid()) {
      $('form input[type="submit"]').removeClass('btn-unactive');
    }
    else {
      $('form input[type="submit"]').addClass('btn-unactive');
    }
  };

  // connect DOM elements to actions
  // fixme don't use interval, use form events instead? Discuss performance & other implications
  //$( '#continueFormSubmit1' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handlePaymentDataSubmit, store ) );
  $('input').on('click, change', WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handleGroupValidations, store ) );
  setInterval(handleGroupValidations, 1000);

  // fixme move to view handler
  $('input[name="payment-info"]').click(function () {
    if ($(this).val() == 'BEZ') {
      $('#anonymus').parent().addClass('disabled');
    }
    else {
      $('#anonymus').parent().removeClass('disabled');
    }
  });

  $('form').on('submit', function () {
    triggerValidityCheckForPersonalDataPage();
    handleGroupValidations();

    if (formDataIsValid()) {
      return true;
    }
    return false;
  });

  // TODO move to view handler
  $("#amount-typed").on('keypress', function (event) {
    var _element = $(this),
      keyCode = event.keyCode || event.which,
      keysAllowed = [44, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 0, 8, 9, 13];

    if ($.inArray(keyCode, keysAllowed) === -1 && event.ctrlKey === false) {
      event.preventDefault();
    }

    if ((keyCode == 44 || keyCode == 46) && $('#amount-typed').val().indexOf('.') > 0) {
      event.preventDefault();
    }

    if (keyCode == 44) {
      setTimeout(
        function () {
          $('#amount-typed').val(
            $('#amount-typed').val().replace(',','.')
          );
        }, 10);
    }
  });

  // Set initial form values
  var initSetup = initData.data( 'initial-form-values' );
  // backend delivers amount as a german-formatted "float" string
  initSetup.amount = WMDE.CurrencyFormatter.createCurrencyFormatter( 'de' ).parse( initSetup.amount );
  store.dispatch( actions.newInitializeContentAction( initSetup ) );

  var $introBanner = $('.introduction.banner');
  var $introDefault = $('.introduction.default');

  // @todo Check if this are all conditions that would be considered "successful deeplink", warrant the special header
  if (initSetup.amount && initSetup.paymentIntervalInMonths && initSetup.paymentType) {
    $introBanner.removeClass('hidden');
    $introDefault.addClass('hidden');
  }

  // Initialize form pages
  store.dispatch( actions.newAddPageAction( 'payment' ) );
  store.dispatch( actions.newAddPageAction( 'personalData' ) );
  store.dispatch( actions.newAddPageAction( 'bankConfirmation' ) );

  // switch to personal page if payment data is filled in
  if ( paymentDataIsValid() ) {
    store.dispatch( actions.newNextPageAction() );
  }

} );
