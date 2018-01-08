'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../../jQueryElementStub' ),
	jQueryPseudoHtmlGenerator = require( '../../../jQueryPseudoHtmlGenerator' ),
	createContainerElement = require( '../createContainerElement' ),
	DonorType = require( '../../../../lib/view_handler/section_info/types/donor_type' )
;

test( 'Donor type info without entered data indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	icon.data.withArgs( 'display-error' ).returns( true );
	text.data.withArgs( 'empty-text' ).returns( 'nothing entered so far' );

	handler.update( 'person', '', '', '', '', '', '', '', '', 'DE', '', { dataEntered: false, isValid: null } );

	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce, 'no data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-error' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'nothing entered so far' ).calledOnce, 'fallback address type text is set' );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for private person indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'person', 'Herr', 'Dr.', 'test', 'user', '', 'demostr 4', '10112', 'Bärlin', 'DE', 'me@you.com', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-person' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'Privatperson' ).calledOnce, 'address type text is set' );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 0 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'Herr Dr. test user', 'name set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 1 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'demostr 4', 'street set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 2 ][ 0 ].text.args[ 0 ][ 0 ].toString(), '10112 Bärlin', 'address set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 3 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'Deutschland', 'country translated and set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 4 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'me@you.com', 'email set' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for company indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'firma', 'Frau', 'Prof.', 'state left', 'from private', 'ACME INC', 'acmestr 133b', '12331', 'Wien', 'AT', 'us@acme.com', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-firma' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'Firma' ).calledOnce, 'address type text is set' );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 0 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'ACME INC', 'name set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 1 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'acmestr 133b', 'street set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 2 ][ 0 ].text.args[ 0 ][ 0 ].toString(), '12331 Wien', 'address set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 3 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'Österreich', 'country translated and set' );
	t.equals( longText.html.args[ 0 ][ 0 ].append.args[ 4 ][ 0 ].text.args[ 0 ][ 0 ].toString(), 'us@acme.com', 'email set' );

	delete global.jQuery;

	t.end();
} );

test( 'Donor type info for anonymous indicated correctly', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( DonorType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { person: 'icon-person', firma: 'icon-firma', anonym: 'icon-anonym' },
			valueTextMap: { person: 'Privatperson', firma: 'Firma', anonym: 'anonym' },

			countryNames: { DE: 'Deutschland', AT: 'Österreich' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'anonym', 'some', 'state', 'irrelevant', 'for', 'an', 'anonymous', 'record', 'left', 'DE', 'nospam@me.info', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce, 'data entered reflected in style' );
	t.ok( icon.addClass.withArgs( 'icon-anonym' ).calledOnce, 'icon set per address type' );
	t.ok( text.text.withArgs( 'anonym' ).calledOnce, 'address type text is set' );
	t.equals( longText.html.args[ 0 ][ 0 ].toString(), '<span>', 'long text filled with custom mark-up' );
	t.ok( longText.html.args[ 0 ][ 0 ].append.notCalled );
	t.ok( longText.append.notCalled );

	delete global.jQuery;

	t.end();
} );
