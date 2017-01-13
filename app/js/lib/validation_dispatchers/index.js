module.exports = {
	createAmountValidationDispatcher: require( './amount' ),
	createAddressValidationDispatcher: require( './address' ),
	createBankDataValidationDispatcher: require( './bankdata' ),
	createEmailValidationDispatcher: require( './email' ),
	createSepaConfirmationValidationDispatcher: require( './sepa_confirmation' ),
	createFeeValidationDispatcher: require( './fee' )
};
