'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	errorBoxHandler = require( '../../lib/view_handler/error_box' )
	;

test( 'When there are no messages, box is hidden', function ( t ) {
	var box = {
			hide: sinon.spy()
		},
		handler = errorBoxHandler.createHandler( box );

	handler.update( {} );
	t.ok( box.hide.calledOnce, 'error box should be hidden' );
	t.end();
} );

test( 'When there are messages, ', function ( t ) {
	var subElement = {
			text: sinon.spy()
		},
		box = {
			show: sinon.spy(),
			find: sinon.stub().returns( subElement )
		},
		handler = errorBoxHandler.createHandler( box ),
		errorMessages = { amount: 'This will be discarded', paymentType: 'irrelevant' };

	handler.update( errorMessages );

	t.test( '    box is shown', function ( t ) {
		t.ok( box.show.calledOnce, 'error box should be shown' );
		t.end();
	} );

	t.test( '    text is inserted in \'.fields\' subelement', function ( t ) {
		t.ok( box.find.calledWith( '.fields' ) );
		t.ok( subElement.text.calledOnce, 'text in subelement should be set' );
		t.ok( subElement.text.calledWith( 'amount, paymentType' ), 'text in subelement should contain element names' );
		t.end();
	} );

	t.end();
} );
