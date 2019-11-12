import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import { createStore } from '@/store/update_address_store';

import App from '@/components/App.vue';

import Component from '@/components/pages/DonationConfirmation.vue';
import { action } from '@/store/util';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressField } from '@/store/address/actionTypes';
import { InputField } from "@/view_models/Address";

const PAGE_IDENTIFIER = 'donation-confirmation',
	IS_FULLWIDTH_PAGE = true,
	ADDRESS_UPDATE_COUNTRIES = [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );

const pageData = new PageDataInitializer<any>( '#app' );
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
			isFullWidth: IS_FULLWIDTH_PAGE,
		},
	},
	[
		h( Component, {
			props: {
				confirmationData: pageData.applicationVars,
				countries: ADDRESS_UPDATE_COUNTRIES,
				validateAddressUrl: pageData.applicationVars.urls.validateAddress,
				updateDonorUrl: pageData.applicationVars.urls.updateDonor,
			},
		} ),
	] ),
} ).$mount( '#app' );
