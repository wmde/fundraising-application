import { Validity } from './Validity';

export interface Payment {
    validity: {
        [key: string]: Validity
    },
    values: {
        [key: string]: string
    }
}

export interface AmountData {
    amountValue: string
    amountCustomValue: string
}

export interface IntervalData {
    selectedInterval: Number
}

export interface TypeData {
    selectedType: string
}
