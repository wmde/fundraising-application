import { MutationTree } from 'vuex';
import { Validity } from '@/view_models/Validity';
import { Helper } from '@/store/util';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELDS_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	SET_ADDRESS_TYPE,
	SET_ADDRESS_FIELD,
	SET_ADDRESS_FIELDS,
	SET_NEWSLETTER_OPTIN,
	SET_RECEIPT_OPTOUT,
} from '@/store/address/mutationTypes';
import { AddressState, InputField } from '@/view_models/Address';
import { REQUIRED_FIELDS } from '@/store/address/constants';

export const mutations: MutationTree<AddressState> = {
	[ VALIDATE_INPUT ]( state: AddressState, field: InputField ) {
		if ( field.value === '' && field.optionalField ) {
			state.validity[ field.name ] = Validity.VALID;
		} else {
			state.validity[ field.name ] = Helper.inputIsValid( field.value, field.pattern );
		}
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: AddressState ) {
		REQUIRED_FIELDS[ state.addressType ].forEach( ( fieldName: string ) => {
			if ( state.validity[ fieldName ] === Validity.INCOMPLETE ) {
				state.validity[ fieldName ] = Validity.INVALID;
			}
		} );
	},
	[ BEGIN_ADDRESS_VALIDATION ]( state: AddressState ) {
		state.isValidating = true;
	},
	[ FINISH_ADDRESS_VALIDATION ]( state: AddressState, payload ) {
		state.isValidating = false;
		if ( payload.status === 'OK' ) {
			return;
		}
		REQUIRED_FIELDS[ state.addressType ].forEach( name => {
			if ( payload.messages[ name ] ) {
				state.validity[ name ] = Validity.INVALID;
			}
		} );
	},
	[ SET_ADDRESS_TYPE ]( state: AddressState, type ) {
		state.addressType = type;
	},
	[ SET_ADDRESS_FIELDS ]( state: AddressState, fields ) {
		Object.keys( fields ).forEach( ( field: string ) => {
			const fieldName = fields[ field ];
			if ( state.validity[ fieldName.name ] !== Validity.INVALID ) {
				state.values[ fieldName.name ] = fieldName.value;
			}
		} );
	},
	[ SET_ADDRESS_FIELD ]( state: AddressState, field: InputField ) {
		state.values[ field.name ] = field.value;
	},
	[ SET_NEWSLETTER_OPTIN ]( state: AddressState, optIn ) {
		state.newsletterOptIn = optIn;
	},
	[ SET_RECEIPT_OPTOUT ]( state: AddressState, optOut ) {
		state.receiptOptOut = optOut;
	},
};
