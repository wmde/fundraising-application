import Vue from 'vue';
import VueI18n from 'vue-i18n';
import PageDataInitializer from '@/page_data_initializer';
import { DEFAULT_LOCALE } from '@/locales';
import App from '@/components/App.vue';
import { createStore } from '@/store/membership_store';
import { AddressTypeModel, addressTypeFromName } from '@/view_models/AddressTypeModel';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setAddressType, setEmail } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';

import Component from '@/components/pages/MembershipForm.vue';
import Sidebar from '@/components/layout/Sidebar.vue';

const PAGE_IDENTIFIER = 'membership-application',
	COUNTRIES = [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];

Vue.config.productionTip = false;
Vue.use( VueI18n );

interface initialFormValues {
	addressType: string,
	city: string,
	companyName: string,
	country: string,
	email: string,
	firstName: string,
	lastName: string,
	postcode: string,
	salutation: string,
	street: string,
	title: string,

}
interface MembershipAmountModel {
	presetAmounts: Array<string>,
	paymentIntervals: Array<string>,
	tracking: Array<number>,
	urls: any,
	showMembershipTypeOption: Boolean,
	initialFormValues: initialFormValues,
}

const pageData = new PageDataInitializer<MembershipAmountModel>( '#app' );
const store = createStore();
if ( pageData.applicationVars.initialFormValues !== undefined ) {
	const initialAddressType: AddressTypeModel = addressTypeFromName( pageData.applicationVars.initialFormValues.addressType );
	store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setAddressType ), initialAddressType );
	store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setEmail ), pageData.applicationVars.initialFormValues.email );
}

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
