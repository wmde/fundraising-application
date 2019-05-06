import { MutationTree } from 'vuex';
import { Payment, AmountData } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';
import {
	MARK_EMPTY_FIELD_INVALID,
	SET_AMOUNT_VALIDITY,
	REGISTER_AMOUNT, REGISTER_INTERVAL, REGISTER_OPTION,
} from './mutationTypes';
import { AxiosResponse } from 'axios';

export const mutations: MutationTree<Payment> = {
	[ MARK_EMPTY_FIELD_INVALID ]( state: Payment, fields: AmountData ) {
		state.validity.amount = ( fields.amountCustomValue === '' && fields.amountValue === '' ) ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_AMOUNT_VALIDITY ]( state: Payment, validationResult: AxiosResponse ) {
		state.validity.amount = validationResult.data.status === 'ERR' ?
			Validity.INVALID : Validity.VALID;
	},
	[ REGISTER_AMOUNT ]( state: Payment, amount ) {
		state.values.amount = amount;
	},
	[ REGISTER_INTERVAL ]( state: Payment, interval ) {
		state.values.interval = interval;
	},
	[ REGISTER_OPTION ]( state: Payment, option ) {
		state.values.option = option;
	},
};
