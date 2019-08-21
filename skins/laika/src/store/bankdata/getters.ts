import { GetterTree } from 'vuex';
import { BankAccount } from '@/view_models/BankAccount';
import { Validity } from '@/view_models/Validity';

export const getters: GetterTree<BankAccount, any> = {
	bankDataIsInvalid: function ( state: BankAccount ): boolean {
		return state.validity.bankdata === Validity.INVALID;
	},
	bankDataIsValid: function ( state: BankAccount ): boolean {
		return state.validity.bankdata === Validity.VALID;
	},
	getBankName: function ( state: BankAccount ): string {
		return state.values.bankName;
	},
	getAccountId: function ( state: BankAccount ): string {
		return state.values.iban;
	},
	getBankId: function ( state: BankAccount ): string {
		return state.values.bic;
	},
};
