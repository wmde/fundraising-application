'use strict';
/*DE">Deutschland</option>
 <option value="AT">Österreich</option>
 <option value="CH">Schweiz</option>
 <option value="BE">Belgien</option>
 <option value="IT">Italien</option>
 <option value="LI">Liechtenstein</option>
 <option value="LU">Luxemburg</option>
 */
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
      this.paymentElement.html(this.paymentText[formContent.paymentType]);
      this.setSummaryIcon(this.addressTypeIconElement, formContent.addressType, this.addressTypeIcon);

      if (formContent.addressType != "") {
        this.addressTypeElement.text(this.addressType[formContent.addressType]);
      }
      else {
        this.addressTypeElement.each(function () {
          if ($(this).data('display-error') === undefined) {
            $(this).text("Betrag noch nicht ausgewählt.");
          }
        });
      }

      this.addressTypeTextElement.html(this.getAddressSummaryContent(formContent));
    },
    formatPaymentInterval: function ( paymentIntervalInMonths ) {
      return this.intervalTranslations[ paymentIntervalInMonths ];
    },
    formatPaymentType: function ( paymentType ) {
      return paymentType=="" ? String("Betrag noch nicht ausgewählt.") : this.paymentTypeTranslations[ paymentType ];
    },
    getAddressSummaryContent: function (formContent) {
      if (formContent.addressType !== "anonym") {
        return (formContent.street ? formContent.street + "<br />" : "") + (formContent.postcode && formContent.city ? formContent.postcode + " " + formContent.city + "<br />" : "")
        + ( formContent.country ? this.countriesDictionary[formContent.country] + "<br />" : "") + formContent.email;
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
                                                 addressTypeTextElement, intervalTextElement, intervalText) {
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
      intervalText: intervalText
    } );
  }
};
