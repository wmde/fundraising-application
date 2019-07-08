import { Module } from 'vuex';
import { Payment } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';
import { actions } from '@/store/membership_fee/actions';
import { getters } from '@/store/membership_fee/getters';
import { mutations } from '@/store/membership_fee/mutations';

export default function (): Module<Payment, any> {
	const state: Payment = {
		validity: {
			fee: Validity.INCOMPLETE,
			interval: Validity.INCOMPLETE,
			type: Validity.VALID,
		},
		values: {
			fee: '', // membership fee in cents
			interval: '',
			type: 'BEZ',
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
