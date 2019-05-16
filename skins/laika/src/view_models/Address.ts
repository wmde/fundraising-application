import { Validity } from './Validity';
import { AddressTypeModel } from './AddressTypeModel';

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

export interface FormValidity {
    [key: string]: Validity
}

export interface FormValues {
    [key: string]: string,
}

export interface AddressState {
    isValidating: boolean,
    addressType: AddressTypeModel,
    newsletterOptIn: boolean,
    values: FormValues,
    validity: FormValidity,
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
