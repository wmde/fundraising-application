import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	validateInput,
	storeAddressFields,
	setAddressType,
} from '@/store/address/actionTypes';
import { AddressState, InputField, Payload } from '@/view_models/Address';
import { Helper } from '@/store/util';
import { ValidationResponse } from '@/store/ValidationResponse';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const actions = {
	[ validateInput ]( context: ActionContext<AddressState, any>, field: InputField ) {
		context.commit( 'VALIDATE_INPUT', field );
	},
	[ storeAddressFields ]( context: ActionContext<AddressState, any>, payload: Payload ) {
		context.commit( 'MARK_EMPTY_FIELD_INVALID', payload.formData );
		if ( context.getters.allFieldsAreValid ) {
			context.commit( 'BEGIN_ADDRESS_VALIDATION' );
			const postData = Helper.formatPostData( payload.formData );
			const bodyFormData = new FormData();
			for ( const field in postData ) {
				bodyFormData.append( field, postData[ field ] );
			}
			axios( payload.validateAddressUrl, {
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
};
