import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createAddress from '@/store/membership_address';
import createPayment from '@/store/membership_fee';
import {
	NS_MEMBERSHIP_ADDRESS,
	NS_MEMBERSHIP_FEE,
} from './namespaces';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[ NS_MEMBERSHIP_ADDRESS ]: createAddress(),
			[ NS_MEMBERSHIP_FEE ]: createPayment(),

		},
		strict: process.env.NODE_ENV !== 'production',
	};

	return new Vuex.Store<any>( storeBundle );
}
