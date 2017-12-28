'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	formComponents = require( '../lib/form_components' ),
	objectAssign = require( 'object-assign' ),
	createSpyingElement = function () {
		return {
			on: sinon.spy(),
			is: sinon.stub(),
			val: sinon.spy(),
			prop: sinon.stub(),
			text: sinon.spy(),
			change: sinon.spy()
		};
	},
	assertChangeHandlerWasSet = function ( t, spyingElement, expectedCallCount ) {
		t.equal( spyingElement.on.callCount, expectedCallCount || 1, 'event handler was set' );
		t.equal( spyingElement.on.firstCall.args[ 0 ], 'change', 'event handler was set for change events' );
		t.equal( typeof spyingElement.on.firstCall.args[ 1 ], 'function', 'event handler is a function' );
	},
	createBankDataConfig = function () {
		return {
			ibanElement: createSpyingElement(),
			bicElement: createSpyingElement(),
			accountNumberElement: createSpyingElement(),
			bankCodeElement: createSpyingElement(),
			bankNameFieldElement: createSpyingElement(),
			bankNameDisplayElement: createSpyingElement()
		};
	},
	createAmountParser = function () {
		return {
			parse: sinon.stub().returnsArg( 0 ),
			getDecimalDelimiter: sinon.stub().returns( ',' )
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

test( 'Change handler of components dispatches validation action to store', function ( t ) {
	var element = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: 'current value', getAttribute: sinon.stub(), hasAttribute: sinon.stub() } },
		expectedAction = { type: 'VALIDATE_INPUT', payload: { contentName: 'city', value: 'current value', pattern: '^.+$', optionalField: false } },
		component = formComponents.createValidatingTextComponent( store, element, 'city' );

	fakeEvent.target.getAttribute.withArgs( 'data-pattern' ).returns( '^.+$' );
	fakeEvent.target.hasAttribute.withArgs( 'data-optional' ).returns( false );
	component.validator( fakeEvent );

	t.ok( store.dispatch.calledWith( expectedAction ), 'action contains event value and element pattern' );

	t.end();
} );

test( 'Change handler of component passes optional attribute when dispatching validation action to store', function ( t ) {
	var element = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: 'current value', getAttribute: sinon.stub(), hasAttribute: sinon.stub() } },
		expectedAction = { type: 'VALIDATE_INPUT', payload: { contentName: 'city', value: 'current value', pattern: '^.+$', optionalField: true } },
		component = formComponents.createValidatingTextComponent( store, element, 'city' );

	fakeEvent.target.getAttribute.withArgs( 'data-pattern' ).returns( '^.+$' );
	fakeEvent.target.hasAttribute.withArgs( 'data-optional' ).returns( true );
	fakeEvent.target.getAttribute.withArgs( 'data-optional' ).returns( 'true' );
	component.validator( fakeEvent );

	t.ok( store.dispatch.calledWith( expectedAction ), 'action contains event value and element pattern' );

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

test( 'Text value is only set when element does not have focus', function ( t ) {
	var element = createSpyingElement(),
		store = {},
		component = formComponents.createTextComponent( store, element, 'value' );

	element.is.withArgs( ':focus' ).returns( true );
	component.render( { value: 'the new awesome value' } );

	t.ok( element.val.calledOnce, 'value is only read' );
	t.ok( element.val.getCall( 0 ).notCalledWith( 'the new awesome value' ), 'value is only read' );
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
		parser = createAmountParser(),
		dummyFormatter = {
			format: function ( v ) {
				return 'XX' + v + 'YY';

			}
		},
		store = {},
		parent = {
			addClass: sinon.spy()
		}
	;

	textElement.parent = function () {
		return parent;
	};

	var component = formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement, parser, dummyFormatter );

	component.render( { amount: 2300, isCustomAmount: true } );

	t.ok( textElement.val.calledOnce, 'value is set once' );

	t.ok( textElement.val.calledWith( 'XX2300YY' ) );
	t.ok( parent.addClass.withArgs( 'filled' ).calledOnce );
	t.ok( selectElement.val.callCount === 0, 'select element value is not set' );
	t.ok( selectElement.prop.calledOnce, 'property was set' );
	t.ok( selectElement.prop.calledWith( 'checked', false ), 'check property was removed' );
	t.ok( hiddenElement.val.calledOnce, 'hidden element value is set' );
	t.ok( hiddenElement.val.calledWith( 'XX2300YY' ), 'hidden element value is set' );

	t.end();
} );

test( 'Rendering the amount component with non-custom amount sets the hidden field and clears the text field', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		hiddenElement = createSpyingElement(),
		parser = createAmountParser(),
		dummyFormatter = {
			format: function ( v ) {
				return 'XX' + v + 'YY';

			}
		},
		store = {},
		parent = {
			removeClass: sinon.stub()
		}
	;

	textElement.parent = function () {
		return parent;
	};

	var component = formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement, parser, dummyFormatter );

	component.render( { amount: 5000, isCustomAmount: false } );

	t.ok( textElement.val.calledOnce, 'value is cleared' );
	t.ok( textElement.val.calledWith( '' ) );
	t.ok( parent.removeClass.withArgs( 'filled' ).calledOnce );
	t.ok( selectElement.val.calledOnce, 'select element value is set' );
	t.ok( selectElement.val.calledWith( [ '5000' ] ), 'select element value is set' ); // needs to be array for selects
	t.ok( hiddenElement.val.calledOnce, 'hidden element value is set' );
	t.ok( hiddenElement.val.calledWith( 'XX5000YY' ), 'hidden element value is set' );
	t.end();
} );

