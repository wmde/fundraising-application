import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import DonationForm from '@/components/pages/DonationForm.vue';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

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
		'payment/paymentDataIsValid': jest.fn(),
		'address/fullName': jest.fn().mockReturnValue( 'Prof. Dr. Alfons Knochensäger' ),
	};
	// The minimum required state for the computed properties of DonationForm
	const state = {
		payment: {
			values: {
				amount: '2349',
				interval: '0',
				type: 'PPL',
			},
		},
		address: {
			addressType: AddressTypeModel.PERSON,
			values: {
				street: 'Am Weier',
				city: 'Hintertupfingen',
				postcode: '87741',
			},
		},
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
				trackingData: { bannerImpressionCount: 0, impressionCount: 0 },
			},
			store: new Vuex.Store( {
				state,
				actions,
				getters,
			} ),
			mocks: {
				$t: jest.fn(),
			},
			stubs: {
				Payment: '<div class="i-am-payment" />',
				AddressForm: '<div class="i-am-address-form" />',
				DonationSummary: '<div class="i-am-summary" />',
			},
		} );
	} );

	it( 'displays Payment component by default ', () => {
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

	it( 'shows next button on Payment page', () => {
		expect( wrapper.find( 'button#next' ).exists() ).toBe( true );
		expect( wrapper.find( 'button#previous' ).exists() ).toBe( false );
		expect( wrapper.find( 'button#submit' ).exists() ).toBe( false );
	} );

	it( 'loads Address component on the next page', () => {
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-address-form' ) ).toBe( true );
	} );

	it( 'shows previous and submit button on Address page', () => {
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.find( 'button#next' ).exists() ).toBe( false );
		expect( wrapper.find( 'button#previous' ).exists() ).toBe( true );
		expect( wrapper.find( 'button#submit' ).exists() ).toBe( true );
	} );

	it( 'loads Payment component on the previous page', () => {
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		wrapper.find( '#previous' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

	it( 'validates the input before going to the next page', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_PAYMENT, markEmptyValuesAsInvalid );
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( store.dispatch ).toHaveBeenCalledWith( expectedAction );
	} );

	it( 'doesn\'t load the next page if there are validation errors', () => {
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( false );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-address-form' ) ).toBe( false );
		expect( wrapper.contains( '.i-am-payment' ) ).toBe( true );
	} );

	it( 'shows the donation summary on the address page', () => {
		getters['payment/paymentDataIsValid'].mockReturnValueOnce( true );
		wrapper.find( '#next' ).trigger( 'click' );
		expect( wrapper.contains( '.i-am-summary' ) ).toBe( true );
	} );

	it( 'hides the donation summary on the payment page', () => {
		expect( wrapper.contains( '.i-am-summary' ) ).toBe( false );
	} );

} );
