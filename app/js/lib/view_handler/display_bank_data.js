'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),
	BankDataDisplayHandler = {
		accountNumber: null,
		bankCode: null,
		bankName: null,
		update: function ( formContent ) {
			if ( formContent.debitType === 'sepa' ) {
				this.accountNumber.text( formContent.iban );
				this.bankCode.text( formContent.bic );
			} else {
				this.accountNumber.text( formContent.accountNumber );
				this.bankCode.text( formContent.bankCode );
			}
			this.bankName.text( formContent.bankName );
		}
	};

module.exports = {
	createBankDataDisplayHandler: function ( elementConfig ) {
		var expectedConfigProperties = [
				'accountNumber',
				'bankCode',
				'bankName'
			],
			unconfiguredElements = _.difference( expectedConfigProperties, _.keys( elementConfig ) );
		if ( unconfiguredElements.length > 0 ) {
			throw new Error( 'The following elements were not configured: ' + unconfiguredElements.join( ', ' ) );
		}
		return objectAssign( Object.create( BankDataDisplayHandler ), elementConfig );
	}
};
