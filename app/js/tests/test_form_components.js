
'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	formComponents = require( '../lib/form_components' ),
	createSpyingElement = function () {
		return {
			on: sinon.spy(),
			val: sinon.spy(),
			prop: sinon.spy()
		};
	}
	;

test( 'Components add change handling function to their elements', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createTextComponent( store, element, 'value' );
	t.ok( element.on.calledOnce, 'event binding function is called once' );
	t.ok( element.on.calledWith( 'change', component.onChange ) );
	t.end();
} );

test( 'Change handler of components dispatches change action to store', function ( t ) {
	var element = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: 'current value' } },
		expectedAction = { type: 'CHANGE_CONTENT', payload: { value: 'current value', contentName: 'value' } },
		component = formComponents.createTextComponent( store, element, 'value' );

	component.onChange( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'store dispatch is called once' );
	t.ok( store.dispatch.calledWith( expectedAction ), 'action contains event value' );

	t.end();
} );

test( 'Rendering the text component sets the value', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createTextComponent( store, element, 'value' );

	component.render( { value: 'the new awesome value' } );

	t.ok( element.val.calledOnce, 'value is set once' );
	t.ok( element.val.calledWith( 'the new awesome value' ) );
	t.end();
} );

test( 'Rendering the radio component sets the value as array', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createRadioComponent( store, element, 'value' );

	component.render( { value: 'the new awesome value' } );

	t.ok( element.val.calledOnce, 'value is set once' );
	t.ok( element.val.calledWith( [ 'the new awesome value' ] ) );
	t.end();
} );

test( 'Rendering the amount component with custom amount clears selection and sets text value', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		store = {},
		component = formComponents.createAmountComponent( store, textElement, selectElement );

	component.render( { amount: '23,00', isCustomAmount: true } );

	t.ok( textElement.val.calledOnce, 'value is set once' );
	t.ok( textElement.val.calledWith( '23,00' ) );
	t.ok( selectElement.val.callCount === 0, 'select element value is not set' );
	t.ok( selectElement.prop.calledOnce, 'property was set' );
	t.ok( selectElement.prop.calledWith( 'checked', false ), 'check property was removed' );

	t.end();
} );

test( 'Rendering the amount component with non-custom amount sets the input and select fields', function ( t ) {
	var textElement = createSpyingElement(),
			selectElement = createSpyingElement(),
			store = {},
			component = formComponents.createAmountComponent( store, textElement, selectElement );

	component.render( { amount: '50,00', isCustomAmount: false } );

	t.ok( textElement.val.calledOnce, 'value is set' );
	t.ok( textElement.val.calledWith( '50,00' ) );
	t.ok( selectElement.val.calledOnce, 'select element value is set' );
	t.ok( selectElement.val.calledWith( [ '50,00' ] ), 'select element value is set' ); // needs to be array for selects
	t.end();
} );

test( 'Changing the amount selection dispatches select action', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: '50,00' } },
		expectedAction = { type: 'SELECT_AMOUNT', payload: { amount: '50,00' } };

	formComponents.createAmountComponent( store, textElement, selectElement );

	t.ok( selectElement.on.calledOnce, 'event handler is attached' );

	// simulate event trigger by calling event handling function
	selectElement.on.args[ 0 ][ 1 ]( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'event handler triggers store update' );
	t.deepEqual( store.dispatch.args[ 0 ][ 0 ], expectedAction, 'event handler generates the correct action' );
	t.end();
} );

test( 'Changing the amount input dispatches select action', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: '99,99' } },
		expectedAction = { type: 'INPUT_AMOUNT', payload: { amount: '99,99' } };

	formComponents.createAmountComponent( store, textElement, selectElement );

	t.ok( textElement.on.calledOnce, 'event handler is attached' );

	// simulate event trigger by calling event handling function
	textElement.on.args[ 0 ][ 1 ]( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'event handler triggers store update' );
	t.deepEqual( store.dispatch.args[ 0 ][ 0 ], expectedAction, 'event handler generates the correct action' );
	t.end();
} );
