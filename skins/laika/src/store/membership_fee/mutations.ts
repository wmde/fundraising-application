import { MutationTree } from 'vuex';
import { MembershipFee } from '@/view_models/MembershipFee';
import { Validity } from '@/view_models/Validity';
import {
	MARK_EMPTY_FEE_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_FEE,
	SET_FEE_VALIDITY,
	SET_INTERVAL,
	SET_INTERVAL_VALIDITY,
	SET_IS_VALIDATING,
	SET_TYPE,
	SET_TYPE_VALIDITY,
} from '@/store/membership_fee/mutationTypes';

export const mutations: MutationTree<MembershipFee> = {
	[ MARK_EMPTY_FEE_INVALID ]( state: MembershipFee ) {
		const numericFee = Number( state.values.fee );
		state.validity.fee = ( isNaN( numericFee ) || numericFee <= 0 ) ?
			Validity.INVALID : Validity.VALID;
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: MembershipFee ) {
		for ( const prop in state.values ) {
			if ( state.values[ prop ] === '' ) {
				state.validity[ prop ] = Validity.INVALID;
			}
		}
	},
	[ SET_FEE_VALIDITY ]( state: MembershipFee, validity: Validity ) {
		state.validity.fee = validity;
	},
	[ SET_INTERVAL_VALIDITY ]( state: MembershipFee ) {
		state.validity.interval = state.values.interval === '' ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_TYPE_VALIDITY ]( state: MembershipFee ) {
		state.validity.type = state.values.type === '' ?
			Validity.INVALID : Validity.VALID;
	},
	[ SET_FEE ]( state: MembershipFee, fee ) {
		state.values.fee = fee;
	},
	[ SET_INTERVAL ]( state: MembershipFee, interval ) {
		state.values.interval = interval;
	},
	[ SET_TYPE ]( state: MembershipFee, type ) {
		state.values.type = type;
	},
	[ SET_IS_VALIDATING ]( state: MembershipFee, isValidating ) {
		state.values.isValidating = isValidating;
	},
};
