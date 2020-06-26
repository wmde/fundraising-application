import { Validity } from './Validity';
import { AddressTypeModel } from './AddressTypeModel';
import { MembershipTypeModel } from './MembershipTypeModel';
import { AddressRequirements } from '@/store/address/constants';
import { FieldInitialization } from '@/view_models/FieldInitialization';

export interface AddressValidity {
    [key: string]: boolean
}

export interface InputField {
    name: string,
    value: string,
    pattern: string,
    optionalField: boolean
}

export interface AddressFormData {
    [key: string]: InputField
}

export interface CountryValidationFields {
    country: InputField,
    postcode: InputField,
}

export interface FormValidity {
    [key: string]: Validity
}

export interface FormValues {
    [key: string]: string,
}

export interface AddressState {
    serverSideValidationCount: number,
    addressType: AddressTypeModel,
    newsletterOptIn: boolean,
    receiptOptOut: boolean,
    values: FormValues,
    validity: FormValidity,
    requiredFields: AddressRequirements,
}

export interface MembershipAddressState {
    serverSideValidationCount: number,
    addressType: AddressTypeModel,
    membershipType: MembershipTypeModel,
    receiptOptOut: boolean,
    values: FormValues,
    validity: FormValidity,
}

export interface InitialAddressValues {
    addressType: AddressTypeModel,
    newsletterOptIn: boolean,
    receiptOptOut: boolean,
    fields: FieldInitialization[],
}

export interface InitialMembershipAddressValues {
    addressType?: AddressTypeModel,
    membershipType?: MembershipTypeModel,
    date?: string,
    receiptOptOut: boolean,
    fields: FieldInitialization[],
}

export interface InitialMembershipData {
    addressType: string,
    salutation: string,
    title: string,
    firstName: string,
    lastName: string,
    companyName: string,
    street: string,
    city: string,
    postcode: string,
    country: string,
    email: string,
    iban?: string
    bic?: string,
    bankname?: string
}

export interface InputField {
    name: string,
    value: string,
    pattern: string,
    optionalField: boolean
}

export interface Payload {
    validateAddressUrl: string,
    validateEmailUrl: string,
    formData: AddressFormData
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

export interface SubmittedAddress {
    addressData: AddressFormData,
    addressType: string
}
