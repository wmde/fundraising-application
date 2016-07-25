var _ = require( 'underscore' ),

	/**
	 * Convert server-side validation messages to initial state for the store
	 *
	 * @param {Object} violatedFields
	 * @returns {Object}
	 */
	createInitialStateFromViolatedFields = function ( violatedFields ) {
		var state = {
			validity: {}
		};
		if ( _.isEmpty( violatedFields ) ) {
			return {};
		}

		if ( violatedFields.betrag ) {
			state.validity.amount = false;
		}

		if ( violatedFields.zahlweise ) {
			state.validity.amount = false;
		}

		return state;
	};

module.exports = {
	createInitialStateFromViolatedFields: createInitialStateFromViolatedFields
};
