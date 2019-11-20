import { ValidationResult } from "@/view_models/Address";

export function mergeValidationResults( results: ValidationResult[] ) {
	return results.reduce(
		( result: ValidationResult, currentResult: ValidationResult ) => {
			if ( currentResult.status !== 'OK' ) {
				result.status = 'ERR';
				result.messages = Object.assign( result.messages, currentResult.messages );
			}
			return result;
		},
		{ status: 'OK', messages: {} },
	);
}