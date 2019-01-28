export interface FormValidity {
	dataEntered: boolean,
	isValid: boolean | null
}

export interface Form {
	[key: string]: FormValidity
}

export interface AddressState {
	isValidating: boolean
	form: Form,
}

export interface ValidationResult {
	status: string,
	messages: object
}

export interface Helper {
	inputIsValid(value: string, pattern: string): boolean
}

export interface inputField {
	name: string,
	value: string,
	pattern: string,
	optionalField: boolean
}

export interface Payload {
	validateAddressURL: string,
	formData: Array<inputField>
}


