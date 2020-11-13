import Vue from 'vue';
import VueI18n from 'vue-i18n';
import VueCompositionApi from '@vue/composition-api';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/membership_store';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { initializeAddress } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';
import { createDataPersister } from '@/store/create_data_persister';
import {
	createInitialMembershipAddressValues,
	createInitialBankDataValues,
	createInitialMembershipFeeValues,
} from '@/store/dataInitializers';
import LocalStorageRepository from '@/store/LocalStorageRepository';
import persistenceItems from '@/store/data_persistence/membership_application';

import Component from '@/components/pages/MembershipForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { InitialMembershipData } from '@/view_models/Address';
import { initializeBankData } from '@/store/bankdata/actionTypes';
import { Country } from '@/view_models/Country';
import { initializeMembershipFee } from '@/store/membership_fee/actionTypes';
import { createTrackFormErrorsPlugin } from '@/store/track_form_errors_plugin';
import { AddressValidation } from '@/view_models/Validation';
import { FeatureTogglePlugin } from '@/FeatureToggle';

const PAGE_IDENTIFIER = 'membership-application';
const FORM_NAMESPACE = 'membership_application';

Vue.config.productionTip = false;
Vue.use( VueI18n );
Vue.use( VueCompositionApi );

interface MembershipAmountModel {
	presetAmounts: Array<string>,
	paymentIntervals: Array<string>,
	tracking: Array<number>,
	countries: Array<Country>,
	urls: any,
	showMembershipTypeOption: Boolean,
	initialFormValues: InitialMembershipData,
	userDataKey: string,
	addressValidationPatterns: AddressValidation,
	dateOfBirthValidationPattern: String,
}

const pageData = new PageDataInitializer<MembershipAmountModel>( '#appdata' );

const dataPersister = createDataPersister(
	new LocalStorageRepository(),
	FORM_NAMESPACE,
	pageData.applicationVars.userDataKey
);

const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

const store = createStore( [
	dataPersister.getPlugin( persistenceItems ),
	createTrackFormErrorsPlugin( FORM_NAMESPACE ),
] );

Vue.use( FeatureTogglePlugin, { activeFeatures: pageData.selectedBuckets } );

dataPersister.initialize( persistenceItems ).then( () => {

	// The PHP serialization sends the initial form data as an empty array (instead of empty object)
	// when donation was anonymous so converting it to a map makes it consistent
	const initialFormValues = new Map( Object.entries( pageData.applicationVars.initialFormValues || {} ) );
	const initialBankAccountData = {
		iban: initialFormValues.get( 'iban' ),
		bic: initialFormValues.get( 'bic' ),
		bankname: initialFormValues.get( 'bankname' ),
	};

	Promise.all( [
		store.dispatch(
			action( NS_MEMBERSHIP_ADDRESS, initializeAddress ),
			createInitialMembershipAddressValues( dataPersister, initialFormValues ),
		),
		store.dispatch(
			action( NS_MEMBERSHIP_FEE, initializeMembershipFee ),
			createInitialMembershipFeeValues( dataPersister, pageData.applicationVars.urls.validateMembershipFee ),
		),
		store.dispatch(
			action( NS_BANKDATA, initializeBankData ),
			createInitialBankDataValues( initialBankAccountData ),
		),
	] ).then( () => {
		new Vue( {
			store,
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
						validateAddressUrl: pageData.applicationVars.urls.validateAddress,
						validateEmailUrl: pageData.applicationVars.urls.validateEmail,
						validateFeeUrl: pageData.applicationVars.urls.validateMembershipFee,
						validateBankDataUrl: pageData.applicationVars.urls.validateIban,
						validateLegacyBankDataUrl: pageData.applicationVars.urls.convertBankData,
						paymentAmounts: pageData.applicationVars.presetAmounts.map( a => Number( a ) * 100 ),
						countries: pageData.applicationVars.countries,
						showMembershipTypeOption: pageData.applicationVars.showMembershipTypeOption,
						paymentIntervals: pageData.applicationVars.paymentIntervals,
						addressValidationPatterns: pageData.applicationVars.addressValidationPatterns,
						dateOfBirthValidationPattern: pageData.applicationVars.dateOfBirthValidationPattern,
					},
				} ),
				h( Sidebar, {
					slot: 'sidebar',
				} ),
			] ),
		} ).$mount( '#app' );
	} );
} );
