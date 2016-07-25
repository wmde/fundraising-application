'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	errorBoxHandler = require( '../../lib/view_handler/error_box' )
	;

test( 'When there are only valid fields, box is hidden', function ( t ) {
	var box = {
			hide: sinon.spy()
		},
		handler = errorBoxHandler.createHandler( box );

	handler.update( { amount: { isValid: true }, paymentType: { isValid: true } } );
	t.ok( box.hide.calledOnce, 'error box should be hidden' );
	t.end();
} );

test( 'When there are invalid fields, ', function ( t ) {
	var subElement = {
			text: sinon.spy()
		},
		box = {
			show: sinon.spy(),
			find: sinon.stub().returns( subElement )
		},
		handler = errorBoxHandler.createHandler( box ),
		fieldProperties = { amount: { isValid: false }, paymentType: { isValid: false } };

	handler.update( fieldProperties );

	t.test( '    text is inserted in \'.fields\' subelement', function ( t ) {
		t.ok( box.find.calledWith( '.fields' ) );
		t.ok( subElement.text.calledOnce, 'text in subelement should be set' );
		t.ok( subElement.text.calledWith( 'amount, paymentType' ), 'text in subelement should contain element names' );
		t.end();
	} );

	t.end();
} );
