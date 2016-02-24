'use strict';

/**
 * This module provides a unified getValue interface for HTML elements or groups of HTML elements that occur in the form.
 */

var objectAsssign = require( 'object-assign' ),
	TextValueAccessor = {
		elements: null,
		getValue: function () {
			return this.elements.val();
		}
	},
	RadioValueAccessor = {
		elements: null,
		getValue: function () {
			return this.elements.filter( ':checked' ).val();
		}
	},
	MultipleValueAccessor = {
		accessors: [],
		getValue: function () {
			var len = this.accessors.length,
				i;
			for ( i = 0; i < len; i++ ) {
				if ( this.accessors[ i ].getValue() ) {
					return this.accessors[ i ].getValue();
				}
			}
		}
	},
	createTextValueAccessor = function ( $elements ) {
		return objectAsssign( Object.create( TextValueAccessor ), { elements: $elements } );
	},
	createRadioValueAccessor = function ( $elements ) {
		return objectAsssign( Object.create( RadioValueAccessor ), { elements: $elements } );
	},
	createMultipleValueAccessor = function () {
		return objectAsssign( Object.create( MultipleValueAccessor ), {
			accessors: Array.prototype.slice.call( arguments )
		} ) ;
	};

module.exports = {
	createTextValueAccessor: createTextValueAccessor,
	createRadioValueAccessor: createRadioValueAccessor,
	createMultipleValueAccessor: createMultipleValueAccessor
};
