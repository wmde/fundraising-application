import { MutationTree } from 'vuex';
import { Payment } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';
import {
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT,
	SET_AMOUNT_VALIDITY,
	SET_BANK_ACCOUNT_ID,
	SET_BANK_ACCOUNT_ID_VALIDITY,
	SET_BANK_ID,
	SET_BANK_ID_VALIDITY,
	SET_INTERVAL,
	SET_TYPE,
	SET_TYPE_VALIDITY,
} from '@/store/payment/mutationTypes';

export const mutations: MutationTree<Payment> = {
	[ MARK_EMPTY_AMOUNT_INVALID ]( state: Payment ) {
		const numericAmount = Number( state.values.amount );
		state.validity.amount = ( isNaN( numericAmount ) || numericAmount <= 0 ) ?
			Validity.INVALID : Validity.VALID;
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: Payment ) {
		for ( const prop in state.values ) {
			if ( state.values[ prop ] === '' ) {
				state.validity[ prop ] = Validity.INVALID;
			}
		}
	},
	[ SET_AMOUNT_VALIDITY ]( state: Payment, validity: Validity ) {
		state.validity.amount = validity;
	},
	[ SET_TYPE_VALIDITY ]( state: Payment ) {
		state.validity.type = state.values.type === '' ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_AMOUNT ]( state: Payment, amount ) {
		state.values.amount = amount;
	},
	[ SET_INTERVAL ]( state: Payment, interval ) {
		state.values.interval = interval;
	},
	[ SET_TYPE ]( state: Payment, type ) {
		state.values.type = type;
	},
	[ SET_BANK_ACCOUNT_ID ]( state: Payment, accountId ) {
		state.values.accountId = accountId;
	},
	[ SET_BANK_ACCOUNT_ID_VALIDITY ]( state: Payment, validity: Validity ) {
		state.validity.accountId = validity;
	},
	[ SET_BANK_ID ]( state: Payment, bankId ) {
		state.values.bankId = bankId;
	},
	[ SET_BANK_ID_VALIDITY ]( state: Payment, validity: Validity ) {
		state.validity.bankId = validity;
	},
};
