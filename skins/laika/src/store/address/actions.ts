import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	validateInput,
	setAddressFields,
	setAddressType,
	setEmail,
	setNewsletterOptIn,
} from '@/store/address/actionTypes';
import { AddressState, InputField, Payload } from '@/view_models/Address';
import { Helper } from '@/store/util';
import { ValidationResponse } from '@/store/ValidationResponse';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const actions = {
	[ validateInput ]( context: ActionContext<AddressState, any>, field: InputField ) {
		context.commit( 'VALIDATE_INPUT', field );
	},
	[ setAddressFields ]( context: ActionContext<AddressState, any>, payload: Payload ) {
		context.commit( 'MARK_EMPTY_FIELD_INVALID', payload.formData );
		context.commit( 'SET_ADDRESS_FIELDS', payload.formData );
		if ( context.getters.allFieldsAreValid ) {
			context.commit( 'BEGIN_ADDRESS_VALIDATION' );
			const postData = Helper.formatPostData( payload.formData );
			const bodyFormData = new FormData();
			for ( const field of postData ) {
				bodyFormData.append( field, postData[ field ] );
			}
			return axios( payload.validateAddressUrl, {
				method: 'post',
				data: bodyFormData,
				headers: { 'Content-Type': 'multipart/form-data' },
			} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
				context.commit( 'FINISH_ADDRESS_VALIDATION', validationResult );
			} );
		}
		return Promise.resolve( true );
	},
	[ setAddressType ]( context: ActionContext<AddressState, any>, type: AddressTypeModel ) {
		context.commit( 'SET_ADDRESS_TYPE', type );
	},
	[ setEmail ]( context: ActionContext<AddressState, any>, email: string ) {
		context.commit( 'SET_EMAIL', email );
	},
	[ setNewsletterOptIn ]( context: ActionContext<AddressState, any>, optIn: boolean ) {
		context.commit( 'SET_NEWSLETTER_OPTIN', optIn );
	},

};
