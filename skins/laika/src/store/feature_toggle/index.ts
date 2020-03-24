import { Store, MutationPayload } from 'vuex';
import {
	SET_ADDRESS_TYPE,
	SET_VALIDITY,
	INITIALIZE_ADDRESS,
} from '@/store/address/mutationTypes';
import { NS_ADDRESS } from '@/store/namespaces';
import { Validity } from '@/view_models/Validity';
import { mutation as mutationName } from '@/store/util';

interface PluginList<T> {
    [key: string]: T
}

export const FeatureToggleVuexPlugins: PluginList<( s: Store<any> ) => void> = {
	'campaigns.address_type.no_preselection': function ( store: Store<any> ) {
		store.subscribe( ( mutation: MutationPayload, s: any ) => {
			switch ( mutation.type ) {
				case mutationName( NS_ADDRESS, SET_ADDRESS_TYPE ):
					store.commit( mutationName( NS_ADDRESS, SET_VALIDITY ), { name: 'addressType', value: Validity.VALID } );
					break;
				case mutationName( NS_ADDRESS, INITIALIZE_ADDRESS ):
					store.commit( mutationName( NS_ADDRESS, SET_VALIDITY ), { name: 'addressType', value: Validity.INCOMPLETE } );
					break;

			}
		} );
	},
};
