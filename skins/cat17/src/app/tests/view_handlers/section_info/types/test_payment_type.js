'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../../jQueryElementStub' ),
	jQueryPseudoHtmlGenerator = require( '../../../jQueryPseudoHtmlGenerator' ),
	createContainerElement = require( '../createContainerElement' ),
	PaymentType = require( '../../../../lib/view_handler/section_info/types/payment_type' )
;

test( 'Payment type PPL info is set in respective elements', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { BEZ: 'icon-BEZ', PPL: 'icon-PPL' },
			valueTextMap: { BEZ: 'Lastschrift', PPL: 'Paypal' },
			valueLongTextMap: { BEZ: 'Will be deducted', PPL: 'I am of no importance' }
		} );

	handler.update( 'PPL', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-PPL' ).calledOnce );
	t.ok( text.text.withArgs( 'Paypal' ).calledOnce, 'Payment type is set' );
	t.ok( longText.text.withArgs( '' ).calledOnce, 'Long text is reset' );
	t.ok( longText.prepend.notCalled, 'Long text is not changed by prepend' );

	t.end();
} );

test( 'Payment type BEZ info is set in respective elements', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { BEZ: 'icon-BEZ', PPL: 'icon-PPL' },
			valueTextMap: { BEZ: 'Lastschrift', PPL: 'Paypal' },
			valueLongTextMap: { BEZ: 'Will be deducted', PPL: 'Forward to PPL' }
		} );

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'BEZ', '4711', '8888', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-BEZ' ).calledOnce );
	t.ok( text.text.withArgs( 'Lastschrift' ).calledOnce, 'Payment type is set' );
	t.ok( longText.text.withArgs( 'Will be deducted' ).calledOnce, 'Long text is set' );
	t.ok( longText.prepend.calledOnce, 'Bank data is prepended' );

	t.equals( longText.prepend.args[ 0 ].toString(), '<dl>', 'Bank data is a list' );
	t.ok( longText.prepend.args[ 0 ][ 0 ].addClass.withArgs( 'bank-info' ).calledOnce );
	t.ok( longText.prepend.args[ 0 ][ 0 ].append.calledOnce, 'Bank data put before text' );

	t.equals( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 0 ].toString(), '<dt>', 'Bank data IBAN title set' );
	t.ok( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 0 ].text.withArgs( 'IBAN' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 1 ].toString(), '<dd>', 'Bank data IBAN set' );
	t.ok( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 1 ].text.withArgs( '4711' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 2 ].toString(), '<dt>', 'Bank data BIC title set' );
	t.ok( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 2 ].text.withArgs( 'BIC' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 3 ].toString(), '<dd>', 'Bank data BIC set' );
	t.ok( longText.prepend.args[ 0 ][ 0 ].append.args[ 0 ][ 3 ].text.withArgs( '8888' ).calledOnce, 'Bank data IBAN set' );

	delete global.jQuery;

	t.end();
} );

test( 'Missing features are gently skipped', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			icon: null,
			text: null,
			longText: null,

			valueIconMap: { BEZ: 'icon-BEZ', PPL: 'icon-PPL' },
			valueTextMap: { BEZ: 'Lastschrift', PPL: 'Paypal' },
			valueLongTextMap: { BEZ: 'Will be deducted', PPL: 'Forward to PPL' }
		} );

	handler.update( 'BEZ', '', '', { dataEntered: false, isValid: null } );

	t.ok( container, 'elements injected as null have no methods called upon, cause no errors' );

	t.end();
} );

test( 'Existing longtext is indicated', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueLongTextMap: { BEZ: 'Will be deducted', PPL: '' }
		} );

	handler.update( 'BEZ', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.toggleClass.withArgs( 'has-longtext', true ).calledOnce );

	t.end();
} );

test( 'Missing longtext is indicated', function ( t ) {
	var container = createContainerElement(),
		icon = jQueryElementStub(),
		text = jQueryElementStub(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueLongTextMap: { BEZ: 'Will be deducted', PPL: '' }
		} );

	handler.update( 'PPL', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.toggleClass.withArgs( 'has-longtext', false ).calledOnce );

	t.end();
} );

test( 'Opened longtext are shut', function ( t ) {
	var container = createContainerElement(),
		longText = jQueryElementStub(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container,

			longText: longText,

			valueLongTextMap: { BEZ: 'Will be deducted', PPL: 'Somesome' }
		} );

	handler.update( 'PPL', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.removeClass.withArgs( 'opened' ).calledOnce );
	t.ok( container.find.withArgs( '.opened' ).calledOnce );

	t.end();
} );
