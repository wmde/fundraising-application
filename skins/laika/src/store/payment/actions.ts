import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import { Payment, AmountData, OptionData } from '@/view_models/Payment';
import { validateAmount, setAmount, setInterval, setOption, validateOption } from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_AMOUNT_SELECTION_INVALID,
	SET_AMOUNT_VALIDITY,
	SET_AMOUNT,
	SET_INTERVAL, SET_OPTION, MARK_EMPTY_OPTION_SELECTION_INVALID,
} from '@/store/payment/mutationTypes';

export const actions = {
	[ validateAmount ]( context: ActionContext<Payment, any>, amountData: AmountData ): void {
		context.commit( MARK_EMPTY_AMOUNT_SELECTION_INVALID, amountData );
	},
	[ validateOption ]( context: ActionContext<Payment, any> ): void {
		context.commit( MARK_EMPTY_OPTION_SELECTION_INVALID );
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
	[ setInterval ]( context: ActionContext<Payment, any>, payload: string ): void {
		context.commit( SET_INTERVAL, payload );
	},
	[ setOption ]( context: ActionContext<Payment, any>, payload: string ): void {
		context.commit( SET_OPTION, payload );
	},
};
