import { MutationTree } from 'vuex';
import { Validity } from '@/view_models/Validity';
import {
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT,
	SET_AMOUNT_VALIDITY,
	SET_INITIALIZED,
	SET_INTERVAL,
	SET_TYPE,
	SET_TYPE_VALIDITY,
	SET_IS_VALIDATING,
} from '@/store/payment/mutationTypes';
import { DonationPayment } from '@/store/payment/types';

export const mutations: MutationTree<DonationPayment> = {
	[ MARK_EMPTY_AMOUNT_INVALID ]( state: DonationPayment ) {
		const numericAmount = Number( state.values.amount );
		state.validity.amount = ( isNaN( numericAmount ) || numericAmount <= 0 ) ?
			Validity.INVALID : Validity.VALID;
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: DonationPayment ) {
		for ( const prop in state.values ) {
			if ( state.values[ prop ] === '' ) {
				state.validity[ prop ] = Validity.INVALID;
			}
		}
	},
	[ SET_AMOUNT_VALIDITY ]( state: DonationPayment, validity: Validity ) {
		state.validity.amount = validity;
	},
	[ SET_TYPE_VALIDITY ]( state: DonationPayment ) {
		state.validity.type = state.values.type === '' ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_AMOUNT ]( state: DonationPayment, amount ) {
		state.values.amount = amount;
	},
	[ SET_INTERVAL ]( state: DonationPayment, interval ) {
		state.values.interval = interval;
	},
	[ SET_TYPE ]( state: DonationPayment, type ) {
		state.values.type = type;
	},
	[ SET_IS_VALIDATING ]( state: DonationPayment, isValidating: boolean ) {
		state.isValidating = isValidating;
	},
	[ SET_INITIALIZED ]( state: DonationPayment, initialized: boolean ) {
		state.initialized = initialized;
	},
};
