'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	actions = require( './actions' ),
	NumericInputHandler = require( './numeric_input_handler' ),

	/**
	 * Wrapper around underscore debounce function with a delay of 300 milliseconds
	 *
	 * @param {Function} f
	 * @param {number} milliseconds
	 * @return {Function}
	 */
	defaultDebounce = function ( f, milliseconds ) {
		return _.debounce( f, milliseconds || 300 );
	},

	createDefaultChangeHandler = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( actions.newChangeContentAction( contentName, evt.target.value ) );
		};
	},

	createRegexValidator = function ( store, contentName ) {
		return function ( evt ) {
			var fieldIsOptional = false;
			if ( evt.target.hasAttribute( 'data-optional' ) ) {
				fieldIsOptional = JSON.parse( evt.target.getAttribute( 'data-optional' ) );
			}
			store.dispatch( actions.newValidateInputAction(
				contentName,
				evt.target.value,
				evt.target.getAttribute( 'data-pattern' ),
				fieldIsOptional
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
			this.element.on( 'focus', function () {
				jQuery( this ).addClass( 'focused' );
			} );
			this.element.on( 'focusout', function () {
				jQuery( '.focused' ).removeClass( 'focused' );
			} );
		}
	},

	SelectComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {

			if ( this.element.val() === formContent[ this.contentName ] || this.elementAndContentAreEmpty( formContent ) ) {
				return;
			}

			this.element.val( [ formContent[ this.contentName ] ] );
			this.element.change();
		},
		elementAndContentAreEmpty: function ( formContent ) {
			// calling this.element.val( '' ) leads to this.element.val() === null in some cases,
			// so we need to compare different types
			return this.element.val() === null && formContent[ this.contentName ] === '';
		}
	},

	CheckboxComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {
			this.element.prop( 'checked', !!formContent[ this.contentName ] ); // !! converts to boolean
			this.element.on( 'focus', function () {
				jQuery( this ).parent().find( 'label' ).addClass( 'focused' );
			} );
			this.element.on( 'focusout', function () {
				jQuery( '.focused' ).removeClass( 'focused' );
			} );
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
		numberFormatter: null,
		render: function ( formContent ) {
			this.hiddenElement.val( this.numberFormatter.format( formContent.amount ) );
			if ( formContent.isCustomAmount ) {
				this.inputElement.parent().addClass( 'filled' );
				this.selectElement.prop( 'checked', false );
				this.inputElement.val( this.numberFormatter.format( formContent.amount ) );
			} else {
				this.inputElement.parent().removeClass( 'filled' );
				this.selectElement.val( [ String( formContent.amount ) ] );
				this.inputElement.val( '' );
			}
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
		var component = objectAssign( Object.create( SelectComponent ), {
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

	/**
	 * @param {Object} store Store instance
	 * @param {jQuery} inputElement
	 * @param {jQuery} selectElement
	 * @param {jQuery} hiddenElement
	 * @param {CurrencyParser} numberParser
	 * @param {CurrencyFormatter} numberFormatter
	 * @return {AmountComponent}
	 */
	createAmountComponent: function ( store, inputElement, selectElement, hiddenElement, numberParser, numberFormatter ) {
		var component = objectAssign( Object.create( AmountComponent ), {
			inputElement: inputElement,
			selectElement: selectElement,
			hiddenElement: hiddenElement,
			numberFormatter: numberFormatter
		} );
		NumericInputHandler.createNumericInputHandler( inputElement, numberParser.getDecimalDelimiter() );
		inputElement.on( 'change', function ( evt ) {
			var amount;
			try {
				amount = numberParser.parse( evt.target.value );
			} catch ( e ) {
				amount = 0;
				inputElement.val( '0' );
			}
			store.dispatch( actions.newInputAmountAction( amount ) );
		} );
		selectElement.on( 'change', function ( evt ) {
			store.dispatch( actions.newSelectAmountAction( evt.target.value ) );
		} );
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
	},

	addEagerChangeBehavior: function ( textComponent, debouncingFunction ) {
		debouncingFunction = debouncingFunction || defaultDebounce;
		textComponent.element.on( 'keypress', debouncingFunction( function ( evt ) {
			textComponent.onChange( evt );
			if ( textComponent.validator ) {
				textComponent.validator( evt );
			}
		} ) );
		return textComponent;
	},

	SelectComponent: SelectComponent
};
