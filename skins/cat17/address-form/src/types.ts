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

export interface AddressMutations {
    VALIDATE_INPUT: { field: inputField },
    MARK_EMPTY_FIELD_INVALID: { payload: Array<FormData>},
    BEGIN_ADDRESS_VALIDATION: { payload: Array<FormData> },
    FINISH_ADDRESS_VALIDATION: { payload: ValidationResult }
}

export interface AddressGetters {
    validity: boolean,
    allFieldsAreValid: boolean
}

export interface AddressActions {
    validateInput: { field: inputField },
    storeAddressFields: { payload: Payload }
}

export interface FormData {
    name: string,
    pattern: string,
    optionalField: boolean
}

export interface inputField {
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
    messages: object
}

export interface Helper {
    inputIsValid(value: string, pattern: string): boolean
}
