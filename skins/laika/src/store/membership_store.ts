import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createAddress from '@/store/membership_address';
import {
	NS_MEMBERSHIP_ADDRESS,
} from './namespaces';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[ NS_MEMBERSHIP_ADDRESS ]: createAddress(),
		},
		strict: process.env.NODE_ENV !== 'production',
	};

	return new Vuex.Store<any>( storeBundle );
}
