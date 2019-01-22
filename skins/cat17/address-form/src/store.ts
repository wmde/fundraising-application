import Vue from "vue";
import Vuex, { StoreOptions } from "vuex";
import { Validity } from './lib/validation_states';
import { Helper } from './mixins/helper';
import { AddressState, Payload, Form, FormData, ValidationResult } from './types';

const VALIDATE_INPUT: string = 'VALIDATE_INPUT';
const MARK_EMPTY_FIELD_INVALID: string = 'MARK_EMPTY_FIELD_INVALID';
const BEGIN_ADDRESS_VALIDATION: string = 'BEGIN_ADDRESS_VALIDATION';
const FINISH_ADDRESS_VALIDATION: string = 'FINISH_ADDRESS_VALIDATION';
const STORE_VALUES: string = 'STORE_VALUES';
const ADDRESS_FIELD_VALIDATOR_NAMES: Array<string> = [
	'addressType',
	'salutation', 'title', 'firstName', 'lastName',
	'companyName',
	'street', 'postcode', 'city', 'country'
]
let transport = new WMDE.JQueryTransport();

Vue.use(Vuex);

const store: StoreOptions<AddressState> = {
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
        [STORE_VALUES](state, payload): void {
            payload.forEach((field: FormData) => {
               state.form[field.name].value = field.value;
           });
        },
        [VALIDATE_INPUT](state, payload): void {
            payload.forEach((field: FormData) => {
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
                        dataEntered: true,
                        isValid: Helper.inputIsValid(field.value, field.pattern)
                    };
                }
            });
		},
		[MARK_EMPTY_FIELD_INVALID](state, payload): void {
			payload.forEach((field: FormData) => {
                if (!field.optionalField && state.form[field.name].isValid === Validity.INCOMPLETE) {
                    state.form[field.name].isValid = Validity.INVALID;
				}
			});
		},
		[BEGIN_ADDRESS_VALIDATION](state): void {
			state.isValidating = true;
		},
        [FINISH_ADDRESS_VALIDATION](state, payload: ValidationResult): void {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach(name => {
                if (state.form[name].dataEntered === true) {
                    state.form[name] = {
                        ...state.form[name],
                        isValid: state.form[name].isValid !== false && !payload.messages[name] ? Validity.VALID : Validity.INVALID
					};
				}
			});
			state.isValidating = false;
		}
	},
	getters: {
		validity(state, contentName: string) {
            return state.form[contentName].isValid;
		}
	},
	actions: {
        storeAddressFields({ commit }, payload: Payload) {
            commit('STORE_VALUES', payload.formData);
            commit('VALIDATE_INPUT', payload.formData);
            commit('MARK_EMPTY_FIELD_INVALID', payload.formData);
            if (Object.keys(this.state.form).filter(field => this.state.form[field].isValid === Validity.INVALID ).length === 0 ) {
                commit('BEGIN_ADDRESS_VALIDATION', payload.formData);
                return transport.postData(payload.validateAddressURL, payload.formData)
                    .then( (validationResult: ValidationResult) => commit('FINISH_ADDRESS_VALIDATION', validationResult));
            }
        }
	}
}

export default new Vuex.Store<AddressState>(store);