'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	objectAssign = require( 'object-assign' ),
	SectionInfo = require( '../../lib/view_handler/section_info' ),
	// your typical jQuery extended DOM node
	createElement = function () {
		return {
			find: sinon.stub(),
			text: sinon.stub(),
			html: sinon.stub(),
			removeClass: sinon.stub(),
			addClass: sinon.stub(),
			data: sinon.stub(),
			prepend: sinon.stub()
		};
	},
	formattedAmount = '23,00',
	currencyFormatter = {
		format: sinon.stub().returns( formattedAmount )
	}
;

test( 'The amount is passed to the currency formatter', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement(),
		handler = objectAssign( Object.create( SectionInfo.AmountFrequencySectionInfo ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { '0': 'icon-0', '1': 'icon-1' },
			valueTextMap: { '0': 'lorem', '1': 'ipsum' },
			valueLongTextMap: { '0': 'lorem lorem', '1': 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 23.00, '0', { dataEntered: true, isValid: true } );

	t.ok( currencyFormatter.format.calledOnce, 'format is called' );
	t.equals( currencyFormatter.format.firstCall.args[ 0 ], 23.00, 'Amount is passed to formatter' );
	t.end();
} );

test( 'Formatted amount is set in amount element', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement(),
		handler = objectAssign( Object.create( SectionInfo.AmountFrequencySectionInfo ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { '0': 'icon-0', '1': 'icon-1' },
			valueTextMap: { '0': 'lorem', '1': 'ipsum' },
			valueLongTextMap: { '0': 'lorem lorem', '1': 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 23.00, '0', { dataEntered: true, isValid: true } );

	t.ok( text.text.calledOnce, 'Amount is set' );
	t.equals( text.text.firstCall.args[ 0 ], formattedAmount + ' €', 'amount is set' );

	t.end();
} );

test( 'Icon is set according to value', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement(),
		handler = objectAssign( Object.create( SectionInfo.AmountFrequencySectionInfo ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { '0': 'icon-0', '1': 'icon-1' },
			valueTextMap: { '0': 'lorem', '1': 'ipsum' },
			valueLongTextMap: { '0': 'lorem lorem', '1': 'ipsum ipsum' },

			currencyFormatter: currencyFormatter
		} );

	handler.update( 34.00, '1', { dataEntered: true, isValid: true } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-1' ).calledOnce );

	t.end();
} );

test( 'Icon is set to error if value out of bounds and error desired', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		handler = objectAssign( Object.create( SectionInfo.AmountFrequencySectionInfo ), {
			container: container,

			icon: icon,

			valueIconMap: { '0': 'icon-0', '1': 'icon-1' },

			currencyFormatter: currencyFormatter
		} );

	icon.data.withArgs( 'display-error' ).returns( true );

	handler.update( 101, 'outOfBounds', { dataEntered: true, isValid: false } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-error' ).calledOnce );

	t.end();
} );

test( 'Icon is reset if value out of bounds and error not desired', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		handler = objectAssign( Object.create( SectionInfo.AmountFrequencySectionInfo ), {
			container: container,

			icon: icon,

			valueIconMap: { '0': 'icon-0', '1': 'icon-1' },

			currencyFormatter: currencyFormatter
		} );

	icon.data.withArgs( 'display-error' ).returns( false );

	handler.update( 101, 'outOfBounds', { dataEntered: true, isValid: false } );

	t.ok( icon.removeClass.withArgs( 'icon-error' ).calledOnce );
	t.ok( icon.removeClass.withArgs( 'icon-0 icon-1' ).calledOnce );
	t.ok( icon.addClass.notCalled );

	t.end();
} );

test( 'Payment type PPL info is set in respective elements', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement(),
		handler = objectAssign( Object.create( SectionInfo.PaymentTypeSectionInfo ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { 'BEZ': 'icon-BEZ', 'PPL': 'icon-PPL' },
			valueTextMap: { 'BEZ': 'Lastschrift', 'PPL': 'Paypal' },
			valueLongTextMap: { 'BEZ': 'Will be deducted', 'PPL': 'I am of no importance' }
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
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement(),
		handler = objectAssign( Object.create( SectionInfo.PaymentTypeSectionInfo ), {
			container: container,

			icon: icon,
			text: text,
			longText: longText,

			valueIconMap: { 'BEZ': 'icon-BEZ', 'PPL': 'icon-PPL' },
			valueTextMap: { 'BEZ': 'Lastschrift', 'PPL': 'Paypal' },
			valueLongTextMap: { 'BEZ': 'Will be deducted', 'PPL': 'Forward to PPL' }
		} );

	/**
	 * $ is used as HTML generator - for this test we let it become a super-charged string object with access to
	 * - the original construction parameter (HTML) via .toString()
	 * - the methods called on the wanna-be node via the properties
	 */
	global.$ = function ( arg0 ) {
		arg0 = objectAssign( arg0, {
			addClass: sinon.stub().returnsThis(),
			text: sinon.stub().returnsThis(),
			append: sinon.stub().returnsThis()
		} );
		return arg0;
	};

	handler.update( 'BEZ', '4711', '8888', { dataEntered: true, isValid: true } );

	t.ok( container.addClass.withArgs( 'completed' ).calledOnce );
	t.ok( icon.addClass.withArgs( 'icon-BEZ' ).calledOnce );
	t.ok( text.text.withArgs( 'Lastschrift' ).calledOnce, 'Payment type is set' );
	t.ok( longText.text.withArgs( 'Will be deducted' ).calledOnce, 'Long text is set' );
	t.ok( longText.prepend.calledOnce, 'Bank data is prepended' );

	t.equals( longText.prepend.args[0].toString(), '<dl>', 'Bank data is a list' );
	t.ok( longText.prepend.args[0][0].addClass.withArgs( 'bank-info' ).calledOnce );
	t.ok( longText.prepend.args[0][0].append.calledOnce, 'Bank data put before text' );

	t.equals( longText.prepend.args[0][0].append.args[0][0].toString(), '<dt>', 'Bank data IBAN title set' );
	t.ok( longText.prepend.args[0][0].append.args[0][0].text.withArgs( 'IBAN' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[0][0].append.args[0][1].toString(), '<dd>', 'Bank data IBAN set' );
	t.ok( longText.prepend.args[0][0].append.args[0][1].text.withArgs( '4711' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[0][0].append.args[0][2].toString(), '<dt>', 'Bank data BIC title set' );
	t.ok( longText.prepend.args[0][0].append.args[0][2].text.withArgs( 'BIC' ).calledOnce, 'Bank data IBAN set' );

	t.equals( longText.prepend.args[0][0].append.args[0][3].toString(), '<dd>', 'Bank data BIC set' );
	t.ok( longText.prepend.args[0][0].append.args[0][3].text.withArgs( '8888' ).calledOnce, 'Bank data IBAN set' );

	delete global.$;

	t.end();
} );

test( 'Fallback text is used when value does not correspond to text map', function ( t ) {
	var container = createElement(),
		text = createElement(),
		handler = objectAssign( Object.create( SectionInfo.SectionInfo ), {
			container: container,

			text: text,

			valueTextMap: { 'BEZ': 'Lastschrift', 'PPL': 'Paypal' }
		} );

	text.data.withArgs( 'empty-text' ).returns( 'Bitcoin' );

	handler.update( 'BTC' );

	t.ok( text.data.withArgs( 'empty-text' ).calledOnce, 'Fetches default text' );
	t.ok( text.text.withArgs( 'Bitcoin' ).calledOnce, 'Payment type is set' );

	t.end();
} );

test( 'Missing features are gently skipped', function ( t ) {
	var container = createElement(),
		handler = objectAssign( Object.create( SectionInfo.PaymentTypeSectionInfo ), {
			container: container,

			icon: null,
			text: null,
			longText: null,

			valueIconMap: { 'BEZ': 'icon-BEZ', 'PPL': 'icon-PPL' },
			valueTextMap: { 'BEZ': 'Lastschrift', 'PPL': 'Paypal' },
			valueLongTextMap: { 'BEZ': 'Will be deducted', 'PPL': 'Forward to PPL' }
		} );

	handler.update( 'BEZ', '', '', { dataEntered: false, isValid: null } );

	t.ok( container, 'elements injected as null have no methods called upon, cause no errors' );

	t.end();
} );

test( 'Instance correctly detects and applies sub-elements', function ( t ) {
	var container = createElement(),
		icon = createElement(),
		text = createElement(),
		longText = createElement()
	;

	container.find.withArgs( 'i' ).returns( icon );
	container.find.withArgs( '.text' ).returns( text );
	container.find.withArgs( '.info-detail' ).returns( longText );

	var handler = SectionInfo.createInstance( {}, container );

	t.deepEquals( handler.container, container );
	t.deepEquals( handler.icon, icon );
	t.deepEquals( handler.text, text );
	t.deepEquals( handler.longText, longText );

	t.ok( container.find.withArgs( 'i' ).calledOnce );
	t.ok( container.find.withArgs( '.text' ).calledOnce );
	t.ok( container.find.withArgs( '.info-detail' ).calledOnce );

	t.end();
} );

test( 'Instance is created with properties applied', function ( t ) {
	var container = createElement(),
		iconMap = { 'a': 1 },
		textMap = { 'a': 2 },
		longTextMap = { 'a': 3 },
		additionalProperties = { 'alpha': 'gamma' }
	;

	var handler = SectionInfo.createInstance( {}, container, iconMap, textMap, longTextMap, additionalProperties );

	t.deepEquals( handler.valueIconMap, iconMap );
	t.deepEquals( handler.valueTextMap, textMap );
	t.deepEquals( handler.valueLongTextMap, longTextMap );

	t.deepEquals( handler.alpha, 'gamma' );

	t.end();
} );

test( 'Proxy forwards calls and arguments', function ( t ) {
	var widgetOneDom = createElement(),
		widgetTwoDom = createElement(),
		fakeType = {
			update: sinon.stub()
		},
		// IRL a jQuery object that matched multiple DOM nodes
		containers = {
			get: sinon.stub().returns( [ widgetOneDom, widgetTwoDom ] )
		}
	;

	global.$ = sinon.stub();
	global.$.returnsArg( 0 ); // pretend to extend the DOM element given to jQuery. We don't but have all methods stubbed

	var proxy = SectionInfo.createProxy( fakeType, containers, {}, {}, {}, {} );

	proxy.update(  'a', 'b', 'c' );

	t.ok( proxy.widgets instanceof Array );
	t.equals( proxy.widgets.length, 2 );
	t.deepEquals( proxy.widgets[ 0 ].update.firstCall.args, [ 'a', 'b', 'c' ] );
	t.deepEquals( proxy.widgets[ 1 ].update.firstCall.args, [ 'a', 'b', 'c' ] );

	delete global.$;
	t.end();
} );
