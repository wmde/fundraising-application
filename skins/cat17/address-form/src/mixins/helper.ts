import { Validity } from '@/types';

export const Helper = {
    inputIsValid: (value: string, pattern: string ): Validity => {
        if (pattern === null) {
            return value !== '' ? Validity.VALID : Validity.INVALID;
        }
        return new RegExp(pattern).test(value) ? Validity.VALID : Validity.INVALID;
    }
}