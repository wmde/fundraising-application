import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentPage from '@/components/pages/donation_form/subpages/PaymentPage.vue';

describe( 'DonationForm', () => {
	let wrapper: any;

	const actions = {
		'payment/markEmptyValuesAsInvalid': jest.fn(),
	};
	const getters = {
		'payment/paymentDataIsValid': jest.fn(),
	};
	beforeEach( () => {
		global.window.scrollTo = jest.fn();
		const localVue = createLocalVue();
		localVue.use( Vuex );
		localVue.use( Buefy );
		wrapper = mount( PaymentPage, {
			localVue,
			propsData: {
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				paymentTypes: [ 'BEZ', 'PPL', 'UEB', 'BTC' ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: new Vuex.Store( {
				actions,
				getters,
			} ),
			mocks: {
				$t: jest.fn(),
			},
			stubs: {
				Payment: '<div class="i-am-payment" />',
			},
		} );
	} );

	it( 'validates the input before going to the next page', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn().mockResolvedValue( true );
		const expectedAction = action( NS_PAYMENT, markEmptyValuesAsInvalid );
		getters[ 'payment/paymentDataIsValid' ].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( store.dispatch ).toHaveBeenCalledWith( expectedAction );
	} );

	it( 'doesn\'t load the next page if there are validation errors', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn().mockResolvedValue( true );
		getters[ 'payment/paymentDataIsValid' ].mockReturnValueOnce( false );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-address-form' ) ).toBe( false );
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );
} );
