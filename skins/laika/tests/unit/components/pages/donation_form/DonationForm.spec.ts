import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import DonationForm from '@/components/pages/DonationForm.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';

declare global {
	namespace NodeJS {
		interface Global {
			window: Window;
		}
	}
}
describe( 'DonationForm', () => {
	let wrapper: any;
	const actions = {
		'payment/markEmptyValuesAsInvalid': jest.fn(),
	};
	const getters = {
		'payment/paymentDataIsValid': jest.fn()
			.mockReturnValueOnce( true )
			.mockReturnValueOnce( true )
			.mockReturnValueOnce( true )
			.mockReturnValueOnce( true )
			.mockReturnValueOnce( false ),
	};
	beforeEach( () => {
		global.window.scrollTo = jest.fn();
		const localVue = createLocalVue();
		localVue.use( Vuex );
		localVue.use( Buefy );
		wrapper = mount( DonationForm, {
			localVue,
			propsData: {
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				paymentTypes: [ 'BEZ', 'PPL', 'UEB', 'BTC' ],
				validateAmountUrl: 'https://example.com/amount-check',
				validateAddressUrl: 'https://example.com/address-check',
				addressCountries: [ 'DE' ],
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
				AddressForm: '<div class="i-am-address-form" />',
			},
		} );
	} );

	it( 'displays Payment component by default ', () => {
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

	it( 'shows next button on Payment page', () => {
		expect( wrapper.html() ).toContain( '<button type="button" id="next" class="button level-item is-primary is-main">' );
	} );

	it( 'loads Address component on the next page', () => {
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-address-form' ) ).toBe( true );
	} );

	it( 'shows previous and submit button on Address page', () => {
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.html() ).toContain( '<button type="button" id="previous" class="button level-item is-primary is-main">' );
		expect( wrapper.html() ).toContain( '<button type="button" id="submit" class="button level-item is-primary is-main">' );
	} );

	it( 'loads Payment component on the previous page', () => {
		wrapper.find( '#next' ).trigger( 'click' );
		wrapper.find( '#previous' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

	it( 'validates the input before going to the next page', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_PAYMENT, markEmptyValuesAsInvalid );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( store.dispatch ).toHaveBeenCalledWith( expectedAction );
	} );

	it( 'doesn\'t load the next page if there are validation errors', () => {
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-address-form' ) ).toBe( false );
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

} );
