import { MutationTree } from 'vuex';
import { Validity } from '@/view_models/Validity';
import { Helper } from '@/store/util';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELD_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	SET_ADDRESS_TYPE,
} from '@/store/address/mutationTypes';
import { AddressState, InputField } from '@/view_models/Address';
const ADDRESS_FIELD_VALIDATOR_NAMES: Array<string> = [
	'addressType',
	'companyName',
	'street', 'postcode', 'city', 'country',
];

export const mutations: MutationTree<AddressState> = {
	[ VALIDATE_INPUT ]( state, field: InputField ) {
		if ( field.value === '' && field.optionalField ) {
			state.form[ field.name ] = Validity.INCOMPLETE;
		} else {
			state.form[ field.name ] = Helper.inputIsValid( field.value, field.pattern );
		}
	},
	[ MARK_EMPTY_FIELD_INVALID ]( state, payload ) {
		Object.keys( payload ).forEach( ( field: string ) => {
			const fieldName = payload[ field ];
			if ( !fieldName.optionalField && state.form[ fieldName.name ] === Validity.INCOMPLETE ) {
				state.form[ fieldName.name ] = Validity.INVALID;
			}
		} );
	},
	[ BEGIN_ADDRESS_VALIDATION ]( state ) {
		state.isValidating = true;
	},
	[ FINISH_ADDRESS_VALIDATION ]( state, payload ) {
		if ( payload.status === 'ERR' ) {
			ADDRESS_FIELD_VALIDATOR_NAMES.forEach( name => {
				if ( payload.messages[ name ] ) {
					state.form[ name ] = Validity.INVALID;
				}
			} );
		}
		state.isValidating = false;
	},
	[ SET_ADDRESS_TYPE ]( state, type ) {
		state.addressType = type;
	},
};
