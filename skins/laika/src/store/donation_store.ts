import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createPayment from '@/store/payment';
import createAddress from '@/store/address';

import {
	NS_PAYMENT,
	NS_ADDRESS,
} from './namespaces';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[ NS_PAYMENT ]: createPayment(),
			[ NS_ADDRESS ]: createAddress(),
		},
		strict: process.env.NODE_ENV !== 'production',
	};

	return new Vuex.Store<any>( storeBundle );
}
