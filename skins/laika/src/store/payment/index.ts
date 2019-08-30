import { Module } from 'vuex';
import { Validity } from '@/view_models/Validity';
import { actions } from '@/store/payment/actions';
import { getters } from '@/store/payment/getters';
import { mutations } from '@/store/payment/mutations';
import { DonationPayment } from '@/store/payment/types';

export default function (): Module<DonationPayment, any> {
	const state: DonationPayment = {
		isValidating: false,
		validity: {
			amount: Validity.INCOMPLETE,
			type: Validity.INCOMPLETE,
		},
		values: {
			amount: '', // amount in cents
			interval: '0',
			type: '',
		},
		initialized: false,
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
