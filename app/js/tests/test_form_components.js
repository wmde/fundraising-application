
'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	formComponents = require( '../lib/form_components' ),
	createSpyingElement = function () {
		return {
			on: sinon.spy(),
			val: sinon.spy(),
			prop: sinon.stub(),
			text: sinon.spy(),
			change: sinon.spy()
		};
	},
	assertChangeHandlerWasSet = function ( t, spyingElement ) {
		t.ok( spyingElement.on.calledOnce, 'event handler was set once' );
		t.equal( spyingElement.on.firstCall.args[ 0 ], 'change', 'event handler was set for change events' );
		t.equal( typeof spyingElement.on.firstCall.args[ 1 ], 'function', 'event handler is a function' );
	},
	createBankDataConfig = function () {
		return {
			ibanElement: createSpyingElement(),
			bicElement: createSpyingElement(),
			accountNumberElement: createSpyingElement(),
			bankCodeElement: createSpyingElement(),
			debitTypeElement: createSpyingElement(),
			bankNameFieldElement: createSpyingElement(),
			bankNameDisplayElement: createSpyingElement()
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

test( 'Validating components add change handling function to their elements', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createValidatingTextComponent( store, element, 'value' );
	t.ok( element.on.calledTwice, 'event binding function is called twice' );
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

	t.ok( element.val.calledTwice, 'value is called twice (get/set)' );
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

test( 'Given true, rendering the checkbox component applies the checked property', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createCheckboxComponent( store, element, 'value' );

	component.render( { value: true } );

	t.ok( element.prop.calledOnce, 'value is set once' );
	t.ok( element.prop.calledWith( 'checked', true ) );
	t.end();
} );

test( 'Given false, rendering the checkbox component applies the checked property', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createCheckboxComponent( store, element, 'value' );

	component.render( { value: false } );

	t.ok( element.prop.calledOnce, 'value is set once' );
	t.ok( element.prop.calledWith( 'checked', false ) );
	t.end();
} );

test( 'Event handler for checkbox component stores checked state', function ( t ) {
	var element = createSpyingElement(),
		store = { dispatch: sinon.spy() },
		fakeEvent = { target: { value: 'current value' } },
		expectedAction = { type: 'CHANGE_CONTENT', payload: { value: true, contentName: 'value' } },
		component = formComponents.createCheckboxComponent( store, element, 'value' );

	element.prop.returns( true );

	component.onChange( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'action is dispatched' );
	t.ok( store.dispatch.calledWith( expectedAction ), 'action contains expected value ' );
	t.end();
} );

test( 'Rendering the amount component with custom amount clears selection and sets text and hidden fields', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		hiddenElement = createSpyingElement(),
		store = {},
		component = formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement );

	component.render( { amount: '23,00', isCustomAmount: true } );

	t.ok( textElement.val.calledOnce, 'value is set once' );
	t.ok( textElement.val.calledWith( '23,00' ) );
	t.ok( selectElement.val.callCount === 0, 'select element value is not set' );
	t.ok( selectElement.prop.calledOnce, 'property was set' );
	t.ok( selectElement.prop.calledWith( 'checked', false ), 'check property was removed' );
	t.ok( hiddenElement.val.calledOnce, 'hidden element value is set' );
	t.ok( hiddenElement.val.calledWith( '23,00' ), 'hidden element value is set' );

	t.end();
} );

test( 'Rendering the amount component with non-custom amount sets the hidden field and clears the text field', function ( t ) {
	var textElement = createSpyingElement(),
			selectElement = createSpyingElement(),
			hiddenElement = createSpyingElement(),
			store = {},
			component = formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement );

	component.render( { amount: '50,00', isCustomAmount: false } );

	t.ok( textElement.val.calledOnce, 'value is cleared' );
	t.ok( textElement.val.calledWith( '' ) );
	t.ok( selectElement.val.calledOnce, 'select element value is set' );
	t.ok( selectElement.val.calledWith( [ '50,00' ] ), 'select element value is set' ); // needs to be array for selects
	t.ok( hiddenElement.val.calledOnce, 'hidden element value is set' );
	t.ok( hiddenElement.val.calledWith( '50,00' ), 'hidden element value is set' );
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

test( 'Bank data component adds change handling function to its elements', function ( t ) {
	var bankDataComponentConfig = createBankDataConfig(),
		store = {};

	formComponents.createBankDataComponent( store, bankDataComponentConfig );

	assertChangeHandlerWasSet( t, bankDataComponentConfig.ibanElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.bicElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.accountNumberElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.bankCodeElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.debitTypeElement );
	// Bank data is a pure display field with no change handler
	t.end();
} );

test( 'Bank data component renders the store values in its elements', function ( t ) {
	var bankDataComponentConfig = createBankDataConfig(),
		store = {},
		handler = formComponents.createBankDataComponent( store, bankDataComponentConfig );

	handler.render( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'non-sepa'
	} );

	t.equal( bankDataComponentConfig.ibanElement.val.args[ 0 ][ 0 ], 'DE12500105170648489890', 'IBAN value is set' );
	t.equal( bankDataComponentConfig.bicElement.val.args[ 0 ][ 0 ], 'INGDDEFFXXX', 'BIC value is set' );
	t.equal( bankDataComponentConfig.accountNumberElement.val.args[ 0 ][ 0 ], '0648489890', 'Account number is set' );
	t.equal( bankDataComponentConfig.bankCodeElement.val.args[ 0 ][ 0 ], '50010517', 'BIC value is set' );
	t.equal( bankDataComponentConfig.bankNameDisplayElement.text.args[ 0 ][ 0 ], 'ING-DiBa', 'Bank name is displayed' );
	t.equal( bankDataComponentConfig.bankNameFieldElement.val.args[ 0 ][ 0 ], 'ING-DiBa', 'Bank name is set in field' );
	t.deepEqual( bankDataComponentConfig.debitTypeElement.val.args[ 0 ][ 0 ], [ 'non-sepa' ], 'Debit type is set' );

	t.end();
} );

test( 'Bank data component checks if all elements in the configuration are set', function ( t ) {
	// TODO Do more than a spot check. How do i check all fields without using a loop in the test?
	// The test succeeds without checking code in the factory function because of the calls to the event binding
	// However, if bankName instead of iban was missing, the test would fail.
	var bankDataComponentConfigWithMissingIban = createBankDataConfig(),
		store = {};

	delete bankDataComponentConfigWithMissingIban.ibanElement;

	t.throws( function () {
		formComponents.createBankDataComponent( store, bankDataComponentConfigWithMissingIban );
	} );

	t.end();
} );
