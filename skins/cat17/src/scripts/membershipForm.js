$( function () {
  /** global: WMDE */

  if ($('body#membership').length == 0) {
    return;
  }

  var initData = $( '#init-form.membership' ),
    store = WMDE.Store.createMembershipStore(
      WMDE.createInitialStateFromViolatedFields( initData.data( 'violatedFields' ), {} )
    ),
    actions = WMDE.Actions;

  WMDE.StoreUpdates.connectComponentsToStore(
    [
      WMDE.Components.createRadioComponent( store, $( 'input[name="intervalType"]' ), 'membershipType' ),
      WMDE.Components.createRadioComponent( store, $( 'input[name="addressType"]' ), 'addressType' ),
      WMDE.Components.createRadioComponent( store, $( '.salutation-select' ), 'salutation' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#personal-title' ), 'title' ),
      WMDE.Components.createValidatingTextComponent( store, $( '.first-name' ), 'firstName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#surname' ), 'lastName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#company-name' ), 'companyName' ),
      WMDE.Components.createValidatingTextComponent( store, $( '.street' ), 'street' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#post-code' ), 'postcode' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#city' ), 'city' ),
      WMDE.Components.createSelectMenuComponent( store, $( '#country' ), 'country' ),
      WMDE.Components.createTextComponent( store, $( '#email' ), 'email' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#date-of-birth' ), 'dateOfBirth' ),
      WMDE.Components.createValidatingTextComponent( store, $( '#phone' ), 'phoneNumber' ),
      WMDE.Components.createRadioComponent( store, $( 'input[name="payment-info"]' ), 'paymentType' ),
      WMDE.Components.createRadioComponent( store, $( 'input[name="periode"]' ), 'paymentIntervalInMonths' ),
      WMDE.Components.createAmountComponent( store, $( '#amount-typed' ), $( 'input[name="amount-grp"]' ), $( '#amount-hidden' ) ),
      WMDE.Components.createBankDataComponent( store, {
        ibanElement: $( '#iban' ),
        bicElement: $( '#bic' ),
        accountNumberElement: $( '#account-number' ),
        bankCodeElement: $( '#bank-code' ),
        bankNameFieldElement: $( '#field-bank-name' ),
        bankNameDisplayElement: $( '#bank-name' ),
        debitTypeElement: $( '.debit-type-select' )
      } ),
      WMDE.Components.createValidatingCheckboxComponent( store, $( '#confirm_sepa' ), 'confirmSepa' ),
      WMDE.Components.createTextComponent( store, $( '#contact-person' ), 'contactPerson' )
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
        ),
        WMDE.ValidationDispatchers.createSepaConfirmationValidationDispatcher(
          WMDE.FormValidation.createSepaConfirmationValidator(),
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
          accountNumber: 'Kontonummer',
          bankCode: 'Bankleitzahl',
          confirmSepa: 'SEPA-Lastschrift'
        } ),
        stateKey: 'membershipInputValidation'
      },
      {
        viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#finishFormSubmit' ), /^PPL$|^$/ ),
        stateKey: 'membershipFormContent.paymentType'
      },
      {
        viewHandler: WMDE.View.createSimpleVisibilitySwitcher( $( '#continueFormSubmit' ), 'BEZ' ),
        stateKey: 'membershipFormContent.paymentType'
      },
      {
        viewHandler: WMDE.View.createRecurrentPaypalNoticeHandler(
          WMDE.View.Animator.createSlidingElementAnimator( $( '.notice-ppl-recurrent' ) )
        ),
        stateKey: 'membershipFormContent'
      },
      {
        viewHandler: WMDE.View.createSlidingVisibilitySwitcher( $( '.fields-direct-debit' ), 'BEZ' ),
        stateKey: 'membershipFormContent.paymentType'
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
            '0': 'Ihr Konto wird Einmal belastet.',
            '1': 'Ihr Konto wird jeden Monat belastet. Ihre monatliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '3': 'Ihr Konto wird alle drei Monate belastet. Ihre monatliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '6': 'Ihr Konto wird jeden Monat belastet. Ihre monatliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.',
            '12': 'Ihr Konto wird jährlich belastet. Ihre jährliche Spende können Sie jederzeit fristlos per E-Mail an spenden@wikimedia.de stornieren.'
          },
          $('.payment-method .info-detail'),
          {
            'PPL': 'Nach der Möglichkeit der Adressangabe werden Sie zu PayPal weitergeleitet, wo Sie die Spende abschließen müssen.',
            'MCP': 'Nach der Möglichkeit der Adressangabe werden Sie zu unserem Partner Micropayment weitergeleitet, wo Sie Ihre Kreditkarteninformationen eingeben können.',
            'BEZ': 'TODO Some text for Lastschrift payment method',
            'UEB': 'IBAN 348720983472938<br />BIC 87668786<br />Ich ermächtige die gemeinnützige Wikimedia Fördergesellschaft mbH (Gläubiger-ID: DE25ZZZ00000448435) Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von der gemeinnützigen Wikimedia Fördergesellschaft mbH auf mein Konto gezogenen Lastschriften einzulösen.<br />Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.'
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
        stateKey: 'membershipInputValidation.accountNumber'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '#bank-code' ) ),
        stateKey: 'membershipInputValidation.bankCode'
      },
      {
        viewHandler: WMDE.View.createFieldValueValidityIndicator( $( '.amount-input' ) ),
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
    return store.getState().membershipFormContent.paymentType !== 'BEZ' || store.getState().validity.bankData;
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

  function triggerValidityCheckForSepaPage() {
    if ( !store.getState().validity.sepaConfirmation ) {
      store.dispatch( actions.newMarkEmptyFieldsInvalidAction( [ 'confirmSepa' ] ) );
    }
  }

  function handleMembershipDataSubmitForDirectDebit() {
    if ( formDataIsValid() ) {
      store.dispatch( actions.newNextPageAction() );
      $( 'section#donation-amount, section#donation-sheet' ).hide();
    } else {
      triggerValidityCheckForPersonalDataPage();
      displayErrorBox();
    }
  }

  function handleMembershipDataSubmitForNonDirectDebit() {
    if ( formDataIsValid() ) {
      $( '#memForm' ).submit();
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

    console.log(state);
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
      donatorType.removeClass('disabled');
      /*if (state.membershipInputValidation.paymentType.dataEntered && !state.membershipInputValidation.paymentType.isValid) {
        paymentMethod.removeClass('completed').addClass('invalid');
        paymentMethod.find('.payment-icon').removeClass().addClass('payment-icon icon-error');
      }*/
    }
    else {
      donatorType.addClass('disabled');
    }

    if (state.membershipFormContent.addressType) {
      donatorType.addClass('completed').removeClass('disabled invalid');
      var validators = state.membershipInputValidation;
      if (
        state.membershipFormContent.addressType == 'personal' &&
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
        state.membershipFormContent.addressType == 'firma' &&
        (
        (validators.contactPerson.dataEntered && !validators.contactPerson.isValid) ||
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


    if (state.validity.paymentData && state.validity.address && (state.membershipFormContent.paymentType != 'BEZ' || state.validity.bankData) ) {
      $('form input[type="submit"]').removeClass('btn-unactive');
    }
    else {
      $('form input[type="submit"]').addClass('btn-unactive');
    }
  };
  $('input').on('click, change', WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handleGroupValidations, store ) );
  handleGroupValidations();

  $( '#continueFormSubmit' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handleMembershipDataSubmitForDirectDebit, store ) );

  $( '#finishFormSubmit' ).click( WMDE.StoreUpdates.makeEventHandlerWaitForAsyncFinish( handleMembershipDataSubmitForNonDirectDebit, store ) );

  $( '.back-button' ).click( function () {
    // TODO check if page is valid
    store.dispatch( actions.newResetFieldValidityAction( [ 'confirmSepa' ] ) );
    store.dispatch( actions.newPreviousPageAction() );
  } );

  $( '#finishFormSubmit2' ).click( function () {
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