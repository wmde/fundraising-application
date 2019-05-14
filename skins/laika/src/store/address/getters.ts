import { GetterTree } from 'vuex';
import { AddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const getters: GetterTree<AddressState, any> = {
	invalidFields: ( state: AddressState ): Array<string> => {
		return Object.keys( state.validity ).filter( field => state.validity[ field ] === Validity.INVALID );
	},
	allFieldsAreValid: ( state: AddressState, getters: GetterTree<AddressState, any> ): boolean => {
		return getters.invalidFields.length === 0;
	},
	addressType: ( state: AddressState ): AddressTypeModel => state.addressType,
};
