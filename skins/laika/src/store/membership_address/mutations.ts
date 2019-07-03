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
	SET_EMAIL,
	SET_DATE,
	SET_NEWSLETTER_OPTIN,
	SET_RECEIPT_OPTOUT,
} from '@/store/membership_address/mutationTypes';
import { MembershipAddressState, InputField } from '@/view_models/Address';
import { REQUIRED_FIELDS } from '@/store/membership_address/constants';

export const mutations: MutationTree<MembershipAddressState> = {
	[ VALIDATE_INPUT ]( state: MembershipAddressState, field: InputField ) {
		if ( field.value === '' && field.optionalField ) {
			state.validity[ field.name ] = Validity.INCOMPLETE;
		} else {
			state.validity[ field.name ] = Helper.inputIsValid( field.value, field.pattern );
		}
	},
	[ MARK_EMPTY_FIELDS_INVALID ]( state: MembershipAddressState ) {
		REQUIRED_FIELDS[ state.addressType ].forEach( ( fieldName: string ) => {
			if ( state.validity[ fieldName ] === Validity.INCOMPLETE ) {
				state.validity[ fieldName ] = Validity.INVALID;
			}
		} );
	},
	[ BEGIN_ADDRESS_VALIDATION ]( state: MembershipAddressState ) {
		state.isValidating = true;
	},
	[ FINISH_ADDRESS_VALIDATION ]( state: MembershipAddressState, payload ) {
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
	[ SET_ADDRESS_TYPE ]( state: MembershipAddressState, type ) {
		state.addressType = type;
	},
	[ SET_ADDRESS_FIELDS ]( state: MembershipAddressState, fields ) {
		Object.keys( fields ).forEach( ( field: string ) => {
			const fieldName = fields[ field ];
			if ( state.validity[ fieldName.name ] !== Validity.INVALID ) {
				state.values[ fieldName.name ] = fieldName.value;
			}
		} );
	},
	[ SET_ADDRESS_FIELD ]( state: MembershipAddressState, field: InputField ) {
		state.values[ field.name ] = field.value;
	},
	[ SET_EMAIL ]( state: MembershipAddressState, email ) {
		state.values.email = email;
		state.validity.email = Validity.VALID;
	},
	[ SET_DATE ]( state: MembershipAddressState, date ) {
		state.values.date = date;
		state.validity.date = Validity.VALID;
	},
	[ SET_NEWSLETTER_OPTIN ]( state: MembershipAddressState, optIn ) {
		state.newsletterOptIn = optIn;
	},
	[ SET_RECEIPT_OPTOUT ]( state: MembershipAddressState, optOut ) {
		state.receiptOptOut = optOut;
	},
};
