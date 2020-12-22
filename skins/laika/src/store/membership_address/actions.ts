import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import {
	initializeAddress,
	validateAddress,
	validateEmail,
	setAddressType,
	validateAddressField,
	setAddressField,
	setReceiptOptOut,
	setIncentives,
	setDate,
	setMembershipType,
	validateCountry,
} from '@/store/membership_address/actionTypes';
import {
	MembershipAddressState,
	InputField,
	CountryValidationFields,
	InitialMembershipAddressValues,
} from '@/view_models/Address';
import { ValidationResponse } from '@/store/ValidationResponse';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import {
	BEGIN_ADDRESS_VALIDATION,
	BEGIN_EMAIL_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	FINISH_EMAIL_VALIDATION,
	MARK_EMPTY_FIELDS_INVALID,
	SET_ADDRESS_FIELD,
	SET_ADDRESS_FIELD_VALIDITY,
	SET_ADDRESS_TYPE,
	SET_DATE,
	SET_MEMBERSHIP_TYPE,
	SET_MEMBERSHIP_TYPE_VALIDITY,
	SET_RECEIPT_OPTOUT,
	SET_INCENTIVES,
	VALIDATE_INPUT,
} from '@/store/membership_address/mutationTypes';
import { Validity } from '@/view_models/Validity';
import { FieldInitialization } from '@/view_models/FieldInitialization';

export const actions = {
	[ initializeAddress ]( context: ActionContext<MembershipAddressState, any>, initialData: InitialMembershipAddressValues ) {

		if ( initialData.addressType ) {
			context.commit( SET_ADDRESS_TYPE, initialData.addressType );
		}

		if ( initialData.membershipType ) {
			context.commit( SET_MEMBERSHIP_TYPE, initialData.membershipType );
			context.commit( SET_MEMBERSHIP_TYPE_VALIDITY, Validity.VALID );
		}

		if ( initialData.date ) {
			context.commit( SET_DATE, initialData.date );
		}

		context.commit( SET_RECEIPT_OPTOUT, initialData.receiptOptOut );
		context.commit( SET_INCENTIVES, initialData.incentives );

		initialData.fields.forEach( ( field: FieldInitialization ) => {
			context.commit( SET_ADDRESS_FIELD, { name: field.name, value: field.value } );
			context.commit( SET_ADDRESS_FIELD_VALIDITY, { name: field.name, validity: field.validity } );
		} );
	},
	[ validateAddressField ]( context: ActionContext<MembershipAddressState, any>, field: InputField ) {
		context.commit( VALIDATE_INPUT, field );
	},
	[ setAddressField ]( context: ActionContext<MembershipAddressState, any>, field: InputField ) {
		field.value = field.value.trim();
		context.commit( SET_ADDRESS_FIELD, field );
		context.commit( VALIDATE_INPUT, field );
	},
	[ validateCountry ]( context: ActionContext<MembershipAddressState, any>, countryValidation: CountryValidationFields ) {
		context.commit( SET_ADDRESS_FIELD, countryValidation.country );
		context.commit( VALIDATE_INPUT, countryValidation.country );
		context.commit( SET_ADDRESS_FIELD, countryValidation.postcode );
		context.commit( VALIDATE_INPUT, countryValidation.postcode );
	},
	[ validateAddress ]( context: ActionContext<MembershipAddressState, any>, validateAddressUrl: string ) {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
		if ( !context.getters.requiredFieldsAreValid ) {
			return Promise.resolve( { status: 'ERR', messages: [] } );
		}

		context.commit( BEGIN_ADDRESS_VALIDATION );
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
			context.commit( FINISH_ADDRESS_VALIDATION, validationResult.data );
			return validationResult.data;
		} );

	},
	[ validateEmail ]( context: ActionContext<MembershipAddressState, any>, validateEmailUrl: string ) {
		context.commit( MARK_EMPTY_FIELDS_INVALID );
		if ( !context.getters.requiredFieldsAreValid ) {
			return Promise.resolve( { status: 'ERR', messages: [] } );
		}

		context.commit( BEGIN_EMAIL_VALIDATION );
		const bodyFormData = new FormData();
		bodyFormData.append( 'email', context.state.values.email );

		return axios( validateEmailUrl, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse<ValidationResponse> ) => {
			context.commit( FINISH_EMAIL_VALIDATION, validationResult.data );
			return validationResult.data;
		} );

	},
	[ setAddressType ]( context: ActionContext<MembershipAddressState, any>, type: AddressTypeModel ) {
		context.commit( SET_ADDRESS_TYPE, type );
		if ( type === AddressTypeModel.COMPANY && context.getters.membershipType === MembershipTypeModel.ACTIVE ) {
			context.commit( SET_MEMBERSHIP_TYPE_VALIDITY, Validity.INVALID );
		}
	},
	[ setDate ]( context: ActionContext<MembershipAddressState, any>, date: string ) {
		context.commit( 'SET_DATE', date );
	},
	[ setReceiptOptOut ]( context: ActionContext<MembershipAddressState, any>, optOut: boolean ) {
		context.commit( SET_RECEIPT_OPTOUT, optOut );
	},
	[ setIncentives ]( context: ActionContext<MembershipAddressState, any>, incentives: string[] ) {
		context.commit( SET_INCENTIVES, incentives );
	},
	[ setMembershipType ]( context: ActionContext<MembershipAddressState, any>, type: MembershipTypeModel ) {
		context.commit( SET_MEMBERSHIP_TYPE, type );
		context.commit( SET_MEMBERSHIP_TYPE_VALIDITY, Validity.VALID );
	},

};
