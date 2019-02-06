import { Validity } from '../lib/validation_states';
import { PostData, FormData } from '../types';

export const Helper = {
    inputIsValid: (value: string, pattern: string ) => {
        if (pattern === null) {
            return value !== '' ? Validity.VALID : Validity.INVALID;
        }
        return new RegExp(pattern).test(value) ? Validity.VALID : Validity.INVALID;
    },
    formatPostData: (form: FormData) => {
        return Object.keys(form).reduce((accumulator: PostData, currentValue: string) => {
            accumulator[currentValue] = form[currentValue].value;
            return accumulator;
        }, {});
    }
}