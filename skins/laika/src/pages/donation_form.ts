import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/donation_store';

import Component from '@/components/pages/DonationForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

const PAGE_IDENTIFIER = 'donation-form',
	COUNTRIES = [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface DonationAmountModel {
	presetAmounts: Array<string>,
	paymentTypes: Array<string>,
	paymentIntervals: Array<number>
	urls: any
}

const pageData = new PageDataInitializer<DonationAmountModel>( '#app' );
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
				validateAddressUrl: pageData.applicationVars.urls.validateAddress,
				validateAmountUrl: pageData.applicationVars.urls.validateDonationAmount,
				paymentAmounts: pageData.applicationVars.presetAmounts,
				paymentIntervals: pageData.applicationVars.paymentIntervals,
				paymentTypes: pageData.applicationVars.paymentTypes,
				addressCountries: COUNTRIES,
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
