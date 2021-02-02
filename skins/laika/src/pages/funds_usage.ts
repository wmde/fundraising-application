import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import VueCompositionApi from '@vue/composition-api';

import Component from '@/components/pages/UseOfFunds.vue';

const PAGE_IDENTIFIER = 'use-of-funds',
	IS_FULLWIDTH_PAGE = true;

Vue.config.productionTip = false;
Vue.use( VueCompositionApi );
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

const pageData = new PageDataInitializer<any>( '#appdata' );

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

new Vue( {
	i18n,
	render: h => h( App, {
		props: {
			assetsPath: pageData.assetsPath,
			pageIdentifier: PAGE_IDENTIFIER,
			isFullWidth: IS_FULLWIDTH_PAGE,
			cookieConsent: pageData.cookieConsent,
		},
	},
	[
		h( Component, {
			props: {
				content: pageData.applicationVars,
				assetsPath: pageData.assetsPath,
				// TODO propagate locale from application vars
				locale: 'de',
			},
		} ),
	] ),
} ).$mount( '#app' );
