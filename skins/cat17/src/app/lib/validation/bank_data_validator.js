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
