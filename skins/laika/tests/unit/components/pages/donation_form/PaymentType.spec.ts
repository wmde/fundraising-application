import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { setType } from '@/store/payment/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const testPaymentMethods = [ 'BEZ', 'PPL', 'UEB', 'BTC' ];

describe( 'PaymentType', () => {

	it( 'sends new payment type to store when it is selected', () => {
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
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( '#payment-btc' ).trigger( 'click' );
		const expectedAction = action( NS_PAYMENT, setType );

		expect( store.dispatch ).toBeCalledWith( expectedAction, 'BTC' );
	} );

} );
