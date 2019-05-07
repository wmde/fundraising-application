import { MutationTree } from 'vuex';
import { Payment, AmountData, OptionData } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';
import {
	MARK_EMPTY_AMOUNT_SELECTION_INVALID,
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT_VALIDITY,
	SET_AMOUNT, SET_INTERVAL, SET_OPTION,
} from '@/store/payment/mutationTypes';
import { AxiosResponse } from 'axios';

export const mutations: MutationTree<Payment> = {
	[ MARK_EMPTY_AMOUNT_SELECTION_INVALID ]( state: Payment, fields: AmountData ) {
		state.validity.amount = ( fields.amountCustomValue === '' && fields.amountValue === '' ) ?
			Validity.INVALID : Validity.VALID;
	},
	[ MARK_EMPTY_AMOUNT_INVALID ]( state: Payment, amount: string ) {
		state.validity.amount = amount === '' ? Validity.INVALID : Validity.VALID;
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: Payment ) {
		state.validity.option = ( state.values.option === '' ) ? Validity.INVALID : Validity.VALID;
	},
	[ SET_AMOUNT_VALIDITY ]( state: Payment, validationResult: AxiosResponse ) {
		state.validity.amount = validationResult.data.status === 'ERR' ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_AMOUNT ]( state: Payment, amount ) {
		state.values.amount = amount;
	},
	[ SET_INTERVAL ]( state: Payment, interval ) {
		state.values.interval = interval;
	},
	[ SET_OPTION ]( state: Payment, option ) {
		state.values.option = option;
	},
};