test( 'Changing the amount selection dispatches select action with selected value', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		hiddenElement = createSpyingElement(),
		store = {
			dispatch: sinon.spy()
		},
		dummyAmountParser = createAmountParser(),
		fakeEvent = { target: { value: 5000 } },
		expectedAction = { type: 'SELECT_AMOUNT', payload: { amount: 5000 } };

	formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement, dummyAmountParser );

	t.ok( selectElement.on.calledOnce, 'event handler is attached' );

	// simulate event trigger by calling event handling function
	selectElement.on.args[ 0 ][ 1 ]( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'event handler triggers store update' );
	t.deepEqual( store.dispatch.args[ 0 ][ 0 ], expectedAction, 'event handler generates the correct action' );
	t.ok( dummyAmountParser.parse.notCalled, 'amount parser is not called as the amount is passed on 1:1' );
	t.end();
} );

test( 'Changing the amount input dispatches input action with parsed content', function ( t ) {
	var textElement = createSpyingElement(),
		selectElement = createSpyingElement(),
		hiddenElement = createSpyingElement(),
		dummyAmountParser = createAmountParser(),
		store = {
			dispatch: sinon.spy()
		},
		fakeEvent = { target: { value: '99,99' } },
		expectedAction = { type: 'INPUT_AMOUNT', payload: { amount: '99,99' } };

	formComponents.createAmountComponent( store, textElement, selectElement, hiddenElement, dummyAmountParser );

	t.ok( textElement.on.withArgs( 'change' ).calledOnce, 'text input event handler is attached' );

	// simulate event trigger by calling event handling function
	textElement.on.withArgs( 'change' ).args[ 0 ][ 1 ]( fakeEvent );

	t.ok( store.dispatch.calledOnce, 'event handler triggers store update' );
	t.deepEqual( store.dispatch.args[ 0 ][ 0 ], expectedAction, 'event handler generates the correct action' );
	t.ok( dummyAmountParser.parse.calledWith( '99,99' ), 'parser us called with amount' );
	t.end();
} );

test( 'Bank data component adds change handling function to its elements', function ( t ) {
	var bankDataComponentConfig = createBankDataConfig(),
		store = {};

	formComponents.createBankDataComponent( store, bankDataComponentConfig );

	assertChangeHandlerWasSet( t, bankDataComponentConfig.ibanElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.bicElement, 2 );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.accountNumberElement );
	assertChangeHandlerWasSet( t, bankDataComponentConfig.bankCodeElement );
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

test( 'addEagerChangeBehavior calls onChange and validator handler on keypress event', function ( t ) {
	var component = {
			element: createSpyingElement(),
			onChange: sinon.spy(),
			validator: sinon.spy()
		},
		synchronousDebounce = function ( f ) {
			return f;
		},
		fakeEvent = { target: { value: 'Berlin' } };

	formComponents.addEagerChangeBehavior( component, synchronousDebounce );

	t.ok( component.element.on.withArgs( 'keypress' ).calledOnce, 'keypress event is attached' );

	component.element.on.withArgs( 'keypress' ).args[ 0 ][ 1 ]( fakeEvent );

	t.ok( component.onChange.calledOnce, 'change handler was called once' );
	t.ok( component.onChange.calledWith( fakeEvent ), 'change handler was called with event' );

	t.ok( component.validator.calledOnce, 'validator  was called once' );
	t.ok( component.validator.calledWith( fakeEvent ), 'validator was called with event' );
	t.end();
} );

test( 'SelectComponent renders when value changed', function ( t ) {
	var element = {
			val: sinon.stub(),
			change: sinon.stub()
		},
		component = objectAssign( Object.create( formComponents.SelectComponent ), {
			element: element,
			contentName: 'somesome'
		} );

	element.val.withArgs().returns( '6' ); // .val() is both the getter and setter method

	component.render( { somesome: '5' } );

	t.equals( element.val.callCount, 3 );
	t.deepEquals( element.val.thirdCall.args[ 0 ], [ '5' ], 'value gets set' );
	t.ok( element.change.calledOnce );

	t.end();
} );

test( 'SelectComponent treats null like empty string in update check', function ( t ) {
	var element = {
			val: sinon.stub(),
			change: sinon.stub()
		},
		component = objectAssign( Object.create( formComponents.SelectComponent ), {
			element: element,
			contentName: 'lorem'
		} );

	element.val.withArgs().returns( null ); // .val() is both the getter and setter method

	component.render( { lorem: '' } );

	t.equals( element.val.callCount, 2 );
	t.ok( element.change.notCalled );

	t.end();
} );
