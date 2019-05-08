import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import { Payment, AmountData, TypeData, IntervalData } from '@/view_models/Payment';
import {
	checkIfEmptyAmount,
	setAmount,
	setInterval,
	setType,
	markEmptyValuesAsInvalid,
} from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_AMOUNT_INVALID,
	MARK_EMPTY_FIELDS_INVALID,
	SET_AMOUNT_VALIDITY,
	SET_AMOUNT,
	SET_INTERVAL,
	SET_TYPE,
} from '@/store/payment/mutationTypes';

export const actions = {
	[ checkIfEmptyAmount ]( context: ActionContext<Payment, any>, amountData: AmountData ): void {
		context.commit( MARK_EMPTY_AMOUNT_INVALID, amountData );
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
		} ).then( ( validationResult: AxiosResponse ) => {
			context.commit( SET_AMOUNT_VALIDITY, validationResult );
		} );
	},
	[ setInterval ]( context: ActionContext<Payment, any>, payload: IntervalData ): void {
		context.commit( SET_INTERVAL, payload );
	},
	[ setType ]( context: ActionContext<Payment, any>, payload: TypeData ): void {
		context.commit( SET_TYPE, payload );
	},
};
