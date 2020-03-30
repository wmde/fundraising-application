import { GetterTree } from 'vuex';
import { AddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const getters: GetterTree<AddressState, any> = {
	invalidFields: ( state: AddressState ): Array<string> => {
		return state.requiredFields[ state.addressType ].filter( fieldName => state.validity[ fieldName ] !== Validity.VALID );
	},
	requiredFieldsAreValid: ( state: AddressState, getters: GetterTree<AddressState, any> ): boolean => {
		return getters.invalidFields.length === 0;
	},
	addressType: ( state: AddressState ): AddressTypeModel => state.addressType,
	addressTypeIsNotAnon: ( state: AddressState, getters ): boolean => getters.addressType !== AddressTypeModel.ANON,
	addressTypeIsInvalid: ( state: AddressState ): boolean => state.validity.addressType === Validity.INVALID,
	fullName: ( state: AddressState ): string => {
		// Duplicating code from DonorName PHP class
		const address = state.values;
		const nonEmpty = ( v: string ): boolean => !!v;
		const companyName = state.addressType === AddressTypeModel.COMPANY ? address.companyName : '';
		// remove ternary operator in the following line when we implement contact person, https://phabricator.wikimedia.org/T220366
		const privateName = state.addressType === AddressTypeModel.PERSON ? [ address.title, address.firstName, address.lastName ].filter( nonEmpty ).join( ' ' ) : '';
		return [ companyName, privateName ].filter( nonEmpty ).join( ', ' );
	},
	isValidating: ( state: AddressState ): boolean => {
		return state.serverSideValidationCount > 0;
	},
	allRequiredFieldsEmpty: ( state: AddressState ): boolean => {
		return state.requiredFields[ state.addressType ].map( field => state.values[ field ] === '' ).every( x => x );
	},
};
