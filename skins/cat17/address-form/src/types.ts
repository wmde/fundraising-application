export interface FormValidity {
    dataEntered: boolean,
    value: string,
    isValid: boolean
}

export interface Form {
    salutation: FormValidity,
    title: FormValidity,
    firstName: FormValidity,
    lastName: FormValidity,
    companyName: FormValidity,
    street: FormValidity,
    postcode: FormValidity,
    city: FormValidity,
    country: FormValidity
}

export interface AddressState {
    isValidating: boolean
    form: Form,
}

export interface FormData {
    name: string,
    value: string,
    pattern: string,
    optionalField: boolean
}

export interface Payload {
    formData: Array<FormData>,
    validateAddressURL: string
}

export interface ValidationResult {
    status: string,
    messages: {}
}

export interface Helper {
    inputIsValid(value: string, pattern: string): boolean;
}