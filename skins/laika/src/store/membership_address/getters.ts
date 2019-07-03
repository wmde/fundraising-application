import { GetterTree } from 'vuex';
import { MembershipAddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { REQUIRED_FIELDS } from '@/store/address/constants';

export const getters: GetterTree<MembershipAddressState, any> = {
	invalidFields: ( state: MembershipAddressState ): Array<string> => {
		return REQUIRED_FIELDS[ state.addressType ].filter( fieldName => state.validity[ fieldName ] !== Validity.VALID );
	},
	requiredFieldsAreValid: ( state: MembershipAddressState, getters: GetterTree<MembershipAddressState, any> ): boolean => {
		return getters.invalidFields.length === 0;
	},
	addressType: ( state: MembershipAddressState ): AddressTypeModel => state.addressType,
	fullName: ( state: MembershipAddressState ): string => {
		// Duplicating code from DonorName PHP class
		const address = state.values;
		const nonEmpty = ( v: string ): boolean => !!v;
		const companyName = state.addressType === AddressTypeModel.COMPANY ? address.companyName : '';
		// remove ternary operator in the following line when we implement contact person, https://phabricator.wikimedia.org/T220366
		const privateName = state.addressType === AddressTypeModel.PERSON ? [ address.title, address.firstName, address.lastName ].filter( nonEmpty ).join( ' ' ) : '';
		return [ companyName, privateName ].filter( nonEmpty ).join( ', ' );
	},
};
