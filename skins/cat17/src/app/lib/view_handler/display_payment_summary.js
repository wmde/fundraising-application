'use strict';
var objectAssign = require( 'object-assign' ),
  PaymentSummaryDisplayHandler = {
    intervalElement: null,
    amountElement: null,
    paymentTypeElement: null,
    intervalTranslations: null,
    paymentTypeTranslations: null,
    numberFormatter: null,
    intervalIconElement: null,
    intervalIcons: null,
    paymentIconsElement: null,
    paymentIcons: null,
    periodicityTextElement: null,
    periodicityText: null,
    paymentElement: null,
    paymentText: null,
    addressTypeIconElement: null,
    addressTypeIcon: null,
    addressTypeElement: null,
    addressType: null,
    addressTypeTextElement: null,
    countriesDictionary: {'DE': 'Deutschland', 'AT': 'Österreich', 'CH': 'Schweiz', 'BE': 'Belgien', 'IT': 'Italien', 'LU': 'Luxemburg'},
    intervalTextElement: null,
    intervalText: null,
    memberShipTypeElement: null,
    memberShipType: null,
    memberShipTypeIconElement: null,
    memberShipTypeIcon: null,
    memberShipTypeTextElement: null,
    memberShipTypeText: null,
    update: function ( formContent ) {
      this.intervalElement.text( this.formatPaymentInterval( formContent.paymentIntervalInMonths ) );
      this.intervalTextElement.text( this.intervalText[formContent.paymentIntervalInMonths] );

      var amountFormat = this.numberFormatter.format(formContent.amount);
      this.amountElement.each(function () {
        if (formContent.amount > 0 || $(this).data('display-error') === undefined) {
          $(this).text(amountFormat);
        }
        else {
          $(this).text('Betrag');
        }
      });

      var paymentTypeText = this.formatPaymentType( formContent.paymentType );
      this.paymentTypeElement.each(function () {
        if (formContent.paymentType !== "" || $(this).data('display-error') === undefined) {
          $(this).text(paymentTypeText);
        }
        else {
          $(this).text('Zahlart');
        }
      });

      this.setSummaryIcon(this.intervalIconElement, formContent.paymentIntervalInMonths, this.intervalIcons);
      this.setSummaryIcon(this.paymentIconsElement, formContent.paymentType, this.paymentIcons);
      this.periodicityTextElement.text( this.periodicityText[formContent.paymentIntervalInMonths] );

      var paymentTextFormatted = this.paymentText[formContent.paymentType];
      if (formContent.paymentType == "BEZ") {
        paymentTextFormatted = '<div class="col-lg-6 no-gutter">' + paymentTextFormatted + "</div>";
        paymentTextFormatted = paymentTextFormatted.replace('<br />', '</div><div class="col-lg-6 no-gutter">');

        if (formContent.accountNumber && formContent.bankCode) {
          paymentTextFormatted = "<dl class='bank-info'><div><dt>Kontonummer</dt><dd>"+formContent.accountNumber+"</dd></div>" +
            "<div><dt>Bankleitzahl</dt><dd>"+formContent.bankCode+"</dd></div></dl>" + paymentTextFormatted;
        }
        else if (formContent.iban && formContent.bic) {
          paymentTextFormatted = "<dl class='bank-info'><div><dt>IBAN</dt><dd>"+formContent.iban+"</dd></div>" +
          "<div><dt>BIC</dt><dd>"+formContent.bic+"</dd></div></dl>" + paymentTextFormatted;
        }
      }
      this.paymentElement.html(paymentTextFormatted);
      this.setSummaryIcon(this.addressTypeIconElement, formContent.addressType, this.addressTypeIcon);

      if (formContent.addressType != "") {
        this.addressTypeElement.text(this.addressType[formContent.addressType]);
      }
      else {
        this.addressTypeElement.each(function () {
          if ($(this).data('display-error') === undefined) {
            $(this).text("Daten noch nicht ausgewählt.");
          }
        });
      }

      this.addressTypeTextElement.html(this.getAddressSummaryContent(formContent));

      if (this.memberShipTypeElement) {
        var textMemberShipType = this.memberShipType[formContent.membershipType];
        this.memberShipTypeElement.each(function () {
          if (formContent.membershipType) {
            $(this).text(textMemberShipType);
          }
        });

        this.setSummaryIcon(this.memberShipTypeIconElement, formContent.membershipType, this.memberShipTypeIcon);

        this.memberShipTypeTextElement.text(this.memberShipTypeText[formContent.membershipType]);
      }
    },
    capitalize: function (s) {
      return s[0].toUpperCase() + s.slice(1);
    },
    formatPaymentInterval: function ( paymentIntervalInMonths ) {
      return this.intervalTranslations[ paymentIntervalInMonths ];
    },
    formatPaymentType: function ( paymentType ) {
      return paymentType=="" ? String("Zahlung noch nicht ausgewählt.") : this.paymentTypeTranslations[ paymentType ];
    },
    getAddressSummaryContent: function (formContent) {
      if (formContent.addressType === "person") {
        return (
          formContent.firstName && formContent.lastName ?
            (formContent.salutation ? this.capitalize(formContent.salutation) : "") + " " +
            (formContent.title ? this.capitalize(formContent.title) : "") + " " +
            formContent.firstName + " " + formContent.lastName + "<br />"
            : ""
        ) +
        (formContent.street ? formContent.street + "<br />" : "") +
        (formContent.postcode && formContent.city ? formContent.postcode + " " + formContent.city + "<br />" : "") +
        ( formContent.country ? this.countriesDictionary[formContent.country] + "<br />" : "") +
        formContent.email;
      }
      else if (formContent.addressType === 'firma') {
        return (formContent.companyName ? formContent.companyName + "<br />" : "") +
        (formContent.contactPerson ? formContent.contactPerson + "<br />" : "") +
        (formContent.street ? formContent.street + "<br />" : "") +
        (formContent.postcode && formContent.city ? formContent.postcode + " " + formContent.city + "<br />" : "") +
        ( formContent.country ? this.countriesDictionary[formContent.country] + "<br />" : "") +
        formContent.email;
      }

      return "";
    },
    setSummaryIcon: function (elements, value, iconsDictionary) {
      elements.removeClass('icon-error');
      if (elements.length && elements.get(0).className.split(' ').length > 1) {
        elements.removeClass(elements.get(0).className.split(' ').pop());
      }
      if (iconsDictionary[value] === undefined) {
        elements.each(function() {
          if ($(this).data('display-error') === undefined) {
            $(this).addClass('icon-error');
          }
        });
      }
      else {
        elements.addClass( iconsDictionary[value] );
      }
    }
  };

