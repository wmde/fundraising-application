import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '../store/store';

import Component from '@/components/pages/Payment.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

const PAGE_IDENTIFIER = 'donation-form';

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface PaymentAmountModel {
	presetsAmounts: Array<string>
}

const pageData = new PageDataInitializer<PaymentAmountModel>( '#app' );

const store = createStore();

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

new Vue( {
	store,
	i18n,
	render: h => h( App, {
			props: {
				assetsPath: pageData.assetsPath,
				pageIdentifier: PAGE_IDENTIFIER,
			},
		},
		[
			h( Component, {
				props: {
					paymentAmounts: pageData.applicationVars,
				},
			} ),
			h( Sidebar, {
				slot: 'sidebar',
			} ),
		] ),
} ).$mount( '#app' );
