import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export const REQUIRED_FIELDS: {[key: number]: string[]} = {
	[ AddressTypeModel.PERSON ]: [ 'salutation', 'firstName', 'lastName', 'street', 'postcode', 'city', 'email' ],
	[ AddressTypeModel.COMPANY ]: [ 'companyName', 'street', 'postcode', 'city', 'email' ],
};
