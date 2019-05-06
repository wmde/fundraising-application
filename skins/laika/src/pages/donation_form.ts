import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '../store/store';

import Component from '@/components/pages/Payment.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

const PAGE_IDENTIFIER = 'donation-form';

const PAYMENT_INTERVAL_OPTIONS = [
	{ interval: 0, id: 'one-time', icon: 'icon-unique' },
	{ interval: 1, id: 'monthly', icon: 'icon-repeat_1' },
	{ interval: 3, id: 'quarterly', icon: 'icon-repeat_3' },
	{ interval: 6, id: 'biannual', icon: 'icon-repeat_6' },
	{ interval: 12, id: 'yearly', icon: 'icon-repeat_12' }
];

const PAYMENT_TYPE_OPTIONS = [
	{ type: 'PPL', id: 'paypal', icon: 'icon-payment-paypal' },
	{ type: 'MCP', id: 'credit-card', icon: 'icon-payment-credit_card' },
	{ type: 'BEZ', id: 'debit-card', icon: 'icon-payment-debit' },
	{ type: 'UEB', id: 'bank-transfer', icon: 'icon-payment-transfer' },
	{ type: 'SUB', id: 'sofort', icon: 'icon-payment-sofort' }
];

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface PaymentAmountModel {
	presetsAmounts: Array<string>
}

const pageData = new PageDataInitializer<PaymentAmountModel>( '#app' );
const donationForm: any = document.getElementById( 'app' );

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
				validateAmountURL: donationForm.getAttribute( 'data-validate-amount-url' ),
				paymentIntervals: PAYMENT_INTERVAL_OPTIONS,
				paymentOptions: PAYMENT_TYPE_OPTIONS
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
