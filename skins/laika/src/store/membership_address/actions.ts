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
import { AddressState, InputField, Payload } from '@/view_models/Address';
import { ValidationResponse } from '@/store/ValidationResponse';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MARK_EMPTY_FIELDS_INVALID } from '@/store/membership_address/mutationTypes';

export const actions = {
	[ setAddressField ]( context: ActionContext<AddressState, any>, field: InputField ) {
		context.commit( 'SET_ADDRESS_FIELD', field );
		context.commit( 'VALIDATE_INPUT', field );
	},
	[ validateAddress ]( context: ActionContext<AddressState, any>, validateAddressUrl: string ) {
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
	[ setAddressType ]( context: ActionContext<AddressState, any>, type: AddressTypeModel ) {
		context.commit( 'SET_ADDRESS_TYPE', type );
	},
	[ setEmail ]( context: ActionContext<AddressState, any>, email: string ) {
		context.commit( 'SET_EMAIL', email );
	},
	[ setDate ]( context: ActionContext<AddressState, any>, date: string ) {
		context.commit( 'SET_DATE', date );
	},
	[ setNewsletterOptIn ]( context: ActionContext<AddressState, any>, optIn: boolean ) {
		context.commit( 'SET_NEWSLETTER_OPTIN', optIn );
	},
	[ setReceiptOptOut ]( context: ActionContext<AddressState, any>, optOut: boolean ) {
		context.commit( 'SET_RECEIPT_OPTOUT', optOut );
	},

};
