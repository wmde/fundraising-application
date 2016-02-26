'use strict';

var test = require( 'tape' ),
	enabledWhenValidHandler = require( '../../lib/view_handler/enabled_when_valid' ),
	ElementSpy = {
		isEnabled: true,
		prop: function ( attr, value ) {
			if ( attr.toLowerCase() === 'disabled' )  {
				this.isEnabled = !value;
			}
		}
	};

test( 'Element is enabled when state is valid and validated', function ( t ) {
	var elm = Object.create( ElementSpy ),
		handler = enabledWhenValidHandler.createHandler( elm );

	elm.isEnabled = false;
	handler.update( { isValid: true, isValidated: true } );
	t.ok( elm.isEnabled );
	t.end();
} );

test( 'Element is enabled when state is invalid or unvalidated', function ( t ) {
	var elm = Object.create( ElementSpy ),
		handler = enabledWhenValidHandler.createHandler( elm );

	handler.update( { isValid: true, isValidated: false } );
	t.notOk( elm.isEnabled, 'element is enabled' );
	handler.update( { isValid: false, isValidated: true } );
	t.notOk( elm.isEnabled, 'element is disabled' );
	t.end();
} );
