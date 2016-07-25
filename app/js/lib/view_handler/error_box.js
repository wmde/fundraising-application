'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),

	FieldNameTranslator = {
		fieldNames: {},
		translate: function ( fieldName ) {
			return this.fieldNames[ fieldName ] ? this.fieldNames[ fieldName ] : fieldName;
		}
	},

	ErrorBox = {
		el: null,
		fieldNameTranslator: null,
		collectFieldKeys: function ( fieldProperties ) {
			var invalidFields = [];
			_.forEach( fieldProperties, function ( properties, key ) {
				if ( properties.isValid === false ) {
					invalidFields.push( key );
				}
			} );
			return invalidFields;
		},
		update: function ( fieldProperties ) {
			var invalidFields = this.collectFieldKeys( fieldProperties );
			if ( _.isEmpty( invalidFields ) ) {
				this.el.hide();
				return;
			}
			this.el.find( '.fields' ).text(
				invalidFields
					.map( this.fieldNameTranslator.translate.bind( this.fieldNameTranslator ) )
					.join( ', ' )
			);
		}
	},

	createHandler = function ( boxContainer, fieldNameTranslations ) {
		var translator = objectAssign( Object.create( FieldNameTranslator ), {
			fieldNames: fieldNameTranslations || {}
		} );
		return objectAssign( Object.create( ErrorBox ), {
			el: boxContainer,
			fieldNameTranslator: translator
		} );
	};

module.exports = {
	createHandler: createHandler
};
