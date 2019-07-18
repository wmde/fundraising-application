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
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';

function isNonNumeric( value: string ) {
	return value === '' || isNaN( Number( value ) );
}

function postFeeData( context: ActionContext<MembershipFee, any>, validateFeeUrl: string, amount: string, interval: string, addressType: AddressTypeModel ) {
	const bodyFormData = new FormData();
	bodyFormData.append( 'amount', amount );
	bodyFormData.append( 'paymentIntervalInMonths', interval );
	bodyFormData.append( 'addressType', addressTypeName( addressType ) );
	axios( validateFeeUrl, {
		method: 'post',
		data: bodyFormData,
		headers: { 'Content-Type': 'multipart/form-data' },
	} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
		const validity = validationResult.data.status === 'ERR' ?
			Validity.INVALID : Validity.VALID;
		context.commit( SET_FEE_VALIDITY, validity );
	} );
}

function validateFeeDataRemotely( context: ActionContext<MembershipFee, any>, validateFeeUrl: string, feeValue: string, interval: string ) {
	const feeAmount = ( Number( feeValue ) / 100 ).toFixed( 2 );
	const paymentInterval = interval;
	const addressType = context.rootState.membership_address.addressType;
	postFeeData( context, validateFeeUrl, feeAmount, paymentInterval, addressType );
}

export const actions = {
	[ markEmptyValuesAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
	[ markEmptyFeeAsInvalid ]( context: ActionContext<MembershipFee, any> ): void {
		context.commit( MARK_EMPTY_FEE_INVALID );
	},
	[ setFee ]( context: ActionContext<MembershipFee, any>, payload: SetFeePayload ): void {
		context.commit( SET_FEE, payload.feeValue );
		if ( isNonNumeric( payload.feeValue ) ) {
			context.commit( SET_FEE_VALIDITY, Validity.INVALID );
			return;
		}
		if ( isNonNumeric( context.state.values.interval ) ) {
			context.commit( SET_INTERVAL_VALIDITY );
			return;
		}
		validateFeeDataRemotely( context, payload.validateFeeUrl, payload.feeValue, context.state.values.interval );
	},
	[ setInterval ]( context: ActionContext<MembershipFee, any>, payload: IntervalData ): void {
		context.commit( SET_INTERVAL, payload.selectedInterval );
		context.commit( SET_INTERVAL_VALIDITY );
		if ( isNonNumeric( context.state.values.fee ) ) {
			return;
		}
		validateFeeDataRemotely( context, payload.validateFeeUrl, context.state.values.fee, context.state.values.interval );
	},
};
