import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	BankAccount, BankAccountData,
	BankAccountRequest,
	BankAccountResponse,
} from '@/view_models/BankAccount';
import {
	initializeBankData, markBankDataAsIncomplete, markBankDataAsInvalid,
	markEmptyValuesAsInvalid,
	setBankData,
} from '@/store/bankdata/actionTypes';
import {
	MARK_BANKDATA_INCOMPLETE,
	MARK_EMPTY_FIELDS_INVALID,
	SET_BANK_DATA_VALIDITY,
	SET_BANKDATA, SET_BANKNAME, SET_IS_VALIDATING,
} from '@/store/bankdata/mutationTypes';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ setBankData ]( context: ActionContext<BankAccount, any>, payload: BankAccountRequest ): void {
		context.commit( SET_IS_VALIDATING, true );
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
			context.commit( SET_IS_VALIDATING, false );
		} );
	},
	[ initializeBankData ]( context: ActionContext<BankAccount, any>, payload: BankAccountData & { bankName: string} ): void {
		if ( payload.accountId === '' ) {
			return;
		}
		context.commit( SET_BANKDATA, {
			accountId: payload.accountId,
			bankId: payload.bankId,
		} );
		context.commit( SET_BANKNAME, payload.bankName );
		context.commit( SET_BANK_DATA_VALIDITY, Validity.VALID );
	},
	[ markEmptyValuesAsInvalid ]( context: ActionContext<BankAccount, any> ): void {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
	},
	[ markBankDataAsIncomplete ]( context: ActionContext<BankAccount, any> ): void {
		context.commit( MARK_BANKDATA_INCOMPLETE );
		context.commit( SET_BANKNAME, '' );
	},
	[ markBankDataAsInvalid ]( context: ActionContext<BankAccount, any> ): void {
		context.commit( SET_BANK_DATA_VALIDITY, Validity.INVALID );
		context.commit( SET_BANKNAME, '' );
	},
};
