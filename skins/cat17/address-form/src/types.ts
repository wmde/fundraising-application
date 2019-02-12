export enum Validity  {
	INVALID,
	VALID,
	INCOMPLETE
}

export interface Form {
	[key: string]: Validity
}

export interface AddressState {
	isValidating: boolean
	form: Form,
}

/**
 * Validation result JSON object from the server
 */
export interface ValidationResult {
	status: string,
	messages: object
}

export interface Helper {
    inputIsValid(value: string, pattern: string): boolean,
    formatPostData(form: FormData): Object
}

export interface InputField {
	name: string,
	value: string,
	pattern: string,
	optionalField: boolean
}

export interface FormData {
    [key: string]: InputField
}

export interface Transport {
    getData: Function
    postData: Function
}

export interface Payload {
    transport: Transport,
	validateAddressURL: String,
	formData: FormData
}

export interface PostData {
    [key: string]: string
}

/**
 * Form can be mapped to simple boolean validity (instead of the tri-state isValid in FieldValidity).
 */
export interface AddressValidity {
	[key: string]: boolean
}