module.exports = {
  createPaymentSummaryDisplayHandler: function ( intervalElement, amountElement, paymentTypeElement,
                                                 intervalTranslations, paymentTypeTranslations, numberFormatter,
                                                 intervalIconElement, intervalIcons, paymentIconsElement, paymentIcons,
                                                 periodicityTextElement, periodicityText, paymentElement, paymentText,
                                                 addressTypeIconElement, addressTypeIcon, addressTypeElement, addressType,
                                                 addressTypeTextElement, intervalTextElement, intervalText,
                                                 memberShipTypeElement, memberShipType, memberShipTypeIconElement,
                                                 memberShipTypeIcon, memberShipTypeTextElement, memberShipTypeText) {
    return objectAssign( Object.create( PaymentSummaryDisplayHandler ), {
      intervalElement: intervalElement,
      amountElement: amountElement,
      paymentTypeElement: paymentTypeElement,
      intervalTranslations: intervalTranslations,
      paymentTypeTranslations: paymentTypeTranslations,
      numberFormatter: numberFormatter,
      intervalIconElement: intervalIconElement,
      intervalIcons: intervalIcons,
      paymentIconsElement: paymentIconsElement,
      paymentIcons: paymentIcons,
      periodicityTextElement: periodicityTextElement,
      periodicityText: periodicityText,
      paymentElement: paymentElement,
      paymentText: paymentText,
      addressTypeIconElement: addressTypeIconElement,
      addressTypeIcon: addressTypeIcon,
      addressTypeElement: addressTypeElement,
      addressType: addressType,
      addressTypeTextElement: addressTypeTextElement,
      intervalTextElement: intervalTextElement,
      intervalText: intervalText,
      memberShipTypeElement: memberShipTypeElement,
      memberShipType: memberShipType,
      memberShipTypeIconElement: memberShipTypeIconElement,
      memberShipTypeIcon: memberShipTypeIcon,
      memberShipTypeTextElement: memberShipTypeTextElement,
      memberShipTypeText: memberShipTypeText
    } );
  }
};
