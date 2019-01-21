import Vue from "vue";
import Vuex from "vuex";
import { Validity } from '../../src/app/lib/validation/validation_states';
import { Helper } from 'mixins/helper'

const VALIDATE_INPUT = 'VALIDATE_INPUT';
const MARK_EMPTY_FIELD_INVALID = 'MARK_EMPTY_FIELD_INVALID';
const BEGIN_ADDRESS_VALIDATION = 'BEGIN_ADDRESS_VALIDATION';
const FINISH_ADDRESS_VALIDATION = 'FINISH_ADDRESS_VALIDATION';
const STORE_VALUES = 'STORE_VALUES';
const ADDRESS_FIELD_VALIDATOR_NAMES = [
	'addressType',
	'salutation', 'title', 'firstName', 'lastName',
	'companyName',
	'street', 'postcode', 'city', 'country'
]
let transport = new WMDE.JQueryTransport();
Vue.use(Vuex);

export default new Vuex.Store({
	state: {
        isValidating: false,
        form: {
            salutation: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            title: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            firstName: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            lastName: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            companyName: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            street: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            postcode: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            city: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            },
            country: {
                dataEntered: false,
                value: '',
                isValid: Validity.INCOMPLETE
            }
    }
	},
	mutations: {
        [STORE_VALUES](state, payload) {
           payload.forEach(field => {
               state.form[field.name].value = field.value;
           });
        },
		[VALIDATE_INPUT](state, payload) {
            payload.forEach(field => {
                if (field.value === '' && field.optionalField === true) {
                    state.form[field.name] = {
                        ...state.form[field.name],
                        dataEntered: false,
                        isValid: Validity.INCOMPLETE
                    };
                }
                else {
                    state.form[field.name] = {
                        ...state.form[field.name],
                        dataEntered: false,
                        isValid: Validity.INCOMPLETE
                    };
                }
            });
		},
		[MARK_EMPTY_FIELD_INVALID](state, payload) {
			payload.forEach(field => {
                if (!field.optionalField && state.form[field].isValid === Validity.INCOMPLETE) {
                    state.form[field].isValid = Validity.INVALID;
				}
			});
		},
		[BEGIN_ADDRESS_VALIDATION](state) {
			state.isValidating = true;
		},
		[FINISH_ADDRESS_VALIDATION](state, payload) {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach(name => {
				if (name.dataEntered === true) {
                    state.form[name] = {
						dataEntered: true,
                        isValid: state.form[name].isValid !== false && !payload.messages[name] ? Validity.VALID : Validity.INVALID
					};
				}
			});
			state.isValidating = false;
		}
	},
	getters: {
		validity(state, contentName) {
            return state.form[contentName].isValid;
		}
	},
	actions: {
        storeAddressFields({ commit }, payload) {
            commit('STORE_VALUES', payload.formData);
            commit('VALIDATE_INPUT', payload.formData);
            commit('MARK_EMPTY_FIELD_INVALID', payload.formData);
            if (Object.keys(this.state.form).filter(field => this.state.form[field].isValid === Validity.INVALID ).length === 0 ) {
                commit('BEGIN_ADDRESS_VALIDATION', payload.formData);
                return transport.postData(payload.validateAddressURL, payload.formData)
                    .then(validationResult => commit('FINISH_ADDRESS_VALIDATION', validationResult));
            }
        }
	}
});
