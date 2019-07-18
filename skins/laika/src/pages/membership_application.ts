import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/membership_store';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { initializeAddress } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';

import Component from '@/components/pages/MembershipForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { InitialMembershipAddress } from '@/view_models/Address';

const PAGE_IDENTIFIER = 'membership-application',
	COUNTRIES = [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface MembershipAmountModel {
	presetAmounts: Array<string>,
	paymentIntervals: Array<string>,
	tracking: Array<number>,
	urls: any,
	showMembershipTypeOption: Boolean,
	initialFormValues: InitialMembershipAddress,
}

const pageData = new PageDataInitializer<MembershipAmountModel>( '#app' );
const i18n = new VueI18n( {
	locale: DEFAULT_LOCALE,
	messages: {
		[ DEFAULT_LOCALE ]: pageData.messages,
	},
} );

const store = createStore();

function initializePage(): void {
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
					validateFeeUrl: pageData.applicationVars.urls.validateMembershipFee,
					validateBankDataUrl: pageData.applicationVars.urls.validateIban,
					validateLegacyBankDataUrl: pageData.applicationVars.urls.convertBankData,
					paymentAmounts: pageData.applicationVars.presetAmounts.map( a => Number( a ) * 100 ),
					addressCountries: COUNTRIES,
					showMembershipTypeOption: pageData.applicationVars.showMembershipTypeOption,
					paymentIntervals: pageData.applicationVars.paymentIntervals,
				},
			} ),
			h( Sidebar, {
				slot: 'sidebar',
			} ),
		] ),
	} ).$mount( '#app' );
}

if ( pageData.applicationVars.initialFormValues !== undefined ) {
	store.dispatch( action( NS_MEMBERSHIP_ADDRESS, initializeAddress ), pageData.applicationVars.initialFormValues ).then( initializePage );
} else {
	initializePage();
}
