import { FeatureToggleVuexPlugins } from '@/store/feature_toggle/index';
import { Store } from 'vuex';

export function createFeatureTogglePlugin( selectedBuckets: string[] ) {
	const applicablePlugins = selectedBuckets.reduce( ( plugins: Array<( store: Store<any> ) => void>, bucket: string ) => {
		if ( FeatureToggleVuexPlugins[ bucket ] ) {
			plugins.push( FeatureToggleVuexPlugins[ bucket ] );
		}
		return plugins;
	}, [] );
	if ( applicablePlugins ) {
		return function ( store: Store<any> ) {
			applicablePlugins.forEach( plugin => plugin( store ) );
		};
	}
	return function ( store: Store<any> ) {};
}
