import { AddressTypeModel } from '@/view_models/AddressTypeModel';

/** AddressTypeModel: String array of required fields **/
export interface AddressRequirements {
	[ key: number ]: string[]
}

export const REQUIRED_FIELDS: AddressRequirements = {
	[ AddressTypeModel.PERSON ]: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'country', 'email', 'addressType' ],
	[ AddressTypeModel.COMPANY ]: [ 'companyName', 'street', 'postcode', 'city', 'country', 'email', 'addressType' ],
	[ AddressTypeModel.ANON ]: [],
	[ AddressTypeModel.UNSET ]: [ 'addressType' ],
};

export const REQUIRED_FIELDS_ADDRESS_UPDATE: AddressRequirements = {
	[ AddressTypeModel.PERSON ]: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'country' ],
	[ AddressTypeModel.COMPANY ]: [ 'companyName', 'street', 'postcode', 'city', 'country' ],
};
