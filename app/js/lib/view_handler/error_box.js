'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),

	FieldNameTranslator = {
		fieldNames: {},
		translate: function ( fieldName ) {
			return this.fieldNames[ fieldName ] ? this.fieldNames[ fieldName ] : fieldName;
		}
	},

	ErrorBox = {
		el: null,
		fieldNameTranslator: null,
		update: function ( validationMessages ) {
			if ( _.isEmpty( validationMessages ) ) {
				this.el.hide();
				return;
			}
			this.el.show();
			this.el.find( '.fields' ).text(
				Object.keys( validationMessages )
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
