import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	validateAddress,
	setAddressType,
	setEmail,
	setNewsletterOptIn,
	setAddressField,
	setReceiptOptOut,
	setDate,
} from '@/store/membership_address/actionTypes';
import { MembershipAddressState, InputField, Payload } from '@/view_models/Address';
import { ValidationResponse } from '@/store/ValidationResponse';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MARK_EMPTY_FIELDS_INVALID } from '@/store/membership_address/mutationTypes';

export const actions = {
	[ setAddressField ]( context: ActionContext<MembershipAddressState, any>, field: InputField ) {
		context.commit( 'SET_ADDRESS_FIELD', field );
		context.commit( 'VALIDATE_INPUT', field );
	},
	[ validateAddress ]( context: ActionContext<MembershipAddressState, any>, validateAddressUrl: string ) {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
		if ( !context.getters.requiredFieldsAreValid ) {
			return Promise.resolve( { status: 'ERR', messages: [] } );
		}

		context.commit( 'BEGIN_ADDRESS_VALIDATION' );
		const bodyFormData = new FormData();
		Object.keys( context.state.values ).forEach(
			field => bodyFormData.append( field, context.state.values[ field ] )
		);
		bodyFormData.append( 'addressType', addressTypeName( context.state.addressType ) );
		return axios( validateAddressUrl, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
			context.commit( 'FINISH_ADDRESS_VALIDATION', validationResult.data );
			return validationResult.data;
		} );

	},
	[ setAddressType ]( context: ActionContext<MembershipAddressState, any>, type: AddressTypeModel ) {
		context.commit( 'SET_ADDRESS_TYPE', type );
	},
	[ setEmail ]( context: ActionContext<MembershipAddressState, any>, email: string ) {
		context.commit( 'SET_EMAIL', email );
	},
	[ setDate ]( context: ActionContext<MembershipAddressState, any>, date: string ) {
		context.commit( 'SET_DATE', date );
	},
	[ setNewsletterOptIn ]( context: ActionContext<MembershipAddressState, any>, optIn: boolean ) {
		context.commit( 'SET_NEWSLETTER_OPTIN', optIn );
	},
	[ setReceiptOptOut ]( context: ActionContext<MembershipAddressState, any>, optOut: boolean ) {
		context.commit( 'SET_RECEIPT_OPTOUT', optOut );
	},

};
