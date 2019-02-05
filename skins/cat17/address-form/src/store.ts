import Vue from 'vue';
import Vuex, {StoreOptions} from 'vuex';
import {Validity} from './lib/validation_states';
import {Helper} from './mixins/helper';
import {AddressState, InputField, Payload, ValidationResult} from './types';

const VALIDATE_INPUT: string = 'VALIDATE_INPUT';
const MARK_EMPTY_FIELD_INVALID: string = 'MARK_EMPTY_FIELD_INVALID';
const BEGIN_ADDRESS_VALIDATION: string = 'BEGIN_ADDRESS_VALIDATION';
const FINISH_ADDRESS_VALIDATION: string = 'FINISH_ADDRESS_VALIDATION';
const ADDRESS_FIELD_VALIDATOR_NAMES: Array<string> = [
	'addressType',
	'salutation', 'title', 'firstName', 'lastName',
	'companyName',
	'street', 'postcode', 'city', 'country'
];
const JQueryTransport = require('../../src/app/lib/jquery_transport').default;
let transport = new JQueryTransport();

Vue.use(Vuex);

const store: StoreOptions<AddressState> = {
	state: {
		isValidating: false,
		form: {
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
				isValid: Validity.VALID
			},
			addressType: {
				dataEntered: false,
				isValid: Validity.VALID
			}
		}
	},
	mutations: {
		[VALIDATE_INPUT](state, field: InputField) {
			if (field.value === '' && field.optionalField) {
				state.form[field.name] = {
					...state.form[field.name],
					dataEntered: false,
					isValid: Validity.INCOMPLETE
				};
			}
			else {
				state.form[field.name] = {
					...state.form[field.name],
					dataEntered: true,
					isValid: Helper.inputIsValid(field.value, field.pattern)
				};
			}
		},
		[MARK_EMPTY_FIELD_INVALID](state, payload) {
            Object.keys(payload).forEach((field: string) => {
					if (!payload[field].optionalField && state.form[payload[field].name].isValid === Validity.INCOMPLETE) {
						state.form[payload[field].name].isValid = Validity.INVALID;
					}
				});
		},
		[BEGIN_ADDRESS_VALIDATION](state) {
			state.isValidating = true;
		},
		[FINISH_ADDRESS_VALIDATION](state, payload) {
            if (payload.status === 'ERR') {
                ADDRESS_FIELD_VALIDATOR_NAMES.forEach(name => {
                    if (state.form[name].dataEntered === true) {
                        state.form[name] = {
                            ...state.form[name],
                            isValid: state.form[name].isValid !== false && !payload.messages[name] ? Validity.VALID : Validity.INVALID
                        };
                    }
                });
            }
			state.isValidating = false;
		}
	},
	getters: {
		validity: (state) => (contentName: string) => {
			return state.form[contentName].isValid;
		},
		invalidFields: (state) :Array<string> => {
			return Object.keys(state.form).filter(field => state.form[field].isValid === Validity.INVALID)
		},
		allFieldsAreValid: (state, getters) :boolean => {
			return getters.invalidFields.length === 0;
		}
	},
	actions: {
		validateInput({ commit }, field: InputField) {
			commit('VALIDATE_INPUT', field);
		},
		storeAddressFields({ commit, getters }, payload: Payload) {
			commit('MARK_EMPTY_FIELD_INVALID', payload.formData);
			if ( getters.allFieldsAreValid ) {
				commit('BEGIN_ADDRESS_VALIDATION');
				return transport.postData(payload.validateAddressURL, payload.formData)
					.then( (validationResult: ValidationResult) => {
						commit('FINISH_ADDRESS_VALIDATION', validationResult);
					});
			}
			return Promise.resolve();
		}
	}
}

export default new Vuex.Store<AddressState>(store);
