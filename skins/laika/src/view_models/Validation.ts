export interface AddressValidation {
	salutation: string,
	title: string,
	companyName: string,
	firstName: string,
	lastName: string,
	street: string,
	city: string,
	postcode: string,
	country: string,
	email: string,
}

export interface ContactFormValidation {
	firstname: string,
	lastname: string,
	donationNumber: string,
	email: string,
	topic: string,
	subject: string,
	comment: string,
}
