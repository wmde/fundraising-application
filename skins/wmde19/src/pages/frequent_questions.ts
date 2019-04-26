import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';

import Component from '@/components/pages/Faq.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

import { faqContentFromObject } from '@/view_models/faq.ts';

const PAGE_IDENTIFIER = 'faq-page';

Vue.config.productionTip = false;
Vue.use( VueI18n );

const pageData = new PageDataInitializer<any>( '#app' );
console.log( 'hello: ', pageData.applicationVars );

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
			imagePath: 'http://localhost:7072',
			pageIdentifier: PAGE_IDENTIFIER,
		},
	},
	[
		h( Component, {
			props: {
				content: faqContentFromObject( pageData.applicationVars ),
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
