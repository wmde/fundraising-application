'use strict';
var objectAssign = require( 'object-assign' ),
	actions = require( './actions' ),

	createDefaultChangeHandler = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( actions.newChangeContentAction( contentName, evt.target.value ) );
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

	TextComponent = {
		element: null,
		contentName: '',
		onChange: null,
		render: function ( formContent ) {
			this.element.val( formContent[ this.contentName ] );
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

	createTextComponent: function ( store, element, contentName ) {
		var component = objectAssign( Object.create( TextComponent ), {
			element: element,
			contentName: contentName,
			onChange: createDefaultChangeHandler( store, contentName )
		} );
		element.on( 'change', component.onChange );
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
	}
};
