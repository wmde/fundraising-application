import { Validity } from './Validity';

export interface AddressValidity {
    [key: string]: boolean
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

export interface Form {
    [key: string]: Validity
}

export interface AddressState {
    isValidating: boolean
    form: Form,
}

export interface InputField {
    name: string,
    value: string,
    pattern: string,
    optionalField: boolean
}

export interface Payload {
    validateAddressUrl: string,
    formData: FormData
}

/**
 * Validation result JSON object from the server
 */
export interface ValidationResult {
    status: string,
    messages: object
}

export interface PostData {
    [key: string]: string
}
