import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
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
import { addressTypeName } from '@/view_models/AddressTypeModel';

export const actions = {
	[ markEmptyValuesAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
	[ markEmptyFeeAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FEE_INVALID );
	},
	[ setFee ]( context: ActionContext<MembershipFee, any>, payload: SetFeePayload ): void {
		context.commit( SET_FEE, payload.feeValue );
		const bodyFormData = new FormData();
		bodyFormData.append( 'amount', ( Number( payload.feeValue ) / 100 ).toFixed( 2 ) );
		bodyFormData.append( 'paymentIntervalInMonths', context.state.values.interval );
		bodyFormData.append( 'addressType', addressTypeName( context.rootState.membership_address.addressType ) );
		axios( payload.validateFeeUrl, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
			const validity = validationResult.data.status === 'ERR' ?
				Validity.INVALID : Validity.VALID;
			context.commit( SET_FEE_VALIDITY, validity );
		} );
	},
	[ setInterval ]( context: ActionContext<MembershipFee, any>, payload: IntervalData ): void {
		context.commit( SET_INTERVAL, payload );
		context.commit( SET_INTERVAL_VALIDITY );
	},
};
