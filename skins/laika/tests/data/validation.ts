import { AddressValidation, ContactFormValidation } from '@/view_models/Validation';

export const addressValidationPatterns: AddressValidation = {
	salutation: '^(Herr|Frau)$',
	title: '',
	companyName: '^.+$',
	firstName: "^[A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\\s\\-\\.\\']+$",
	lastName: "^[A-Za-z\u00C0-\u00D6\u00D8-\u00f6\u00f8-\u00ff\\s\\-\\.\\']+$",
	street: '^.+$',
	city: '^.+$',
	postcode: '^.+$',
	country: '^.+$',
	email: '^(.+)@(.+)\\.(.+)$',
};

export const contactFormValidationPatterns: ContactFormValidation = {
	firstname: '^.+$',
	lastname: '^.+$',
	donationNumber: '^[0-9]*$',
	email: '^(.+)@(.+)\\.(.+)$',
	topic: '^.+$',
	subject: '^.+$',
	comment: '(\n|.)+',
};

export const dateOfBirthValidationPattern: string = [
	'^(?:(?:31(\\.)',
	'(?:0?[13578]|1[02]))\\1|(?:(?:29|30)(\\.)',
	'(?:0?[13-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29(\\.)',
	'0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$',
	'|^(?:0?[1-9]|1\\d|2[0-8])(\\.)(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$',
].join( '' );
