import { MutationPayload, Store } from 'vuex';
import { mutation as mutationName } from '@/store/util';
import { NS_ADDRESS, NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { trackFormValidationErrors } from '@/tracking';
import { VALIDATE_INPUT } from '@/store/address/mutationTypes';
import { Validity } from '@/view_models/Validity';

export const trackFormErrorPlugin = ( store: Store<any>, formName: string ) => {

	const formNameToStoreNameLookUp: { [key: string]: string} = {
		'donation_form': 'address',
		'update_address': 'address',
		'membership_application': 'membership_address',
	};
	const storeName = formNameToStoreNameLookUp[ formName ];

	let prevState = { ... store.state[ storeName ].validity };

	store.subscribe( ( mutation: MutationPayload, mutatedState: any ) => {

		if ( mutation.type === ( storeName + '/VALIDATE_INPUT' ) ) {
			const currentFormFieldName = mutation.payload.name;

			if ( prevState[ currentFormFieldName ] === Validity.INCOMPLETE
				|| prevState[ currentFormFieldName ] === Validity.VALID ) {

				if ( mutatedState[ storeName ].validity[ currentFormFieldName ] === Validity.INVALID ) {
					trackFormValidationErrors( formName, currentFormFieldName );
				}
			}
			prevState = { ...mutatedState[ storeName ].validity };
		}
	} );
};

export function createTrackFormErrorsPlugin( formName: string ) {
	return ( store: Store<any> ) => {
		return trackFormErrorPlugin( store, formName );
	};
}
