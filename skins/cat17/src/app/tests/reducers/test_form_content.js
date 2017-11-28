'use strict';

var test = require( 'tape' ),
	deepFreeze = require( 'deep-freeze' ),
	formContent = require( '../../lib/reducers/form_content' ).formContent;

test( 'SELECT_AMOUNT sets amount and isCustomAmount', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 5, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: 5 } } ), expectedState );
	t.end();
} );

test( 'SELECT_AMOUNT keeps amount if selected amount is null', function ( t ) {
	var stateBefore = { amount: 99, isCustomAmount: true },
		expectedState = { amount: 99, isCustomAmount: false };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'SELECT_AMOUNT', payload: { amount: null } } ), expectedState );
	t.end();
} );

test( 'INPUT_AMOUNT sets amount and isCustomAount', function ( t ) {
	var stateBefore = { amount: 5, isCustomAmount: false },
		expectedState = { amount: 4223, isCustomAmount: true };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, { type: 'INPUT_AMOUNT', payload: { amount: 4223 } } ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT changes the field', function ( t ) {
	var stateBefore = { paymentType: 'PPL', amount: 0 },
		expectedState = { paymentType: 'BEZ', amount: 0 },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'BEZ', contentName: 'paymentType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'CHANGE_CONTENT throws an error if the field name is not allowed', function ( t ) {
	var action = { type: 'CHANGE_CONTENT', payload: { value: 'supercalifragilistic', contentName: 'unknownField' } };

	t.throws( function () {
		formContent( {}, action );
	} );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to private, company name is cleared', function ( t ) {
	var stateBefore = { companyName: 'Globex Corp', addressType: 'firma' },
		expectedState = { companyName: '', addressType: 'person' },
		action = { type: 'CHANGE_CONTENT', payload: { value: 'person', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to company, names are cleared', function ( t ) {
	var stateBefore = {
			salutation: 'Herr',
			title: 'Dr.',
			firstName: 'Hank',
			lastName: 'Scorpio',
			addressType: 'privat'
		},
		expectedState = {
			salutation: '',
			title: '',
			firstName: '',
			lastName: '',
			addressType: 'firma'
		},
		action = { type: 'CHANGE_CONTENT', payload: { value: 'firma', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets address type to anonymous, all personal data fields are cleared', function ( t ) {
	var stateBefore = {
			companyName: '',
			salutation: 'Herr',
			title: 'Dr.',
			firstName: 'Hank',
			lastName: 'Scorpio',
			street: 'Hammock District',
			postcode: '12345',
			city: 'Cypress Creek',
			addressType: 'person',
			email: 'hank@globex.com'
		},
		expectedState = {
			companyName: '',
			salutation: '',
			title: '',
			firstName: '',
			lastName: '',
			street: '',
			postcode: '',
			city: '',
			addressType: 'anonym',
			email: ''
		},
		action = { type: 'CHANGE_CONTENT', payload: { value: 'anonym', contentName: 'addressType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets payment type to direct debit and address type is anonymous, address type is switched to person', function ( t ) {
	var stateBefore = {
			paymentType: 'PPL',
			addressType: 'anonym'
		},
		expectedState = {
			paymentType: 'BEZ',
			addressType: 'person'
		},
		action = { type: 'CHANGE_CONTENT', payload: { value: 'BEZ', contentName: 'paymentType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT sets membership type to active, address type is switched to person', function ( t ) {
	var stateBefore = {
			membershipType: 'sustaining',
			addressType: 'firma'
		},
		expectedState = {
			membershipType: 'active',
			addressType: 'person'
		},
		action = { type: 'CHANGE_CONTENT', payload: { value: 'active', contentName: 'membershipType' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT is passed a value surrounded by whitespaces, the value is trimmed', function ( t ) {
	var stateBefore = { name: '  Jack L. Hide   ' },
		expectedState = { name: 'Jack L. Hide' },
		action = { type: 'CHANGE_CONTENT', payload: { value: '  Jack L. Hide  ', contentName: 'name' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'When CHANGE_CONTENT is passed a non-string value, the value is not trimmed', function ( t ) {
	var stateBefore = { amount: 500 },
		expectedState = { amount: 500 },
		action = { type: 'CHANGE_CONTENT', payload: { value: 500, contentName: 'amount' } };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION sets bank data when status is OK', function ( t ) {
	var stateBefore = { iban: '', bic: '', accountNumber: '', bankCode: '', bankName: '' },
		expectedState = {
			iban: 'DE12500105170648489890',
			bic: 'INGDDEFFXXX',
			accountNumber: '0648489890',
			bankCode: '50010517',
			bankName: 'ING-DiBa'
		},
		action = { type: 'FINISH_BANK_DATA_VALIDATION', payload: {
			status: 'OK',
			iban: 'DE12500105170648489890',
			bic: 'INGDDEFFXXX',
			account: '0648489890',
			bankCode: '50010517',
			bankName: 'ING-DiBa'
		} };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), expectedState );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION does not modify state data when status is not OK', function ( t ) {
	var stateBefore = { iban: '', bic: '', accountNumber: '', bankCode: '', bankName: '' },
		action = { type: 'FINISH_BANK_DATA_VALIDATION', payload: {
			status: 'ERR',
			message: 'Invalid BIC'
		} };

	deepFreeze( stateBefore );
	t.equal( formContent( stateBefore, action ), stateBefore );
	t.end();
} );

test( 'FINISH_BANK_DATA_VALIDATION does not clear BIC when it is not passed in validation response', function ( t ) {
	var stateBefore = { iban: 'AT022050302101023600', bic: 'SPIHAT22XXX', accountNumber: '', bankCode: '', bankName: '' },
		action = { type: 'FINISH_BANK_DATA_VALIDATION', payload: {
			status: 'OK',
			iban: 'AT022050302101023600'
		} };

	deepFreeze( stateBefore );
	t.deepEqual( formContent( stateBefore, action ), stateBefore );
	t.end();
} );
