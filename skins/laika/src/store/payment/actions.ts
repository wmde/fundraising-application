import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	IntervalData,
	Payment,
	TypeData,
	InitialPaymentValues,
} from '@/view_models/Payment';

import {
	initializePayment,
	markEmptyAmountAsInvalid,
	markEmptyValuesAsInvalid,
	setAmount,
	setInterval,
	setType,
} from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT,
	SET_AMOUNT_VALIDITY,
	SET_INTERVAL,
	SET_TYPE,
	SET_TYPE_VALIDITY,
} from '@/store/payment/mutationTypes';
import { ValidationResponse } from '@/store/ValidationResponse';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ initializePayment ]( context: ActionContext<Payment, any>, initialValues: InitialPaymentValues ): Promise<boolean> {
		let amountIsFilled = false, paymentIsFilled = false;
		if ( initialValues.amount !== '0' ) {
			context.commit( SET_AMOUNT, initialValues.amount );
			context.commit( SET_AMOUNT_VALIDITY, Validity.VALID );
			amountIsFilled = true;
		}

		if ( initialValues.type !== '' ) {
			context.commit( SET_TYPE, initialValues.type );
			context.commit( SET_TYPE_VALIDITY, Validity.VALID );
			paymentIsFilled = true;
		}
		context.commit( SET_INTERVAL, initialValues.paymentIntervalInMonths );

		return Promise.resolve( amountIsFilled && paymentIsFilled );
	},
	[ markEmptyValuesAsInvalid ]( context: ActionContext<Payment, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
	[ markEmptyAmountAsInvalid ]( context: ActionContext<Payment, any> ): void {
		context.commit( MARK_EMPTY_AMOUNT_INVALID );
	},
	[ setAmount ]( context: ActionContext<Payment, any>, payload: any ): void {
		context.commit( SET_AMOUNT, payload.amountValue );
		const bodyFormData = new FormData();
		bodyFormData.append( 'amount', payload.amountValue );
		axios( payload.validateAmountUrl, {
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
