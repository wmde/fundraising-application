import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import { createStore } from '@/store/update_address_store';

import App from '@/components/App.vue';
import Component from '@/components/pages/UpdateAddress.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { createTrackFormErrorsPlugin } from '@/store/track_form_errors_plugin';

const PAGE_IDENTIFIER = 'update-address';
const FORM_NAMESPACE = 'update_address';

Vue.config.productionTip = false;
Vue.use( VueI18n );

const pageData = new PageDataInitializer<any>( '#app' );
const store = createStore( [
	createTrackFormErrorsPlugin( FORM_NAMESPACE ),
] );

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
				updateAddressURL: pageData.applicationVars.urls.updateAddress,
				isCompany: pageData.applicationVars.isCompany,
				countries: pageData.applicationVars.countries,
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
