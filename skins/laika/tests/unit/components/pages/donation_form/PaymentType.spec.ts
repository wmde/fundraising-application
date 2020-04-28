import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { setInterval, setType } from '@/store/payment/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const testPaymentMethods = [ 'BEZ', 'PPL', 'UEB', 'BTC' ];

describe( 'PaymentType', () => {

	it( 'emits new payment type when it is selected', () => {
		const wrapper = mount( PaymentType, {
			localVue,
			propsData: {
				paymentTypes: testPaymentMethods,
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );

		wrapper.find( '#payment-btc' ).trigger( 'click' );

		expect( wrapper.emitted( 'payment-type-selected' ) ).toBeTruthy();
		expect( wrapper.emitted( 'payment-type-selected' )![ 0 ] ).toEqual( [ 'BTC' ] );
	} );

	it( 'updates the selected type when the property changes', async () => {
		const wrapper = mount( PaymentType, {
			localVue,
			propsData: {
				paymentTypes: testPaymentMethods,
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );

		// explicitly simulate a prop change from outside of the wrapper
		wrapper.setProps( { currentType: 'PPL' } );
		await wrapper.vm.$nextTick();
		expect( wrapper.vm.$data.selectedType ).toBe( 'PPL' );
	} );

} );
