'use strict';

var test = require( 'tape-catch' ),
	objectAssign = require( 'object-assign' ),
	jQueryElementStub = require( '../../../jQueryElementStub' ),
	jQueryPseudoHtmlGenerator = require( '../../../jQueryPseudoHtmlGenerator' ),
	createContainerElement = require( '../createContainerElement' ),
	PaymentType = require( '../../../../lib/view_handler/section_info/types/payment_type' )
;

test.Test.prototype.assertNthBankDataElementIsTag = function ( node, nthChild, tag, msg ) {
	this.equals( node.append.args[ 0 ][ nthChild ].toString(), tag, msg );
};

test.Test.prototype.assertNthBankDataElementHasText = function ( node, nthChild, text, msg ) {
	this.ok( node.append.args[ 0 ][ nthChild ].text.withArgs( text ).calledOnce, msg );
};

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
	t.ok( longText.html.withArgs( '' ).calledOnce, 'Long text is reset' );

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
		} ),
		longTextWrapper,
		bankData,
		paymentTypeInfo
	;

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'BEZ', '4711', '8888', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-BEZ' ).calledOnce );
	t.ok( text.text.withArgs( 'Lastschrift' ).calledOnce, 'Payment type is set' );

	longTextWrapper = longText.html.args[ 0 ][ 0 ];

	t.equals( longTextWrapper.toString(), '<div>' );
	t.ok( longTextWrapper.append.calledTwice );

	bankData = longTextWrapper.append.args[ 0 ][ 0 ];
	paymentTypeInfo = longTextWrapper.append.args[ 1 ][ 0 ];

	t.equals( bankData.toString(), '<dl>', 'Bank data is a list' );
	t.ok( bankData.addClass.withArgs( 'bank-info' ).calledOnce );

	t.assertNthBankDataElementIsTag( bankData, 0, '<dt>', 'Bank data IBAN title set' );
	t.assertNthBankDataElementHasText( bankData, 0, 'IBAN', 'Bank data IBAN title set' );

	t.assertNthBankDataElementIsTag( bankData, 1, '<dd>', 'Bank data IBAN set' );
	t.assertNthBankDataElementHasText( bankData, 1, '4711', 'Bank data IBAN set' );

	t.assertNthBankDataElementIsTag( bankData, 2, '<dt>', 'Bank data BIC title set'  );
	t.assertNthBankDataElementHasText( bankData, 2, 'BIC', 'Bank data BIC title set' );

	t.assertNthBankDataElementIsTag( bankData, 3, '<dd>', 'Bank data BIC set'  );
	t.assertNthBankDataElementHasText( bankData, 3, '8888', 'Bank data BIC set' );

	t.equals( paymentTypeInfo.toString(), '<div>', 'text itself wrapped in another block-level element' );
	t.equals( paymentTypeInfo.text.args[ 0 ][ 0 ], 'Will be deducted', 'Long text is set after bank data' );

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

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'BEZ', '', '', { dataEntered: false, isValid: null } );

	t.ok( container, 'elements injected as null have no methods called upon, cause no errors' );

	delete global.jQuery;

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

	global.jQuery = jQueryPseudoHtmlGenerator;

	handler.update( 'BEZ', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.toggleClass.withArgs( 'has-longtext', true ).calledOnce );

	delete global.jQuery;

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

test( 'No data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container
		} );

	handler.update( '', '', '', { dataEntered: false, isValid: null } );

	t.ok( container.addClass.withArgs( 'disabled' ).calledOnce );

	t.end();
} );

test( 'Valid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container
		} );

	handler.update( 'PPL', '', '', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );

	t.end();
} );

test( 'Invalid data entered reflected in style', function ( t ) {
	var container = createContainerElement(),
		handler = objectAssign( Object.create( PaymentType ), {
			container: container
		} );

	handler.update( 'XFD', '', '', { dataEntered: true, isValid: false } );

	t.ok( container.addClass.withArgs( 'invalid' ).calledOnce );

	t.end();
} );
