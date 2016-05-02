'use strict';

var objectAssign = require( 'object-assign' ),
	BankDataDisplayHandler = {
		ibanElement: null,
		bicElement: null,
		bankNameElement: null,
		update: function ( formContent ) {
			this.ibanElement.text( formContent.iban );
			this.bicElement.text( formContent.bic );
			this.bankNameElement.text( formContent.bankName );
		}
	};

module.exports = {
	createBankDataDisplayHandler: function ( ibanElement, bicElement, bankNameElement ) {
		return objectAssign( Object.create( BankDataDisplayHandler ), {
			ibanElement: ibanElement,
			bicElement: bicElement,
			bankNameElement: bankNameElement
		} );
	}
};
