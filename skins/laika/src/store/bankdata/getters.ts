import { GetterTree } from 'vuex';
import { BankAccount } from '@/view_models/BankAccount';
import { Validity } from '@/view_models/Validity';

export const getters: GetterTree<BankAccount, any> = {
	accountIsValid: function ( state: BankAccount ): boolean {
		return state.validity.accountId !== Validity.INVALID;
	},
	bankIsValid: function ( state: BankAccount ): boolean {
		return state.validity.bankId !== Validity.INVALID;
	},
	getBankName: function ( state: BankAccount ): string {
		return state.values.bankName;
	},
	getBankId: function ( state: BankAccount ): string {
		return state.values.bic;
	},
};
