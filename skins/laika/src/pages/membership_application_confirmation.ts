import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';

import Component from '@/components/pages/MembershipConfirmation.vue';
import { clearPersistentData } from '@/store/create_data_persister';
import LocalStorageRepository from '@/store/LocalStorageRepository';

const PAGE_IDENTIFIER = 'membership-application-confirmation',
	IS_FULLWIDTH_PAGE = true,
	LOCAL_STORAGE_DELETION_NAMESPACES = [ 'donation_form', 'membership_application' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

clearPersistentData( new LocalStorageRepository(), LOCAL_STORAGE_DELETION_NAMESPACES );

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
				confirmationData: pageData.applicationVars,
			},
		} ),
	] ),
} ).$mount( '#app' );
