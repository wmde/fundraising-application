import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import { Payment, TypeData, IntervalData, InitialPaymentValues } from '@/view_models/Payment';
import {
	initializePayment,
	markEmptyAmountAsInvalid,
	markEmptyValuesAsInvalid,
	setAmount,
	setInterval,
	setType,
	setBankId,
	setAccountId,
} from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT_VALIDITY,
	SET_TYPE_VALIDITY,
	SET_AMOUNT,
	SET_INTERVAL,
	SET_TYPE,
	SET_BANK_ACCOUNT_ID,
	SET_BANK_ACCOUNT_ID_VALIDITY,
	SET_BANK_ID,
	SET_BANK_ID_VALIDITY,
} from '@/store/payment/mutationTypes';
import { ValidationResponse } from '@/store/ValidationResponse';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ initializePayment ]( context: ActionContext<Payment, any>, initialValues: InitialPaymentValues ): Promise<boolean> {
		let amountIsFilled = false, paymentIsFilled = false;
		if ( initialValues.amount !== '0' ) {
			context.commit( SET_AMOUNT, initialValues.amount );
			amountIsFilled = true;
		}

		if ( initialValues.type !== '' ) {
			context.commit( SET_TYPE, initialValues.type );
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
	[ setAccountId ]( context: ActionContext<Payment, any>, payload: TypeData ): void {
		context.commit( SET_BANK_ACCOUNT_ID, payload );
		context.commit( SET_BANK_ACCOUNT_ID_VALIDITY );
	},
	[ setBankId ]( context: ActionContext<Payment, any>, payload: TypeData ): void {
		context.commit( SET_BANK_ID, payload );
		context.commit( SET_BANK_ID_VALIDITY );
	},
};
