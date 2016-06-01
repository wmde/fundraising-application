var _ = require( 'underscore' ),

	/**
	 * Convert server-side validation messages to initial state for the store
	 *
	 * @param {Object} violatedFields
	 * @returns {Object}
	 */
	createInitialStateFromViolatedFields = function ( violatedFields ) {
		var state = {
			validity: {},
			validationMessages: {}
		};
		if ( _.isEmpty( violatedFields ) ) {
			return {};
		}

		if ( violatedFields.betrag ) {
			state.validity.amount = false;
			state.validationMessages.amount = violatedFields.betrag;
		}

		if ( violatedFields.zahlweise ) {
			state.validity.amount = false;
			state.validationMessages.paymentType = violatedFields.zahlweise;
		}

		return state;
	};

module.exports = {
	createInitialStateFromViolatedFields: createInitialStateFromViolatedFields
};
