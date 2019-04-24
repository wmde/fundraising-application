import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';

import Component from '@/components/pages/Placeholder.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

const PAGE_IDENTIFIER = 'membership-application';

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface ErrorModel {
	message: string
}

const pageData = new PageDataInitializer<ErrorModel>( '#app' );

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

// TODO imagePath needs to be dynamically established
new Vue( {
	i18n,
	render: h => h( App, {
		props: {
			imagePath: pageData.assetsPath,
			pageIdentifier: PAGE_IDENTIFIER,
		},
	},
	[
		h( Component, {} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
