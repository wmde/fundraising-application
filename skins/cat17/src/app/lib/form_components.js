'use strict';

var objectAssign = require( 'object-assign' ),
	actions = require( './actions' ),

	INTERVAL_TYPE_ONE_OFF = 0,
	INTERVAL_TYPE_RECURRING = 1,

	createDefaultChangeHandler = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( actions.newChangeContentAction( contentName, evt.target.value ) );
		};
	},

	createRegexValidator = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( actions.newValidateInputAction(
				contentName,
				evt.target.value,
				evt.target.getAttribute( 'data-pattern' )
			) );
		};
	},

	createNoEmptyStringValidator = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( actions.newValidateInputAction(
				contentName,
				evt.target.value
			) );
		};
	},

	RadioComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {
			this.element.val( [ formContent[ this.contentName ] ] ); // Needs to be an array
		}
	},

	CheckboxComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {
			this.element.prop( 'checked', !!formContent[ this.contentName ] ); // !! converts to boolean
		}
	},

	TextComponent = {
		element: null,
		contentName: '',
		onChange: null,
		validator: null,
		render: function ( formContent ) {
			if ( this.element.val() !== formContent[ this.contentName ] ) {
				// Avoid changing value while element is edited by the user
				if ( !this.element.is( ':focus' ) ) {
					this.element.val( formContent[ this.contentName ] );
					this.element.change();
				}
			}
		}
	},

	AmountComponent = {
		inputElement: null,
		selectElement: null,
		hiddenElement: null,
		render: function ( formContent ) {
			// @todo mv into GermanCurrencyFormatter or similar
			var germanFloatFromMixedAmountFormatter = function ( amount ) {
				// @todo Make state always carry float (or money object), not sometimes string
				amount = parseFloat( amount );
				// @todo toFixed is a bad idea™ (https://stackoverflow.com/a/661757)
				amount = amount.toFixed( 2 );
				return amount.replace( '.', ',' );
			};
			this.hiddenElement.val( germanFloatFromMixedAmountFormatter( formContent.amount ) );
			if ( formContent.isCustomAmount ) {
        this.inputElement.parent().addClass('filled');
				this.selectElement.prop( 'checked', false );
				this.inputElement.val( formContent.amount );
			} else {
        this.inputElement.parent().removeClass('filled');
				this.selectElement.val( [ formContent.amount ] );
				this.inputElement.val( '' );
			}
		}
	},

	PaymentIntervalComponent = {
		decisionElement: null,
		paymentIntervalElement: null,
		render: function ( formContent ) {
      if (formContent.paymentIntervalInMonths < 0) {
        return;
      }
			var intervalType = formContent.paymentIntervalInMonths > 0 ?
				INTERVAL_TYPE_RECURRING :
				INTERVAL_TYPE_ONE_OFF;
			this.paymentIntervalElement.val( [ formContent.paymentIntervalInMonths ] );
			this.decisionElement.val( [ intervalType ] );
		}
	},

	BankDataComponent = {
		ibanElement: null,
		bicElement: null,
		accountNumberElement: null,
		bankCodeElement: null,
		bankNameFieldElement: null,
		bankNameDisplayElement: null,
		render: function ( formContent ) {
			this.ibanElement.val( formContent.iban );
			this.bicElement.val( formContent.bic );
			this.accountNumberElement.val( formContent.accountNumber );
			this.bankCodeElement.val( formContent.bankCode );
			this.bankNameFieldElement.val( formContent.bankName );
			this.bankNameDisplayElement.text( formContent.bankName );
		}
	};

module.exports = {

	INTERVAL_TYPE_ONE_OFF: INTERVAL_TYPE_ONE_OFF,
	INTERVAL_TYPE_RECURRING: INTERVAL_TYPE_RECURRING,

	createRadioComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( RadioComponent ), {
			element: element,
			contentName: contentName,
			onChange: createDefaultChangeHandler( store, contentName )
		} );
		element.on( 'change', component.onChange );
		return component;
	},

	createSelectMenuComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( RadioComponent ), {
			element: element,
			contentName: contentName,
			onChange: createDefaultChangeHandler( store, contentName )
		} );
		element.on( 'selectmenuchange, change', component.onChange );
		return component;
	},

	createCheckboxComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( CheckboxComponent ), {
			element: element,
			contentName: contentName,
			onChange: function () {
				store.dispatch( actions.newChangeContentAction( contentName, !!element.prop( 'checked' ) ) );
			}
		} );
		element.on( 'change', component.onChange );
		return component;
	},

	createValidatingCheckboxComponent: function ( store, element, contentName ) {
		var component = objectAssign( this.createCheckboxComponent( store, element, contentName ), {
			validator: createNoEmptyStringValidator( store, contentName )
		} );
		element.on( 'change', component.validator );
		return component;
	},

	createTextComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( TextComponent ), {
			element: element,
			contentName: contentName,
			onChange: createDefaultChangeHandler( store, contentName )
		} );
		element.on( 'change', component.onChange );
		return component;
	},

	createValidatingTextComponent: function ( store, element, contentName ) {
		var component = objectAssign( this.createTextComponent( store, element, contentName ), {
			validator: createRegexValidator( store, contentName )
		} );
		element.on( 'change', component.validator );
		return component;
	},

	createAmountComponent: function ( store, inputElement, selectElement, hiddenElement ) {
		var component = objectAssign( Object.create( AmountComponent ), {
			inputElement: inputElement,
			selectElement: selectElement,
			hiddenElement: hiddenElement
		} );
		inputElement.on( 'change', function ( evt ) {
			store.dispatch( actions.newInputAmountAction( evt.target.value ) );
		} );
		selectElement.on( 'change', function ( evt ) {
			store.dispatch( actions.newSelectAmountAction( evt.target.value ) );
		} );
		return component;
	},

	createPaymentIntervalComponent: function ( store, decisionElement, intervalElement ) {
		var component = objectAssign( Object.create( PaymentIntervalComponent ), {
			decisionElement: decisionElement,
			paymentIntervalElement: intervalElement,
			onChange: createDefaultChangeHandler( store, 'paymentIntervalInMonths' )
		} );
		decisionElement.on( 'change', component.onChange );
		intervalElement.on( 'change', component.onChange );
		return component;
	},

	createBankDataComponent: function ( store, bankDataElements ) {
		var component = objectAssign( Object.create( BankDataComponent ), bankDataElements );
		bankDataElements.ibanElement.on( 'change', createDefaultChangeHandler( store, 'iban' ) );
		bankDataElements.bicElement.on( 'change', createDefaultChangeHandler( store, 'bic' ) );
		bankDataElements.bicElement.on( 'change', createRegexValidator( store, 'bic' ) );
		bankDataElements.accountNumberElement.on( 'change', createDefaultChangeHandler( store, 'accountNumber' ) );
		bankDataElements.bankCodeElement.on( 'change', createDefaultChangeHandler( store, 'bankCode' ) );
		return component;
	}

};
