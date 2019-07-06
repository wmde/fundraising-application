import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentInterval from '@/components/shared/PaymentInterval.vue';
import { createStore } from '@/store/donation_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const YEARLY = 12;
const testIntervals: Array<number> = [ 0, 1, 3, 6, YEARLY ];

describe( 'PaymentInterval', () => {

	it( 'emits new interval when it is selected', () => {
		const wrapper = mount( PaymentInterval, {
			localVue,
			propsData: {
				currentInterval: '0',
				paymentIntervals: testIntervals,
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );

		wrapper.find( `#interval-${YEARLY}` ).trigger( 'click' );

		expect( wrapper.emitted( 'interval-selected' ) ).toBeTruthy();
		expect( wrapper.emitted( 'interval-selected' )[ 0 ] ).toEqual( [ String( YEARLY ) ] );
	} );

	it( 'updates the selected interval when the incoming property changes', () => {
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

		// explicitly simulate a prop change from outside of the wrapper
		wrapper.setProps( { currentInterval: '6' } );

		expect( wrapper.vm.$data.selectedInterval ).toBe( '6' );
	} );

} );
