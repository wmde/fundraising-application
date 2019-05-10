import { Module } from 'vuex';
import { AddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { actions } from '@/store/address/actions';
import { getters } from '@/store/address/getters';
import { mutations } from '@/store/address/mutations';

export default function (): Module<AddressState, any> {
	const state: AddressState = {
		isValidating: false,
		form: {
			salutation: Validity.INCOMPLETE,
			title: Validity.INCOMPLETE,
			firstName: Validity.INCOMPLETE,
			lastName: Validity.INCOMPLETE,
			companyName: Validity.VALID,
			street: Validity.INCOMPLETE,
			postcode: Validity.INCOMPLETE,
			city: Validity.INCOMPLETE,
			country: Validity.VALID,
			addressType: Validity.VALID,
		},
	};

	const namespaced = true;

	return {
		namespaced,
		state,
		getters,
		mutations,
		actions,
	};
}
