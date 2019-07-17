import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/membership_store';

import Component from '@/components/pages/MembershipForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

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
	initialFormValues: Object | String,
}

const pageData = new PageDataInitializer<MembershipAmountModel>( '#app' );
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
		},
	},
	[
		h( Component, {
			props: {
				validateAddressUrl: pageData.applicationVars.urls.validateAddress,
				validateFeeUrl: pageData.applicationVars.urls.validateMembershipFee,
				paymentAmounts: pageData.applicationVars.presetAmounts.map( a => Number( a ) * 100 ),
				addressCountries: COUNTRIES,
				trackingData: pageData.applicationVars.tracking,
				showMembershipTypeOption: pageData.applicationVars.showMembershipTypeOption,
				initialFormValues: pageData.applicationVars.initialFormValues !== undefined ? pageData.applicationVars.initialFormValues : '',
				paymentIntervals: pageData.applicationVars.paymentIntervals,
			},
		} ),
		h( Sidebar, {
			slot: 'sidebar',
		} ),
	] ),
} ).$mount( '#app' );
