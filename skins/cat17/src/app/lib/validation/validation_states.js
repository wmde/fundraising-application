/**
 * These are the state names used payload FINISH_XXX_VALIDATION actions
 *
 * The servers sends either OK or ERR.
 * INCOMPLETE means "we don't have enough data to send to server"
 *
 * @type {{OK: string, ERR: string, INCOMPLETE: string}}
 */
export const ValidationStates = {
	OK: 'OK',
	ERR: 'ERR',
	INCOMPLETE: 'INCOMPLETE'
};

export default ValidationStates;

/**
 * These are the three validation *Result* types, used in the `validity`
 * section of the store.
 *
 * @type {{VALID: boolean, INVALID: boolean, INCOMPLETE: null}}
 */
export const Validity = {
	VALID: true,
	INVALID: false,
	INCOMPLETE: null
};
