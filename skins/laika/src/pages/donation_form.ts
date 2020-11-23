import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/donation_store';

import Component from '@/components/pages/DonationForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { action } from '@/store/util';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import persistenceItems from '@/store/data_persistence/donation_form';
import { initializePayment } from '@/store/payment/actionTypes';
import { FeatureTogglePlugin } from '@/FeatureToggle';
import { bucketIdToCssClass } from '@/bucket_id_to_css_class';
import { createDataPersister } from '@/store/create_data_persister';
import { createInitialDonationAddressValues, createInitialDonationPaymentValues } from '@/store/dataInitializers';
import LocalStorageRepository from '@/store/LocalStorageRepository';
import { initializeAddress } from '@/store/address/actionTypes';
import { Country } from '@/view_models/Country';
import { createTrackFormErrorsPlugin } from '@/store/track_form_errors_plugin';
import { AddressValidation } from '@/view_models/Validation';

const PAGE_IDENTIFIER = 'donation-form';
const FORM_NAMESPACE = 'donation_form';

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

interface DonationFormModel {
	initialFormValues: any,
	presetAmounts: Array<string>,
	paymentTypes: Array<string>,
	paymentIntervals: Array<number>,
	tracking: Array<number>,
	countries: Array<Country>,
	urls: any,
	userDataKey: string,
	addressValidationPatterns: AddressValidation
}

const pageData = new PageDataInitializer<DonationFormModel>( '#appdata' );
const dataPersister = createDataPersister(
	new LocalStorageRepository(),
	FORM_NAMESPACE,
	pageData.applicationVars.userDataKey
);
const store = createStore( [
	dataPersister.getPlugin( persistenceItems ),
	createTrackFormErrorsPlugin( FORM_NAMESPACE ),
] );

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

Vue.use( FeatureTogglePlugin, { activeFeatures: pageData.selectedBuckets } );

dataPersister.initialize( persistenceItems ).then( () => {
	Promise.all( [
		store.dispatch(
			action( NS_PAYMENT, initializePayment ),
			createInitialDonationPaymentValues( dataPersister, pageData.applicationVars.initialFormValues )
		),
		store.dispatch(
			action( NS_ADDRESS, initializeAddress ),
			createInitialDonationAddressValues( dataPersister, pageData.applicationVars.initialFormValues )
		),
	] ).then( ( [ paymentDataComplete, _ ] ) => {

		new Vue( {
			store,
			i18n,
			render: h => h( App, {
				props: {
					assetsPath: pageData.assetsPath,
					pageIdentifier: PAGE_IDENTIFIER,
					validateAddressUrl: pageData.applicationVars.urls.validateAddress,
					validateEmailUrl: pageData.applicationVars.urls.validateEmail,
					validateAmountUrl: pageData.applicationVars.urls.validateDonationAmount,
					paymentAmounts: pageData.applicationVars.presetAmounts,
					paymentIntervals: pageData.applicationVars.paymentIntervals,
					paymentTypes: pageData.applicationVars.paymentTypes,
					countries: pageData.applicationVars.countries,
					trackingData: pageData.applicationVars.tracking,
					bucketClasses: bucketIdToCssClass( pageData.selectedBuckets ),
				},
			},
			[
				h( Component, {
					props: {
						validateAddressUrl: pageData.applicationVars.urls.validateAddress,
						validateEmailUrl: pageData.applicationVars.urls.validateEmail,
						validateAmountUrl: pageData.applicationVars.urls.validateDonationAmount,
						validateBankDataUrl: pageData.applicationVars.urls.validateIban,
						validateLegacyBankDataUrl: pageData.applicationVars.urls.convertBankData,
						paymentAmounts: pageData.applicationVars.presetAmounts.map( a => Number( a ) * 100 ),
						paymentIntervals: pageData.applicationVars.paymentIntervals,
						paymentTypes: pageData.applicationVars.paymentTypes,
						countries: pageData.applicationVars.countries,
						trackingData: pageData.applicationVars.tracking,
						addressValidationPatterns: pageData.applicationVars.addressValidationPatterns,
						startPage: paymentDataComplete ? 'AddressPage' : 'PaymentPage',
					},
				} ),
				h( Sidebar, {
					slot: 'sidebar',
				} ),
			] ),
		} ).$mount( '#app' );
	} );

} );
