import Vue from "vue";
import Vuex from "vuex";
import {ValidationStates, Validity} from '../../src/app/lib/validation/validation_states';

const INITIALIZE_VALIDATION = 'INITIALIZE_VALIDATION';
const VALIDATE_INPUT = 'VALIDATE_INPUT';
const MARK_EMPTY_FIELD_INVALID = 'MARK_EMPTY_FIELD_INVALID';
const BEGIN_ADDRESS_VALIDATION = 'BEGIN_ADDRESS_VALIDATION';
const FINISH_ADDRESS_VALIDATION = 'FINISH_ADDRESS_VALIDATION';
const ADDRESS_FIELD_VALIDATOR_NAMES = [
	'addressType',
	'salutation', 'title', 'firstName', 'lastName',
	'companyName',
	'street', 'postcode', 'city', 'country'
]

Vue.use(Vuex);

export default new Vuex.Store({
	state: {
		initialValidationResult: Validity.INCOMPLETE,
		isValidating: false,
		salutation: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		title: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		firstName: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		lastName: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		companyName: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		street: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		postcode: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		city: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		},
		country: {
			dataEntered: false,
			isValid: Validity.INCOMPLETE
		}
	},
	mutations: {
		[INITIALIZE_VALIDATION](state, payload) {
			state.initialValidationResult = payload.initialValidationResult;
		},
		[VALIDATE_INPUT](state, payload) {
			if (payload.value === '' && payload.optionalField === true) {
				state[payload.contentName] = {
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				};
			}
			else {
				state[payload.contentName] = {
					dataEntered: true,
					isValid: inputIsValid(payload.value, payload.pattern)
				};
			}
		},
		[MARK_EMPTY_FIELD_INVALID](state, payload) {
			payload.requiredFields.forEach(field => {
				if (field.isValid === Validity.INCOMPLETE) {
					state[field].isValid = Validity.INVALID;
				}
			});
			payload.neutralFields.forEach(field => {
				state[field].isValid = Validity.INCOMPLETE;
			});
		},
		[BEGIN_ADDRESS_VALIDATION](state, payload) {
			state.isValidating = true;
		},
		[FINISH_ADDRESS_VALIDATION](state, payload) {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach(name => {
				if (name.dataEntered === true) {
					state[name] = {
						dataEntered: true,
						isValid: state[name].isValid !== false && !payload.messages[name] ? Validity.VALID : Validity.INVALID
					};
				}
			});
			state.isValidating = false;
		}
	},
	getters: {
		validity(state, contentName) {
			return state[contentName].isValid;
		}
	},
	actions: {
		initializeValidation({commit}, violatedFields, initialValues, initialValidationResult) {
			return commit('INITIALIZE_VALIDATION', {
				violatedFields: violatedFields,
				initialValues: initialValues,
				initialValidationResult: initialValidationResult
			});
		},
		validateInput({commit}, contentName, newValue, pattern, optionalField) {
			return commit('VALIDATE_INPUT', {
				contentName: contentName,
				value: newValue,
				pattern: pattern,
				optionalField: optionalField || false
			});
		},
		markEmptyFieldsInvalid({commit}, requiredFields, neutralFields) {
			return commit('MARK_EMPTY_FIELD_INVALID', {
				requiredFields: requiredFields,
				neutralFields: neutralFields
			});
		},
		beginAddressValidation({commit}, formData) {
			return commit('BEGIN_ADDRESS_VALIDATION', {
				formData: formData
			});
		},
		finishAddressValidation({commit}, validationResult) {
			return commit('FINISH_ADDRESS_VALIDATION', {
				validationResult: validationResult
			});
		}
	}
});
