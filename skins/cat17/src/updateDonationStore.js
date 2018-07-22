import Vue from 'vue';
import Vuex from 'vuex';
import { getField, updateField } from 'vuex-map-fields';

Vue.use(Vuex);

const Store = new Vuex.Store( {
	state: {
		formData: {
			addressType: '',
			firstName: '',
			lastName: '',
			salutation: '',
			title: '',
			companyName: '',
			street: '',
			postcode: '',
			city: '',
			countryCode: 'DE',
			email: ''
		},
		validation: {
			firstName: null,
			lastName: null,
			salutation: null,
			title: null,
			companyName: null,
			street: null,
			postcode: null,
			city: null,
			countryCode: null,
			email: null
		}
	},
	getters: {
		formDataCanBeValidated: ( state ) => {
			const formData = state.formData;
			return formData.addressType &&
				formData.firstName;
		},
		getField
	},
	mutations: {
		updateField
	}
} );

export default Store;