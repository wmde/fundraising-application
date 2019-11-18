import Vue from 'vue';
import Vuex, { StoreOptions } from 'vuex';
import createAddress from '@/store/membership_address';
import createBankData from '@/store/bankdata';
import createPayment from '@/store/membership_fee';
import {
	NS_ADDRESS,
	NS_BANKDATA,
	NS_MEMBERSHIP_ADDRESS,
	NS_MEMBERSHIP_FEE,
} from './namespaces';

Vue.use( Vuex );

export function createStore() {
	const storeBundle: StoreOptions<any> = {
		modules: {
			[ NS_MEMBERSHIP_ADDRESS ]: createAddress(),
			[ NS_MEMBERSHIP_FEE ]: createPayment(),
			[ NS_BANKDATA ]: createBankData(),
		},
		strict: process.env.NODE_ENV !== 'production',
		getters: {
			isValidating: function ( state ): boolean {
				return state[ NS_MEMBERSHIP_FEE ].isValidating ||
					// TODO use getters instead
					state[ NS_MEMBERSHIP_ADDRESS ].serverSideValidationCount > 0 ||
					state[ NS_BANKDATA ].isValidating;
			},
		},
	};

	return new Vuex.Store<any>( storeBundle );
}
