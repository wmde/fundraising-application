import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import Payment from '@/components/pages/membership_form/Payment.vue';
import AmountSelection from '@/components/shared/AmountSelection.vue';
import { createStore } from '@/store/membership_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'Payment.vue', () => {
	it( 'sets correct amount title when interval is selected', async () => {
		const wrapper = mount( Payment, {
			localVue,
			propsData: {
				validateFeeUrl: 'https://example.com/amount-check',
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				validateBankDataUrl: 'https://example.com/amount-check',
				validateLegacyBankDataUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			stubs: {
				AmountSelection: true,
			},
			mocks: {
				$t: ( key: string ): string => { return key; },
			},
		} );

		const interval1 = wrapper.find( '#interval-1 input' );
		const interval12 = wrapper.find( '#interval-12 input' );
		const amountSelection = wrapper.find( AmountSelection );

		expect( amountSelection.vm.$props.title ).toEqual( 'membership_form_payment_amount_title' );

		await interval1.trigger( 'click' );
		expect( amountSelection.vm.$props.title ).toEqual( 'membership_form_payment_amount_title_interval_1' );

		await interval12.trigger( 'click' );
		expect( amountSelection.vm.$props.title ).toEqual( 'membership_form_payment_amount_title_interval_12' );
	} );
} );
