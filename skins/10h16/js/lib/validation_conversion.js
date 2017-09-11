var _ = require( 'underscore' ),

	/**
	 * Convert server-side validation messages to initial state for the store
	 *
	 * @param {Object} violatedFields
	 * @param {Object} initialValidationState
	 * @returns {Object}
	 */
	createInitialStateFromViolatedFields = function ( violatedFields, initialValidationState ) {
		var state = {
			validity: initialValidationState || {}
		};

		if ( _.isEmpty( violatedFields ) ) {
			return state;
		}

		if ( violatedFields.betrag ) {
			state.validity.paymentData = false;
		}

		if ( violatedFields.zahlweise ) {
			state.validity.paymentData = false;
		}

		return state;
	};

module.exports = {
	createInitialStateFromViolatedFields: createInitialStateFromViolatedFields
};
