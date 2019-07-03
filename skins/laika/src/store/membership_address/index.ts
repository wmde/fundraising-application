import { Module } from 'vuex';
import { MembershipAddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { actions } from '@/store/membership_address/actions';
import { getters } from '@/store/membership_address/getters';
import { mutations } from '@/store/membership_address/mutations';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export default function (): Module<MembershipAddressState, any> {
	const state: MembershipAddressState = {
		isValidating: false,
		addressType: AddressTypeModel.PERSON,
		receiptOptOut: false,
		values: {
			salutation: '',
			title: '',
			firstName: '',
			lastName: '',
			companyName: '',
			street: '',
			postcode: '',
			city: '',
			country: 'DE',
			email: '',
			date: '',
		},
		validity: {
			salutation: Validity.INCOMPLETE,
			title: Validity.VALID,
			firstName: Validity.INCOMPLETE,
			lastName: Validity.INCOMPLETE,
			companyName: Validity.INCOMPLETE,
			street: Validity.INCOMPLETE,
			postcode: Validity.INCOMPLETE,
			city: Validity.INCOMPLETE,
			country: Validity.VALID,
			email: Validity.INCOMPLETE,
			date: Validity.VALID,
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
