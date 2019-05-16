import { MutationTree } from 'vuex';
import { Validity } from '@/view_models/Validity';
import { Helper } from '@/store/util';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELD_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	SET_ADDRESS_TYPE,
	SET_ADDRESS_FIELDS,
	SET_EMAIL,
	SET_NEWSLETTER_OPTIN,
} from '@/store/address/mutationTypes';
import { AddressState, InputField } from '@/view_models/Address';
const ADDRESS_FIELD_VALIDATOR_NAMES: Array<string> = [
	'addressType',
	'companyName',
	'street', 'postcode', 'city', 'country',
];

export const mutations: MutationTree<AddressState> = {
	[ VALIDATE_INPUT ]( state: AddressState, field: InputField ) {
		if ( field.value === '' && field.optionalField ) {
			state.validity[ field.name ] = Validity.INCOMPLETE;
		} else {
			state.validity[ field.name ] = Helper.inputIsValid( field.value, field.pattern );
		}
	},
	[ MARK_EMPTY_FIELD_INVALID ]( state: AddressState, payload ) {
		Object.keys( payload ).forEach( ( field: string ) => {
			const fieldName = payload[ field ];
			if ( !fieldName.optionalField && state.validity[ fieldName.name ] === Validity.INCOMPLETE ) {
				state.validity[ fieldName.name ] = Validity.INVALID;
			}
		} );
	},
	[ BEGIN_ADDRESS_VALIDATION ]( state: AddressState ) {
		state.isValidating = true;
	},
	[ FINISH_ADDRESS_VALIDATION ]( state: AddressState, payload ) {
		if ( payload.status === 'ERR' ) {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach( name => {
				if ( payload.messages[ name ] ) {
					state.validity[ name ] = Validity.INVALID;
				}
			} );
		}
		state.isValidating = false;
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
	[ SET_EMAIL ]( state: AddressState, email ) {
		state.values.email = email;
	},
	[ SET_NEWSLETTER_OPTIN ]( state: AddressState, optIn ) {
		state.newsletterOptIn = optIn;
	},
};
