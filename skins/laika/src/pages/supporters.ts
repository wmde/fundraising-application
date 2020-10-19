import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';

import Component from '@/components/pages/Supporters.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { supportersFromObject } from '@/view_models/supporters';

const PAGE_IDENTIFIER = 'supporters';

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

interface ErrorModel {
	message: string
}

const pageData = new PageDataInitializer<ErrorModel>( '#appdata' );

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
		},
	},
	[
		h( Component, {
			props: {
				supporters: supportersFromObject( pageData.applicationVars ),
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
