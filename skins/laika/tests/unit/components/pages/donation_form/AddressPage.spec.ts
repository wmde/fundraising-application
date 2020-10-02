import { createLocalVue, shallowMount } from '@vue/test-utils';

import AddressPage from '@/components/pages/donation_form/subpages/AddressPage.vue';
import Vuex from 'vuex';
import Buefy from 'buefy';
import CompositionAPI from '@vue/composition-api';
import { createStore } from '@/store/donation_store';
import Address from '@/components/pages/donation_form/Address.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { initializePayment } from '@/store/payment/actionTypes';
import { FeatureTogglePlugin } from '@/FeatureToggle';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );
localVue.use( CompositionAPI );

localVue.use( FeatureTogglePlugin, { activeFeatures: [
	'campaigns.address_type.preselection',
	'campaigns.address_provision_options.old_address_type_options',
] } );

describe( 'AddressPage', () => {

	let wrapper: any;
	let store: any;

	beforeEach( () => {
		store = createStore();
		wrapper = shallowMount( AddressPage, {
			localVue,
			store,
			propsData: {
			},
			mocks: {
				$t: () => { },
			},
			stubs: {
				Address: true,
			},
		} );
	} );

	it( 'sends directDebit property value "true" if payment type is direct debit', async () => {
		expect( wrapper.findComponent( Address ).vm.$options.propsData.isDirectDebit ).toBe( false );

		return store.dispatch( action( NS_PAYMENT, initializePayment ), {
			amount: '100',
			type: 'BEZ',
			paymentIntervalInMonths: '0',
			isCustomAmount: false,
		} ).then( () => {
			expect( wrapper.findComponent( Address ).vm.$options.propsData.isDirectDebit ).toBe( true );
		} );

	} );

	it( 'sends directDebit property value "false" if payment type is not direct debit', async () => {
		expect( wrapper.findComponent( Address ).vm.$options.propsData.isDirectDebit ).toBe( false );

		return store.dispatch( action( NS_PAYMENT, initializePayment ), {
			amount: '100',
			type: 'UEB',
			paymentIntervalInMonths: '0',
			isCustomAmount: false,
		} ).then( () => {
			expect( wrapper.findComponent( Address ).vm.$options.propsData.isDirectDebit ).toBe( false );
		} );

	} );

} );
