import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import { Payment, AmountData, TypeData, IntervalData } from '@/view_models/Payment';
import {
	markEmptyAmountAsInvalid,
	setAmount,
	setInterval,
	setType,
	markEmptyValuesAsInvalid,
} from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_AMOUNT_INVALID, MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT_VALIDITY, SET_TYPE_VALIDITY,
	SET_AMOUNT, SET_INTERVAL, SET_TYPE,
} from '@/store/payment/mutationTypes';
import { ValidationResponse } from '@/store/ValidationResponse';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ markEmptyAmountAsInvalid ]( context: ActionContext<Payment, any> ): void {
		context.commit( MARK_EMPTY_AMOUNT_INVALID );
	},
	[ markEmptyValuesAsInvalid ]( context: ActionContext<Payment, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
		if ( context.getters[ 'payment/paymentDataIsValid' ] ) {
			// TODO Go to next page
		}
	},
	[ setAmount ]( context: ActionContext<Payment, any>, payload: any ): void {
		context.commit( SET_AMOUNT, payload.amountValue );
		const bodyFormData = new FormData();
		bodyFormData.append( 'amount', payload.amountValue );
		axios( payload.validateAmountURL, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
			const validity = validationResult.data.status === 'ERR' ?
				Validity.INVALID : Validity.VALID;
			context.commit( SET_AMOUNT_VALIDITY, validity );
		} );
	},
	[ setInterval ]( context: ActionContext<Payment, any>, payload: IntervalData ): void {
		context.commit( SET_INTERVAL, payload );
	},
	[ setType ]( context: ActionContext<Payment, any>, payload: TypeData ): void {
		context.commit( SET_TYPE, payload );
		context.commit( SET_TYPE_VALIDITY );
	},
};
