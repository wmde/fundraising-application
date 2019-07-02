import { MutationTree } from 'vuex';
import { BankAccount } from '@/view_models/BankAccount';
import { Validity } from '@/view_models/Validity';
import {
	SET_BANK_DATA_VALIDITY,
	SET_BANKDATA, SET_BANKNAME,
} from '@/store/bankdata/mutationTypes';

export const mutations: MutationTree<BankAccount> = {
	[ SET_BANKDATA ]( state: BankAccount, bankData: any ) {
		state.values.iban = bankData.accountId;
		state.values.bic = bankData.bankId;
	},
	[ SET_BANKNAME ]( state: BankAccount, bankName: string ) {
		state.values.bankName = bankName;
	},
	[ SET_BANK_DATA_VALIDITY ]( state: BankAccount, validity: Validity ) {
		state.validity.bankdata = validity;
	},
};
