import { Validity } from './Validity';

export interface Payment {
    validity: {
        amount: Validity
    },
    values: {
        amount: string
    }

}

export interface AmountData {
    amountValue: string
    amountCustomValue: string
}
