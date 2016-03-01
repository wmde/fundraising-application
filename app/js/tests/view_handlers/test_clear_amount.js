'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	clearAmountHandler = require( '../../lib/view_handler/clear_amount' )
	;

test( 'When amount is not custom, remove value from custom amount field', function ( t ) {
	var foundAmountSelectElement = { prop: sinon.spy() },
		amountSelect = { filter: sinon.stub().returns( foundAmountSelectElement ) },
		amountInput = { val: sinon.stub().returns( '5' ) },
		handler = clearAmountHandler.createHandler( amountSelect, amountInput ),
		formData = { amount: '5', isCustomAmount: false };

	handler.update( formData );
	t.ok( amountInput.val.calledOnce, 'Value must only be updated once' );
	t.ok( amountInput.val.calledWith( '' ), 'Value must be set to empty string' );
	t.end();
} );

test( 'When amount is not custom, try to select field with escaped amount value', function ( t ) {
	var foundAmountSelectElement = { prop: sinon.spy() },
		amountSelect = { filter: sinon.stub().returns( foundAmountSelectElement ) },
		amountInput = { val: sinon.stub().returns( '5' ) },
		handler = clearAmountHandler.createHandler( amountSelect, amountInput ),
		formData = { amount: '5,00', isCustomAmount: false };

	handler.update( formData );
	t.ok( amountSelect.filter.calledWith( '[value=5\\,00]' ), 'Selection must match value' );
	t.ok( foundAmountSelectElement.prop.calledOnce, 'Value must only be updated once' );
	t.ok( foundAmountSelectElement.prop.calledWith( 'checked', true ), 'Checked property must be set' );

	t.end();
} );

test( 'When amount is null and not custom, no field is selected', function ( t ) {
	var foundAmountSelectElement = { prop: sinon.spy() },
		amountSelect = { filter: sinon.stub().returns( foundAmountSelectElement ) },
		amountInput = { val: sinon.stub().returns( '5' ) },
		handler = clearAmountHandler.createHandler( amountSelect, amountInput ),
		formData = { amount: null, isCustomAmount: false };

	handler.update( formData );
	t.ok( amountSelect.filter.notCalled, 'When amount is null, checked property should be selected' );
	t.ok( foundAmountSelectElement.prop.notCalled, 'When amount is null, checked property should not change' );

	t.end();
} );

test( 'When data stays the same, clear amount only once', function ( t ) {
	var foundAmountSelectElement = { prop: sinon.spy() },
		amountSelect = { filter: sinon.stub().returns( foundAmountSelectElement ) },
		amountInput = { val: sinon.stub().returns( '5' ) },
		handler = clearAmountHandler.createHandler( amountSelect, amountInput ),
		formData = { amount: '5', isCustomAmount: false };

	handler.update( formData );
	handler.update( formData );
	t.ok( amountInput.val.calledOnce, 'Value must only be updated once' );
	t.end();
} );

test( 'When amount is custom, clear selection from preselected amounts', function ( t ) {
	var amountSelect = { prop: sinon.stub(), find: sinon.stub().returns( [] ) },
		amountInput = sinon.stub( {} ),
		handler = clearAmountHandler.createHandler( amountSelect, amountInput ),
		formData = { amount: '23,00', isCustomAmount: true };

	handler.update( formData );
	handler.update( formData ); // Check for multiple calls with identical data
	t.ok( amountSelect.prop.calledOnce, 'prop must be called only once' );
	t.ok( amountSelect.prop.calledWith( 'checked', false ), 'Amount radio buttons must be cleared' );
	t.end();
} );
