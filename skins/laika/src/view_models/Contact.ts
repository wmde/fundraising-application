import { Validity } from '@/view_models/Validity';

export interface InputField {
    name: string,
    value: string,
    pattern: string,
    optionalField: boolean,
    validity: Validity,
}

export interface FormData {
    [key: string]: InputField
}
