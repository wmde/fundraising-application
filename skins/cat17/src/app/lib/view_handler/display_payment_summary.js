'use strict';

var objectAssign = require( 'object-assign' ),
	DOM_SELECTORS = {
		data: {
			emtpyText: 'empty-text',
			displayError: 'display-error'
		},
		classes: {
			errorIcon: 'icon-error',
			summaryBankInfo: 'bank-info'
		}
	},
	PaymentSummaryDisplayHandler = {

		/**
		 * Elements showing state to user
		 */
		intervalTextElement: null,
		amountElement: null,
		paymentTypeElement: null,
		intervalIconElement: null,
		paymentIconsElement: null,
		periodicityTextElement: null,
		paymentElement: null,
		addressTypeIconElement: null,
		addressTypeElement: null,
		addressTypeTextElement: null,
		memberShipTypeElement: null,
		memberShipTypeIconElement: null,
		memberShipTypeTextElement: null,

		/**
		 * Dependencies
		 */
		numberFormatter: null,

		/**
		 * Icons
		 */
		intervalIcons: null,
		addressTypeIcon: null,
		paymentIcons: null,
		memberShipTypeIcon: null,

		/**
		 * Translations
		 */
		intervalTranslations: null,
		paymentTypeTranslations: null,
		countryTranslations: null,
		addressType: null,
		periodicityText: null,
		paymentText: null,
		memberShipType: null,
		memberShipTypeText: null,

		update: function ( formContent ) {
			this.updateAmoutIndicators( formContent.amount );

			this.intervalTextElement.text( this.intervalTranslations[ formContent.paymentIntervalInMonths ] );
			this.setSummaryIcon( this.intervalIconElement, formContent.paymentIntervalInMonths, this.intervalIcons );
			this.periodicityTextElement.text( this.periodicityText[ formContent.paymentIntervalInMonths ] );

			this.updatePaymentTypeIndicators( formContent.paymentType );
			this.setSummaryIcon( this.paymentIconsElement, formContent.paymentType, this.paymentIcons );
			this.updatePaymentTypeSummary( formContent );

			this.updateAddressTypeIndicators( formContent.addressType );
			this.setSummaryIcon( this.addressTypeIconElement, formContent.addressType, this.addressTypeIcon );
			this.addressTypeTextElement.html( this.getAddressSummaryContent( formContent ) );

			if( this.memberShipTypeElement ) {
				this.updateMembershipSummary( formContent );
			}
		},
		updateAmoutIndicators: function ( amount ) {
			var self = this,
				$guiElement;

			this.amountElement.each( function () {
				$guiElement = $( this );
				$guiElement.text( amount === 0 ? $guiElement.data( DOM_SELECTORS.data.emtpyText ) : self.numberFormatter.format( amount ) );
			} );
		},
		updatePaymentTypeIndicators: function ( paymentType ) {
			var self = this,
				$guiElement;

			this.paymentTypeElement.each( function () {
				$guiElement = $( this );
				$guiElement.text( paymentType === '' ? $guiElement.data( DOM_SELECTORS.data.emtpyText ) : self.paymentTypeTranslations[ paymentType ] );
			} );
		},
		updateAddressTypeIndicators: function ( addressType ) {
			var self = this,
				$guiElement;

			this.addressTypeElement.each( function () {
				$guiElement = $( this );
				$guiElement.text( addressType === '' ? $guiElement.data( DOM_SELECTORS.data.emtpyText ) : self.addressType[ addressType ] );
			} );
		},
		updatePaymentTypeSummary: function ( state ) {
			this.paymentElement.text( this.paymentText[ state.paymentType ] );

			if( state.paymentType !== 'BEZ' ) {
				return;
			}

			if( state.iban && state.bic ) {
				this.paymentElement.prepend (
					$( '<dl>' ).addClass( DOM_SELECTORS.classes.summaryBankInfo ).append(
						$('<dt>').text( 'IBAN' ),
						$('<dd>').text( state.iban ),
						$('<dt>').text( 'BIC' ),
						$('<dd>').text( state.bic )
					)
				);
			}
		},
		updateMembershipSummary: function ( state ) {
			if( !state.membershipType ) {
				return;
			}

			this.memberShipTypeElement.text( this.memberShipType[ state.membershipType ] );
			this.setSummaryIcon( this.memberShipTypeIconElement, state.membershipType, this.memberShipTypeIcon );
			this.memberShipTypeTextElement.text( this.memberShipTypeText[ state.membershipType ] );
		},
		getAddressSummaryContent: function ( formContent ) {
			if( formContent.addressType === "person" ) {
				// TODO Escape HTML (T180215)
				// TODO Reuse AddressDisplayHandler
				return (
						formContent.firstName && formContent.lastName ?
							formContent.salutation + ' ' + formContent.title + ' ' + formContent.firstName + ' ' + formContent.lastName + "<br />"
							: ''
					) +
					(formContent.street ? formContent.street + "<br />" : "") +
					(formContent.postcode && formContent.city ? formContent.postcode + " " + formContent.city + "<br />" : "") +
					( formContent.country ? this.countryTranslations[ formContent.country ] + "<br />" : "") +
					formContent.email;
			}
			else if( formContent.addressType === 'firma' ) {
				return (formContent.companyName ? formContent.companyName + "<br />" : "") +
					(formContent.street ? formContent.street + "<br />" : "") +
					(formContent.postcode && formContent.city ? formContent.postcode + " " + formContent.city + "<br />" : "") +
					( formContent.country ? this.countryTranslations[ formContent.country ] + "<br />" : "") +
					formContent.email;
			}

			return "";
		},
		setSummaryIcon: function ( elements, value, iconsDictionary ) {
			var icon = iconsDictionary[ value ];

			elements.removeClass( DOM_SELECTORS.classes.errorIcon );

			// determine the (dynamic) class matching the previous value, remove it from all elements
			if( elements.length && elements.get( 0 ).className.split( ' ' ).length > 1 ) {
				elements.removeClass( elements.get( 0 ).className.split( ' ' ).pop() );
			}

			if( icon === undefined ) {
				elements
					// only configured icons are supposed to communicate validation problems
					.filter( function () {
						return $( this ).data( DOM_SELECTORS.data.displayError ) === true;
					} )
					.addClass( DOM_SELECTORS.classes.errorIcon )
			}
			else {
				elements.addClass( icon );
			}
		}
	};

module.exports = {
	createPaymentSummaryDisplayHandler: function ( intervalTextElement, amountElement, paymentTypeElement,
		intervalTranslations, paymentTypeTranslations, numberFormatter,
		intervalIconElement, intervalIcons, paymentIconsElement, paymentIcons,
		periodicityTextElement, periodicityText, paymentElement, paymentText,
		addressTypeIconElement, addressTypeIcon, addressTypeElement, addressType,
		addressTypeTextElement, countryTranslations,
		memberShipTypeElement, memberShipType, memberShipTypeIconElement,
		memberShipTypeIcon, memberShipTypeTextElement, memberShipTypeText ) {
		return objectAssign( Object.create( PaymentSummaryDisplayHandler ), {
			intervalTextElement: intervalTextElement,
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
			countryTranslations: countryTranslations,
			memberShipTypeElement: memberShipTypeElement,
			memberShipType: memberShipType,
			memberShipTypeIconElement: memberShipTypeIconElement,
			memberShipTypeIcon: memberShipTypeIcon,
			memberShipTypeTextElement: memberShipTypeTextElement,
			memberShipTypeText: memberShipTypeText
		} );
	}
};
