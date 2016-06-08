'use strict';
var objectAssign = require( 'object-assign' ),
	actions = require( './actions' ),

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
			this.element.val( formContent[ this.contentName ] );
		}
	},

	CountrySpecificsUpdateTriggerComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {
			this.element.val( [ formContent[ this.contentName ] ] ); // Needs to be an array
		}
	},

	AmountComponent = {
		inputElement: null,
		selectElement: null,
		render: function ( formContent ) {
			this.inputElement.val( formContent.amount || '' );
			if ( formContent.isCustomAmount ) {
				this.selectElement.prop( 'checked', false );
			} else {
				this.selectElement.val( [ formContent.amount ] );
			}
		}
	},

	BankDataComponent = {
		ibanElement: null,
		bicElement: null,
		accountNumberElement: null,
		bankCodeElement: null,
		debitTypeElement: null,
		bankNameFieldElement: null,
		bankNameDisplayElement: null,
		render: function ( formContent ) {
			this.ibanElement.val( formContent.iban );
			this.bicElement.val( formContent.bic );
			this.accountNumberElement.val( formContent.accountNumber );
			this.bankCodeElement.val( formContent.bankCode );
			this.debitTypeElement.val( [ formContent.debitType ] ); // set as array for radio buttons/dropdown field
			this.bankNameFieldElement.val( formContent.bankName );
			this.bankNameDisplayElement.text( formContent.bankName );
		}
	};

module.exports = {
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
		element.on( 'selectmenuchange', component.onChange );
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

	createAmountComponent: function ( store, inputElement, selectElement ) {
		var component = objectAssign( Object.create( AmountComponent ), {
			inputElement: inputElement,
			selectElement: selectElement
		} );
		inputElement.on( 'change', function ( evt ) {
			store.dispatch( actions.newInputAmountAction( evt.target.value ) );
		} );
		selectElement.on( 'change', function ( evt ) {
			store.dispatch( actions.newSelectAmountAction( evt.target.value ) );
		} );
		return component;
	},

	createBankDataComponent: function ( store, bankDataElements ) {
		// TODO check if all elements are passed in
		var component = objectAssign( Object.create( BankDataComponent ), bankDataElements );
		bankDataElements.ibanElement.on( 'change', createDefaultChangeHandler( store, 'iban' ) );
		bankDataElements.bicElement.on( 'change', createDefaultChangeHandler( store, 'bic' ) );
		bankDataElements.accountNumberElement.on( 'change', createDefaultChangeHandler( store, 'accountNumber' ) );
		bankDataElements.bankCodeElement.on( 'change', createDefaultChangeHandler( store, 'bankCode' ) );
		bankDataElements.debitTypeElement.on( 'change', createDefaultChangeHandler( store, 'debitType' ) );
		return component;
	},

	createCountrySpecificsUpdateTriggerComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( CountrySpecificsUpdateTriggerComponent ), {
			element: element,
			contentName: contentName,
			onChange: function ( evt ) {
				store.dispatch( actions.newCountrySpecificsUpdateAction( evt.target.value ) );
			}
		} );
		element.on( 'selectmenuchange', component.onChange );
		return component;
	}

};
