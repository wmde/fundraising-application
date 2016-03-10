'use strict';
var objectAssign = require( 'object-assign' ),
	createDefaultChangeHandler = function ( store, contentName ) {
		return function ( evt ) {
			store.dispatch( { type: 'CHANGE_CONTENT', payload: {
				value: evt.target.value,
				contentName: contentName
			} } );
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
	}
};
