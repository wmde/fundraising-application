import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import Component from '@/components/pages/Contact.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { ContactFormValidation } from '@/view_models/Validation';

const PAGE_IDENTIFIER = 'contact-form';

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

interface ContactFormModel {
	message: string,
	contactFormValidationPatterns: ContactFormValidation
}

const pageData = new PageDataInitializer<ContactFormModel>( '#appdata' );

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
			cookieConsent: pageData.cookieConsent,
		},
	},
	[
		h( Component, {
			props: {
				contactData: pageData.applicationVars,
				validationPatterns: pageData.applicationVars.contactFormValidationPatterns,
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
