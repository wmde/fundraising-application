import { Store } from 'vuex';

// These constants are mostly for testing
export enum ValidationState {
	WAS_VALIDATING,
	IMMEDIATE
}

export function waitForServerValidationToFinish( store: Store<any> ): Promise<ValidationState> {
	if ( !store.getters.isValidating ) {
		return Promise.resolve( ValidationState.IMMEDIATE );
	}
	return new Promise( ( resolve ) => {
		const unwatch = store.watch(
			( state, getters ) => getters.isValidating,
			( isValidating ) => {
				if ( !isValidating ) {
					unwatch();
					resolve( ValidationState.WAS_VALIDATING );
				}
			}
		);
	} );
}
