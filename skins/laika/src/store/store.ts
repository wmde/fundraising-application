import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createPayment from './payment';

import {
	NS_PAYMENT
} from './namespaces';

Vue.use(Vuex);

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[NS_PAYMENT]: createPayment()
		},
		strict: process.env.NODE_ENV !== 'production',
	};

	return new Vuex.Store<any>(storeBundle);
}
