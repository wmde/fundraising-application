import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	BankAccount,
	BankAccountRequest,
	BankAccountResponse,
} from '@/view_models/BankAccount';
import {
	markEmptyValuesAsInvalid,
	setBankData,
} from '@/store/bankdata/actionTypes';
import {
	MARK_EMPTY_FIELDS_INVALID,
	SET_BANK_DATA_VALIDITY,
	SET_BANKDATA, SET_BANKNAME,
} from '@/store/bankdata/mutationTypes';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ setBankData ]( context: ActionContext<BankAccount, any>, payload: BankAccountRequest ): void {
		axios( payload.validationUrl, {
			method: 'get',
			headers: { 'Content-Type': 'multipart/form-data' },
			params: payload.requestParams,
		} ).then( ( validationResult: AxiosResponse<BankAccountResponse> ) => {
			const validity = validationResult.data.status === 'ERR' ? Validity.INVALID : Validity.VALID;
			context.commit( SET_BANK_DATA_VALIDITY, validity );
			if ( validity === Validity.VALID ) {
				context.commit( SET_BANKNAME, validationResult.data.bankName );
				context.commit( SET_BANKDATA, {
					accountId: validationResult.data.iban,
					bankId: validationResult.data.bic,
				} );
			} else {
				context.commit( SET_BANKNAME, '' );
			}
		} );
	},
	[ markEmptyValuesAsInvalid ]( context: ActionContext<BankAccount, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
};
