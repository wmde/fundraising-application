import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		strict: process.env.NODE_ENV !== 'production',
	};

	return new Vuex.Store<any>( storeBundle );
}
