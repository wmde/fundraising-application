import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	initializeAddress,
	validateAddress,
	validateEmail,
	setAddressType,
	setAddressField,
	setReceiptOptOut,
	setDate,
	setMembershipType,
} from '@/store/membership_address/actionTypes';
import { MembershipAddressState, InputField, InitialMembershipData, AddressState } from '@/view_models/Address';
import { ValidationResponse } from '@/store/ValidationResponse';
import { addressTypeFromName, AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import { MARK_EMPTY_FIELDS_INVALID } from '@/store/membership_address/mutationTypes';
import { Validity } from '@/view_models/Validity';

export const actions = {
	[ initializeAddress ]( context: ActionContext<MembershipAddressState, any>, initialData: InitialMembershipData ) {
		context.commit( 'SET_ADDRESS_TYPE', addressTypeFromName( initialData.addressType ) );
		Object.entries( initialData ).forEach( ( [ name, value ] ) => {
			if ( name === 'addressType' ) {
				return;
			}
			if ( !value ) {
				return;
			}
			context.commit( 'SET_ADDRESS_FIELD', { name, value } );
			// We consider all non-empty values valid because they come from the donation and were validated there
			context.commit( 'SET_ADDRESS_FIELD_VALIDITY', { name, validity: Validity.VALID } );
		} );
	},
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
	[ validateEmail ]( context: ActionContext<MembershipAddressState, any>, validateEmailUrl: string ) {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
		if ( !context.getters.requiredFieldsAreValid ) {
			return Promise.resolve( { status: 'ERR', messages: [] } );
		}

		context.commit( 'BEGIN_EMAIL_VALIDATION' );
		const bodyFormData = new FormData();
		bodyFormData.append( 'email', context.state.values.email );

		return axios( validateEmailUrl, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
			context.commit( 'FINISH_EMAIL_VALIDATION', validationResult.data );
			return validationResult.data;
		} );

	},
	[ setAddressType ]( context: ActionContext<MembershipAddressState, any>, type: AddressTypeModel ) {
		context.commit( 'SET_ADDRESS_TYPE', type );
		if ( type === AddressTypeModel.COMPANY && context.getters.membershipType === MembershipTypeModel.ACTIVE ) {
			context.commit( 'SET_MEMBERSHIP_TYPE_VALIDITY', Validity.INVALID );
		}
	},
	[ setDate ]( context: ActionContext<MembershipAddressState, any>, date: string ) {
		context.commit( 'SET_DATE', date );
	},
	[ setReceiptOptOut ]( context: ActionContext<MembershipAddressState, any>, optOut: boolean ) {
		context.commit( 'SET_RECEIPT_OPTOUT', optOut );
	},
	[ setMembershipType ]( context: ActionContext<MembershipAddressState, any>, type: MembershipTypeModel ) {
		context.commit( 'SET_MEMBERSHIP_TYPE', type );
		context.commit( 'SET_MEMBERSHIP_TYPE_VALIDITY', Validity.VALID );
	},

};
