import { ActionContext } from 'vuex';
import { MembershipFee } from '@/view_models/MembershipFee';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { ValidationResponse } from '@/store/ValidationResponse';
import axios, { AxiosResponse } from 'axios';

// Membership fee call

function postFeeData(
	context: ActionContext<MembershipFee, any>,
	validateFeeUrl: string,
	amount: string,
	interval: string,
	addressType: AddressTypeModel
): Promise<ValidationResponse> {
	const bodyFormData = new FormData();
	bodyFormData.append( 'amount', amount );
	bodyFormData.append( 'paymentIntervalInMonths', interval );
	bodyFormData.append( 'addressType', addressTypeName( addressType ) );
	return axios( validateFeeUrl, {
		method: 'post',
		data: bodyFormData,
		headers: { 'Content-Type': 'multipart/form-data' },
	} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
		return Promise.resolve( validationResult.data );
	} );
}

export function validateFeeDataRemotely(
	context: ActionContext<MembershipFee, any>,
	validateFeeUrl: string,
	feeValue: string,
	interval: string
): Promise<ValidationResponse> {
	const feeAmount = ( Number( feeValue ) / 100 ).toFixed( 2 );
	const paymentInterval = interval;
	const addressType = context.rootState.membership_address.addressType;
	return postFeeData( context, validateFeeUrl, feeAmount, paymentInterval, addressType );
}
