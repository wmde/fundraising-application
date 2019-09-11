import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const REQUIRED_FIELDS: {[key: number]: string[]} = {
	[ AddressTypeModel.PERSON ]: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
	[ AddressTypeModel.COMPANY ]: [ 'companyName', 'street', 'postcode', 'city', 'email' ],
	[ AddressTypeModel.ANON ]: [],
};

export const REQUIRED_FIELDS_ADDRESS_UPDATE: {[key: number]: string[]} = {
	[ AddressTypeModel.PERSON ]: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city' ],
	[ AddressTypeModel.COMPANY ]: [ 'companyName', 'street', 'postcode', 'city' ],
};
