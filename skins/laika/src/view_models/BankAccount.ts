import { Validity } from './Validity';

export interface BankAccount {
    validity: {
        [key: string]: Validity
    },
    values: {
        [key: string]: string
    }
}

export interface BankAccountData {
    accountId: string
    bankId: string
}

export interface BankAccountRequest {
    validationUrl: string
    requestParams: object
}

export interface BankAccountResponse {
    [key: string]: string
}
