export const enum Validity {
    INVALID,
    VALID,
    INCOMPLETE
}

export const ValidationStates = {
    OK: 'OK',
    ERR: 'ERR',
    INCOMPLETE: 'INCOMPLETE'
};

export interface FormValidity {
	dataEntered: boolean,
	isValid: Validity
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
	inputIsValid(value: string, pattern: string): Validity
}

export interface inputField {
	name: string,
	value: string,
	pattern: string,
	optionalField: boolean
}

export interface Payload {
	validateAddressURL: String,
	formData: Array<inputField>
}


