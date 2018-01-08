'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../jQueryElementStub' ),
	createContainerElement = require( './createContainerElement' ),
	Base = require( '../../../lib/view_handler/section_info/base' )
;

test( 'Fallback text is used when value does not correspond to text map', function ( t ) {
	var container = createContainerElement(),
		text = jQueryElementStub(),
		handler = objectAssign( Object.create( Base ), {
			container: container,

			text: text,

			valueTextMap: { BEZ: 'Lastschrift', PPL: 'Paypal' }
		} );

	text.data.withArgs( 'empty-text' ).returns( 'Bitcoin' );

	handler.update( 'BTC' );

	t.ok( text.data.withArgs( 'empty-text' ).calledOnce, 'Fetches default text' );
	t.ok( text.text.withArgs( 'Bitcoin' ).calledOnce, 'Payment type is set' );

	t.end();
} );
