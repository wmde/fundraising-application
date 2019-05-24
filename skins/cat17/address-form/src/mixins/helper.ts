import {PostData, FormData, Validity, InputField} from '../types';

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
    },
    allFieldsAreEmpty: ( formFields: FormData, ignoreFields: string[] ): boolean => {
        const isEmpty = ( field: InputField ) => field.value !== '' && ignoreFields.indexOf( field.name ) == -1;
        return Object.values( formFields ).find( isEmpty ) === undefined;
    }
}