import { MutationTree } from 'vuex';
import { Helper } from '@/store/util';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELDS_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	BEGIN_EMAIL_VALIDATION,
	FINISH_EMAIL_VALIDATION,
	SET_ADDRESS_TYPE,
	SET_ADDRESS_FIELD,
	SET_ADDRESS_FIELDS,
	SET_ADDRESS_FIELD_VALIDITY,
	SET_DATE,
	SET_RECEIPT_OPTOUT,
	SET_INCENTIVES,
	SET_MEMBERSHIP_TYPE,
	SET_MEMBERSHIP_TYPE_VALIDITY,
} from '@/store/membership_address/mutationTypes';
import { REQUIRED_FIELDS } from '@/store/membership_address/constants';
import { Validity } from '@/view_models/Validity';
import { MembershipAddressState, InputField, AddressState } from '@/view_models/Address';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';

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
		state.serverSideValidationCount++;
	},
	[ FINISH_ADDRESS_VALIDATION ]( state: MembershipAddressState, payload ) {
		state.serverSideValidationCount--;
		if ( payload.status === 'OK' ) {
			return;
		}
		REQUIRED_FIELDS[ state.addressType ].forEach( name => {
			if ( payload.messages[ name ] ) {
				state.validity[ name ] = Validity.INVALID;
			}
		} );
	},
	[ BEGIN_EMAIL_VALIDATION ]( state: MembershipAddressState ) {
		state.serverSideValidationCount++;
	},
	[ FINISH_EMAIL_VALIDATION ]( state: MembershipAddressState, payload ) {
		state.serverSideValidationCount--;
		if ( payload.status === 'OK' ) {
			return;
		}
		if ( REQUIRED_FIELDS[ state.addressType ].indexOf( 'email' ) > 0 ) {
			state.validity.email = Validity.INVALID;
		}
	},
	[ SET_ADDRESS_TYPE ]( state: MembershipAddressState, type: AddressTypeModel ) {
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
	[ SET_ADDRESS_FIELD_VALIDITY ]( state: MembershipAddressState, payload: { name: string, validity: Validity } ) {
		state.validity[ payload.name ] = payload.validity;
	},
	[ SET_DATE ]( state: MembershipAddressState, date ) {
		state.values.date = date;
		state.validity.date = Validity.VALID;
	},
	[ SET_RECEIPT_OPTOUT ]( state: MembershipAddressState, optOut ) {
		state.receiptOptOut = optOut;
	},
	[ SET_INCENTIVES ]( state: MembershipAddressState, incentives ) {
		state.incentives = incentives;
	},
	[ SET_MEMBERSHIP_TYPE ]( state: MembershipAddressState, type: MembershipTypeModel ) {
		state.membershipType = type;
	},
	[ SET_MEMBERSHIP_TYPE_VALIDITY ]( state: MembershipAddressState, validity: Validity ) {
		state.validity.membershipType = validity;
	},
};
