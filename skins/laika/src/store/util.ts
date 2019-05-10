import { Validity } from '@/view_models/Validity';
import { PostData, FormData } from '@/view_models/Address';
/**
 * @param namespacesAndName namespace1, namespace2, ..., mutationOrActionName
 */
function buildActionOrMutationName( ...namespacesAndName: string[] ): string {
	return namespacesAndName.join( '/' );
}

export const action = buildActionOrMutationName;
export const mutation = buildActionOrMutationName;

export const Helper = {
	inputIsValid: ( value: string, pattern: string ) => {
		if ( pattern === null ) {
			return value !== '' ? Validity.VALID : Validity.INVALID;
		}
		return new RegExp( pattern ).test( value ) ? Validity.VALID : Validity.INVALID;
	},
	formatPostData: ( form: FormData ): any => {
		return Object.keys( form ).reduce( ( accumulator: PostData, currentValue: string ) => {
			accumulator[ currentValue ] = form[ currentValue ].value;
			return accumulator;
		}, {} );
	},
};
