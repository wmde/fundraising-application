import { Validity } from '../lib/validation_states';

export const Helper = {
    inputIsValid: (value: string, pattern: string ) => {
        if (pattern === null) {
            return value !== '' ? Validity.VALID : Validity.INVALID;
        }
        return new RegExp(pattern).test(value) ? Validity.VALID : Validity.INVALID;
    }
}