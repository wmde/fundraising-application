import { GetterTree } from 'vuex';
import { Payment } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';

export const getters: GetterTree<Payment, any> = {
	amountIsValid: function ( state: Payment ): boolean {
		return state.validity.amount !== Validity.INVALID;
	},
	optionIsValid: function ( state: Payment ): boolean {
		return state.validity.option !== Validity.INVALID;
	},
};
