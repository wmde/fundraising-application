import { ActionContext } from 'vuex';
import axios, { AxiosError, AxiosResponse } from 'axios';
import { Payment, AmountData } from '@/view_models/Payment';
import { validateAmount, registerAmount } from './actionTypes';
import {
	MARK_EMPTY_FIELD_INVALID,
	SET_AMOUNT_VALIDITY,
	REGISTER_AMOUNT,
} from './mutationTypes';

export const actions = {
	[ validateAmount ]( context: ActionContext<Payment, any>, amountData: AmountData ): void {
		context.commit( MARK_EMPTY_FIELD_INVALID, amountData );
	},
	[ registerAmount ]( context: ActionContext<Payment, any>, payload: any ): void {
        context.commit( REGISTER_AMOUNT, payload.amountValue );
        var bodyFormData = new FormData();
        bodyFormData.append('amount', payload.amountValue );
        axios( payload.validateAmountURL, {
            method: 'post',
            data: bodyFormData,
            headers: { 'Content-Type': 'multipart/form-data' }
        } ).then( ( validationResult: AxiosResponse ) => {
			context.commit( SET_AMOUNT_VALIDITY, validationResult );
		} ).catch( ( error: AxiosError ) => {
			// TODO throw an Exception
		} );
	},
};