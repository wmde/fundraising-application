import ValidationStates from './validation_states';

export default class AmountValidator {
	constructor( validationUrl, transport ) {
		this.validationUrl = validationUrl;
		this.transport = transport;
	}

	/**
	 * @param {Object} formValues
	 * @return {Promise}
	 */
	validate( formValues ) {

		if ( AmountValidator.formValuesHaveEmptyRequiredFields( formValues ) ) {
			return Promise.resolve( { status: ValidationStates.INCOMPLETE } );
		}
		return this.transport.postData(
			this.validationUrl,
			{
				amount: formValues.amount
			}
		).catch( function ( reason ) {
			return Promise.resolve( { status: ValidationStates.ERR, messages: { transportError: reason } } );
		} );
	}

	/**
	 * @param {Object} formValues
	 * @return {boolean}
	 * @private
	 */
	static formValuesHaveEmptyRequiredFields( formValues ) {
		return formValues.amount === 0;
	}
}
