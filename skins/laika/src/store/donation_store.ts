import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createPayment from '@/store/payment';
import createAddress from '@/store/address';
import createBankData from '@/store/bankdata';

import { REQUIRED_FIELDS } from '@/store/address/constants';
import {
	NS_PAYMENT,
	NS_ADDRESS,
	NS_BANKDATA,
} from './namespaces';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[ NS_PAYMENT ]: createPayment(),
			[ NS_ADDRESS ]: createAddress( REQUIRED_FIELDS ),
			[ NS_BANKDATA ]: createBankData(),
		},
		strict: process.env.NODE_ENV !== 'production',
		getters: {
			isValidating: function ( state ): boolean {
				return state[ NS_PAYMENT ].isValidating ||
					state[ NS_ADDRESS ].isValidating ||
					state[ NS_BANKDATA ].isValidating;
			},
		},
	};

	return new Vuex.Store<any>( storeBundle );
}
