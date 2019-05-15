import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentInterval from '@/components/pages/donation_form/PaymentInterval.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { setInterval } from '@/store/payment/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const YEARLY = 12;
const testIntervals: Array<number> = [ 0, 1, 3, 6, YEARLY ];

describe( 'PaymentInterval', () => {

	it( 'sends new interval to store when it is selected', () => {
		const wrapper = mount( PaymentInterval, {
			localVue,
			propsData: {
				paymentIntervals: testIntervals,
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( `#interval-${YEARLY}` ).trigger( 'click' );
		const expectedAction = action( NS_PAYMENT, setInterval );

		expect( store.dispatch ).toBeCalledWith( expectedAction, YEARLY );
	} );

} );
