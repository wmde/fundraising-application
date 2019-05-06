import { Validity } from './Validity';

export interface Payment {
    validity: {
        amount: Validity
        option: Validity
    },
    values: {
        amount: string
        interval: string
        option: string
    }
}

export interface AmountData {
    amountValue: string
    amountCustomValue: string
}

export interface IntervalData {
    selectedInterval: Number
}

export interface OptionData {
    selectedOption: string
}
