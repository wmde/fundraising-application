import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import DonationForm from '@/components/pages/DonationForm.vue';
import countries from '@/../tests/data/countries';

declare global {
	namespace NodeJS {
		interface Global {
			window: Window;
		}
	}
}
describe( 'DonationForm', () => {
	let wrapper: any;
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
				countries: countries,
				trackingData: { bannerImpressionCount: 0, impressionCount: 0 },
			},
			store: new Vuex.Store( {} ),
			mocks: {
				$t: jest.fn(),
			},
			stubs: {
				PaymentPage: { template: '<div class="i-am-payment" />' },
				AddressPage: { template: '<div class="i-am-address-form" />' },
			},
		} );
	} );

	it( 'displays Payment page by default ', () => {
		expect( wrapper.find( '.i-am-payment' ).exists() ).toBe( true );
	} );

	it( 'loads Address page when next-page is triggered', async () => {
		wrapper.vm.$refs.currentPage.$emit( 'next-page' );
		await wrapper.vm.$nextTick();
		expect( wrapper.find( '.i-am-address-form' ).exists() ).toBe( true );
	} );

	it( 'loads Payment component on the previous page', () => {
		wrapper.vm.$refs.currentPage.$emit( 'next-page' );
		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		expect( wrapper.find( '.i-am-payment' ).exists() ).toBe( true );
	} );

	it( 'does not overshoot the first or last page when multiple page change events trigger', async () => {
		wrapper.vm.$refs.currentPage.$emit( 'next-page' );
		wrapper.vm.$refs.currentPage.$emit( 'next-page' );
		wrapper.vm.$refs.currentPage.$emit( 'next-page' );
		await wrapper.vm.$nextTick();
		expect( wrapper.find( '.i-am-address-form' ).exists() ).toBe( true );

		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		await wrapper.vm.$nextTick();
		expect( wrapper.find( '.i-am-payment' ).exists() ).toBe( true );

		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		wrapper.vm.$refs.currentPage.$emit( 'previous-page' );
		await wrapper.vm.$nextTick();
		expect( wrapper.find( '.i-am-payment' ).exists() ).toBe( true );
	} );

} );
