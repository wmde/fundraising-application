
'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	formComponents = require( '../lib/form_components' )
	;

test( 'Components add change handling function to their elements', function ( t ) {
	var element = {
			on: sinon.spy()
		},
		store = {},
		component = formComponents.createTextComponent( store, element, 'value' );
	t.ok( element.on.calledOnce, 'event binding function is called once' );
	t.ok( element.on.calledWith( 'change', component.onChange ) );
	t.end();
} );

test( 'Change handler of components dispatches change action to store', function ( t ) {
	var element = {
			on: sinon.spy()
		},
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
	var element = {
			on: sinon.spy(),
			val: sinon.spy()
		},
		store = {},
		component = formComponents.createTextComponent( store, element, 'value' );

	component.render( { value: 'the new awesome value' } );

	t.ok( element.val.calledOnce, 'value is set once' );
	t.ok( element.val.calledWith( 'the new awesome value' ) );
	t.end();
} );

test( 'Rendering the radio component sets the value as array', function ( t ) {
	var element = {
			on: sinon.spy(),
			val: sinon.spy()
		},
		store = {},
		component = formComponents.createRadioComponent( store, element, 'value' );

	component.render( { value: 'the new awesome value' } );

	t.ok( element.val.calledOnce, 'value is set once' );
	t.ok( element.val.calledWith( [ 'the new awesome value' ] ) );
	t.end();
} );
