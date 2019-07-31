import { Validity } from '@/view_models/Validity';
import { FormData, PostData } from '@/view_models/Address';

/**
 * @param namespacesAndName namespace1, namespace2, ..., mutationOrActionName
 */
function buildActionOrMutationName( ...namespacesAndName: string[] ): string {
	return namespacesAndName.join( '/' );
}

export const action = buildActionOrMutationName;
export const mutation = buildActionOrMutationName;

export const Helper = {
	inputIsValid: ( value: string, pattern: string, isOptional?: boolean ) => {
		if ( isOptional && Helper.isEmpty( value ) ) {
			return Validity.VALID;
		}
		if ( pattern === null ) {
			return value !== '' ? Validity.VALID : Validity.INVALID;
		}
		if ( Helper.isEmpty( value ) ) {
			return Validity.INVALID;
		}
		return new RegExp( pattern ).test( value ) ? Validity.VALID : Validity.INVALID;
	},
	isEmpty( value: string ) {
		return value === undefined || value === null || value === '';
	},
	formatPostData: ( form: FormData ): any => {
		return Object.keys( form ).reduce( ( accumulator: PostData, currentValue: string ) => {
			accumulator[ currentValue ] = form[ currentValue ].value;
			return accumulator;
		}, {} );
	},
	isNonNumeric( value: string ): boolean {
		return value === '' || isNaN( Number( value ) );
	},

};
