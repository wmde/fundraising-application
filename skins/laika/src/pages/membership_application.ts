import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/membership_store';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { initializeAddress } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';

import Component from '@/components/pages/MembershipForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import { InitialMembershipData } from '@/view_models/Address';
import { initializeBankData } from '@/store/bankdata/actionTypes';

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
	initialFormValues: InitialMembershipData,
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

// The PHP serialization sends the initial for data as empty array (instead of empty object) when donation was anonymous
if ( pageData.applicationVars.initialFormValues !== undefined && !( ( pageData.applicationVars.initialFormValues as any ) instanceof Array ) ) {
	const initializationCalls = [
		store.dispatch( action( NS_MEMBERSHIP_ADDRESS, initializeAddress ), pageData.applicationVars.initialFormValues ),
	];
	if ( pageData.applicationVars.initialFormValues.iban ) {
		initializationCalls.push(
			store.dispatch(
				action( NS_BANKDATA, initializeBankData ),
				{
					accountId: pageData.applicationVars.initialFormValues.iban,
					bankId: pageData.applicationVars.initialFormValues.bic || '',
					bankName: pageData.applicationVars.initialFormValues.bankname || '',
				}
			)
		);
	}
	Promise.all( initializationCalls ).then( initializePage );
} else {
	initializePage();
}
