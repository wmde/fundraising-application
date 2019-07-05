import { Module } from 'vuex';
import { Validity } from '@/view_models/Validity';
import { actions } from '@/store/bankdata/actions';
import { getters } from '@/store/bankdata/getters';
import { mutations } from '@/store/bankdata/mutations';
import { BankAccount } from '@/view_models/BankAccount';

export default function (): Module<BankAccount, any> {
	const state: BankAccount = {
		validity: {
			bankdata: Validity.INCOMPLETE,
		},
		values: {
			iban: '',
			bic: '',
			bankName: '',
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
