'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	defaultValueSwitcher = require( '../../lib/view_handler/default_value_switcher' ),
	createDefaultValueSwitcher = defaultValueSwitcher.createDefaultValueSwitcher;

function createClickableTestElement() {
	return {
		click: sinon.spy()
	};
}

function createInvisibleElement() {
	return {
		is: sinon.stub().returns( false )
	};
}

function createVisibleElement() {
	return {
		is: sinon.stub().returns( true )
	};
}

function createElementGroup( checkedElement ) {
	return {
		filter: sinon.stub().returns( checkedElement )
	};
}

test( 'When the currently checked element is not visible, click event for default element is clicked', function ( t ) {
	var defaultElement = createClickableTestElement(),
		handler = createDefaultValueSwitcher( createElementGroup( createInvisibleElement() ), defaultElement );

	handler.update();

	t.ok( defaultElement.click.calledOnce, 'click event triggered' );
	t.end();
} );

test( 'When the currently checked element is visible, nothing happens', function ( t ) {
	var defaultElement = createClickableTestElement(),
		handler = createDefaultValueSwitcher( createElementGroup( createVisibleElement() ), defaultElement );

	handler.update();

	t.ok( defaultElement.click.notCalled, 'no click event triggered' );
	t.end();
} );
