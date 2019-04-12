import Vue from 'vue';
import Vuex, {StoreOptions, GetterTree, ActionTree, MutationTree, ActionContext } from 'vuex';
import {Helper} from './mixins/helper';
import {AddressState, InputField, Payload, ValidationResult, Validity} from './types';

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

Vue.use(Vuex);

const state: AddressState = {
	isValidating: false,
	form: {
		salutation: Validity.INCOMPLETE,
		title: Validity.INCOMPLETE,
		firstName: Validity.INCOMPLETE,
		lastName: Validity.INCOMPLETE,
		companyName: Validity.INCOMPLETE,
		street: Validity.INCOMPLETE,
		postcode: Validity.INCOMPLETE,
		city: Validity.INCOMPLETE,
		country: Validity.VALID,
		addressType: Validity.VALID
	}
}

export const mutations: MutationTree<AddressState> = {
	[VALIDATE_INPUT](state, field: InputField) {
		if (field.value === '' && field.optionalField) {
			state.form[field.name] = Validity.INCOMPLETE
		}
		else {
			state.form[field.name] = Helper.inputIsValid(field.value, field.pattern)
		}
	},
	[MARK_EMPTY_FIELD_INVALID](state, payload) {
		Object.keys(payload).forEach((field: string) => {
			const fieldName = payload[field];
			if (!fieldName.optionalField && state.form[fieldName.name] === Validity.INCOMPLETE) {
				state.form[fieldName.name] = Validity.INVALID;
				}
			});
	},
	[BEGIN_ADDRESS_VALIDATION](state) {
		state.isValidating = true;
	},
	[FINISH_ADDRESS_VALIDATION](state, payload) {
		if (payload.status === 'ERR') {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach(name => {
				if ( payload.messages[name] ) {
					state.form[name] = Validity.INVALID;
				}
			});
		}
		state.isValidating = false;
	}
}

export const getters: GetterTree<AddressState, any> = {
	invalidFields: (state: AddressState) :Array<string> => {
		return Object.keys(state.form).filter(field => state.form[field] === Validity.INVALID)
	},
	allFieldsAreValid: (state: AddressState, getters: GetterTree<AddressState, any> ) :boolean => {
		return getters.invalidFields.length === 0;
	}
}

export const actions: ActionTree<AddressState, any> = {
	validateInput({ commit }, field: InputField) {
		commit('VALIDATE_INPUT', field);
	},
	storeAddressFields(context: ActionContext<AddressState, any>, payload: Payload) {
		context.commit('MARK_EMPTY_FIELD_INVALID', payload.formData);
		if ( context.getters.allFieldsAreValid ) {
			context.commit('BEGIN_ADDRESS_VALIDATION');
			const postData = Helper.formatPostData(payload.formData);
			return payload.transport.postData(payload.validateAddressURL, postData)
				.then( (validationResult: ValidationResult) => {
					context.commit('FINISH_ADDRESS_VALIDATION', validationResult);
				});
		}
		return Promise.resolve( true );
	}
}

const store: StoreOptions<AddressState> = {
	state,
	mutations,
	getters,
	actions
}
export default new Vuex.Store<AddressState>(store);
