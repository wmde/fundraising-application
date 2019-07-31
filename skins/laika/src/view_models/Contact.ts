import { Validity } from '@/view_models/Validity';

export interface AddressValidity {
    [key: string]: boolean
}

export interface InputField {
    name: string,
    value: string | null,
    pattern: string,
    optionalField: boolean,
    validity: Validity,
}

export interface FormData {
    [key: string]: InputField
}
