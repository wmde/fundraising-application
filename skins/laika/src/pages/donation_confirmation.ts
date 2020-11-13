import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import { createStore } from '@/store/donor_update_store';
import { clearPersistentData } from '@/store/create_data_persister';
import LocalStorageRepository from '@/store/LocalStorageRepository';
import App from '@/components/App.vue';
import Component from '@/components/pages/DonationConfirmation.vue';
import { Country } from '@/view_models/Country';
import { Donation } from '@/view_models/Donation';
import { AddressValidation } from '@/view_models/Validation';

const PAGE_IDENTIFIER = 'donation-confirmation',
	IS_FULLWIDTH_PAGE = true,
	LOCAL_STORAGE_DELETION_NAMESPACES = [ 'donation_form', 'membership_application' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

clearPersistentData( new LocalStorageRepository(), LOCAL_STORAGE_DELETION_NAMESPACES );

interface DonationConfirmationModel {
	urls: { [ key: string ]: string },
	countries: Array<Country>,
	donation: Donation,
	address: Object,
	addressType: String,
	addressValidationPatterns: AddressValidation,
}

const pageData = new PageDataInitializer<DonationConfirmationModel>( '#appdata' );
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
			cookieConsent: pageData.cookieConsent,
		},
	},
	[
		h( Component, {
			props: {
				donation: pageData.applicationVars.donation,
				address: pageData.applicationVars.address,
				addressType: pageData.applicationVars.addressType,
				countries: pageData.applicationVars.countries,
				validateAddressUrl: pageData.applicationVars.urls.validateAddress,
				validateEmailUrl: pageData.applicationVars.urls.validateEmail,
				updateDonorUrl: pageData.applicationVars.urls.updateDonor,
				cancelDonationUrl: pageData.applicationVars.urls.cancelDonation,
				postCommentUrl: pageData.applicationVars.urls.postComment,
				addressValidationPatterns: pageData.applicationVars.addressValidationPatterns,
			},
		} ),
	] ),
} ).$mount( '#app' );
