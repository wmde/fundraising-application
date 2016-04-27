'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createBankDataDisplayHandler = require( '../../lib/view_handler/display_bank_data' ).createBankDataDisplayHandler,
	createElement = function () {
		return {
			text: sinon.spy()
		};
	},
	createBankDataConfigSpy = function () {
		return {
			accountNumber: createElement(),
			bankCode: createElement(),
			bankName: createElement()
		};
	}
	;

test( 'Given sepa bank data, IBAN and BIC are displayed', function ( t ) {
	var bankDataConfig = createBankDataConfigSpy(),
		handler = createBankDataDisplayHandler( bankDataConfig );

	handler.update( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'sepa'
	} );

	t.ok( bankDataConfig.accountNumber.text.calledWith( 'DE12500105170648489890' ), 'account number is set to IBAN' );
	t.ok( bankDataConfig.bankCode.text.calledWith( 'INGDDEFFXXX' ), 'bank code is set to BIC'  );
	t.ok( bankDataConfig.bankName.text.calledWith( 'ING-DiBa' ), 'bank name is set'  );

	t.end();
} );

test( 'Given non-sepa bank data, IBAN and BIC are displayed', function ( t ) {
	var bankDataConfig = createBankDataConfigSpy(),
		handler = createBankDataDisplayHandler( bankDataConfig );

	handler.update( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'non-sepa'
	} );

	t.ok( bankDataConfig.accountNumber.text.calledWith( '0648489890' ), 'account number is set' );
	t.ok( bankDataConfig.bankCode.text.calledWith( '50010517' ), 'bank code is set'  );
	t.ok( bankDataConfig.bankName.text.calledWith( 'ING-DiBa' ), 'bank name is set'  );

	t.end();
} );

test( 'Bank data display handler checks if all elements are configured', function ( t ) {
	var bankDataWithMissingAccountNumber = {
		bankCode: createElement(),
		bankName: createElement()
	};
	t.throws( function () {
		createBankDataDisplayHandler( bankDataWithMissingAccountNumber );
	} );

	t.end();
} );

