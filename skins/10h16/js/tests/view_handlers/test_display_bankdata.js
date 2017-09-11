'use strict';

var test = require( 'tape' ),
	sinon = require( 'sinon' ),
	createBankDataDisplayHandler = require( '../../lib/view_handler/display_bank_data' ).createBankDataDisplayHandler,
	createElement = function () {
		return {
			text: sinon.spy()
		};
	}
	;

test( 'Given sepa bank data, IBAN and BIC are displayed', function ( t ) {
	var ibanElement = createElement(),
		bicElement = createElement(),
		bankNameElement = createElement(),
		handler = createBankDataDisplayHandler( ibanElement, bicElement, bankNameElement );

	handler.update( {
		iban: 'DE12500105170648489890',
		bic: 'INGDDEFFXXX',
		accountNumber: '0648489890',
		bankCode: '50010517',
		bankName: 'ING-DiBa',
		debitType: 'sepa'
	} );

	t.ok( ibanElement.text.calledWith( 'DE12500105170648489890' ), 'IBAN text is set' );
	t.ok( bicElement.text.calledWith( 'INGDDEFFXXX' ), 'BIC text is set'  );
	t.ok( bankNameElement.text.calledWith( 'ING-DiBa' ), 'Bank name is set'  );

	t.end();
} );
