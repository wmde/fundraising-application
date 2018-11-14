import ValidationStates from './validation_states';

export class BankDataValidator {

	constructor( validationUrlForSepaBankData, validationUrlForClassicBankData, transport ) {
		this.validationUrlForSepaBankData = validationUrlForSepaBankData;
		this.validationUrlForClassicBankData = validationUrlForClassicBankData;
		this.transport = transport;
	}

	/**
	 * @param {string} iban
	 * @return {Promise}
	 */
	validateSepaBankData( iban ) {
		iban = iban.replace( /\s/g, '' );
		if ( iban === '' ) {
			return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
		}
		return this.transport.getData(
			this.validationUrlForSepaBankData,
			{
				iban
			}
		);
	}

	/**
	 * @param {string} accountNumber
	 * @param {string} bankCode
	 * @return {Promise}
	 */
	validateClassicBankData( accountNumber, bankCode ) {
		accountNumber = accountNumber.replace( /\s/g, '' );
		bankCode = bankCode.replace( /\s/g, '' );
		if ( accountNumber === '' || bankCode === '' ) {
			return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
		}
		return this.transport.getData(
			this.validationUrlForClassicBankData,
			{
				accountNumber,
				bankCode
			}
		);
	}
}

export default BankDataValidator;
