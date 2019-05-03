import { Validity } from './Validity';

export interface Payment {
    amount: Validity
}

export interface AmountData {
    amountValue: String
    amountCustomValue: String
}
