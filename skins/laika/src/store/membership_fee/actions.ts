import { ActionContext } from 'vuex';

import {
	IntervalData,
	SetFeePayload,
	MembershipFee,
} from '@/view_models/MembershipFee';

import {
	markEmptyFeeAsInvalid,
	markEmptyValuesAsInvalid,
	setFee,
	setInterval,
} from '@/store/membership_fee/actionTypes';
import {
	MARK_EMPTY_FEE_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_FEE,
	SET_FEE_VALIDITY,
	SET_INTERVAL,
	SET_INTERVAL_VALIDITY,
} from '@/store/membership_fee/mutationTypes';
import { ValidationResponse } from '@/store/ValidationResponse';
import { Validity } from '@/view_models/Validity';
import { Helper } from '@/store/util';
import { validateFeeDataRemotely } from '@/store/axios';

export const actions = {
	[ markEmptyValuesAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
	[ markEmptyFeeAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FEE_INVALID );
	},
	[ setFee ]( context: ActionContext<MembershipFee, any>, payload: SetFeePayload ): void {
		context.commit( SET_FEE, payload.feeValue );
		if ( Helper.isNonNumeric( payload.feeValue ) ) {
			context.commit( SET_FEE_VALIDITY, Validity.INVALID );
			return;
		}
		if ( Helper.isNonNumeric( context.state.values.interval ) ) {
			context.commit( SET_INTERVAL_VALIDITY );
			return;
		}
		validateFeeDataRemotely(
			context,
			payload.validateFeeUrl,
			payload.feeValue,
			context.state.values.interval
		).then( ( validationResult: ValidationResponse ) => {
			context.commit( SET_FEE_VALIDITY, validationResult.status === 'ERR' ? Validity.INVALID : Validity.VALID );
		} );
	},
	[ setInterval ]( context: ActionContext<MembershipFee, any>, payload: IntervalData ): void {
		context.commit( SET_INTERVAL, payload.selectedInterval );
		context.commit( SET_INTERVAL_VALIDITY );
		if ( Helper.isNonNumeric( context.state.values.fee ) ) {
			return;
		}
		validateFeeDataRemotely(
			context,
			payload.validateFeeUrl,
			context.state.values.fee,
			context.state.values.interval
		).then( ( validationResult: ValidationResponse ) => {
			context.commit( SET_FEE_VALIDITY, validationResult.status === 'ERR' ? Validity.INVALID : Validity.VALID );
		} );
	},
};
