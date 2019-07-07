import { createLocalVue, shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';

import Payment from '@/components/pages/donation_form/Payment.vue';
import { NS_PAYMENT } from '@/store/namespaces';
import { setAmount, setInterval, setType } from '@/store/payment/actionTypes';
import PaymentInterval from '@/components/shared/PaymentInterval.vue';
import AmountSelection from '@/components/shared/AmountSelection.vue';
import PaymentType from '@/components/pages/donation_form/PaymentType.vue';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'Payment', () => {
	it( 'sends amount to store when amount selection emits event ', () => {
		const wrapper = shallowMount( Payment, {
			localVue,
			propsData: {
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				paymentTypes: [ 'BEZ', 'PPL', 'UEB', 'BTC' ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: jest.fn(),
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedPayload = {
			amountValue: '1500',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		wrapper.find( AmountSelection ).vm.$emit( 'amount-selected', '1500' );

		expect( store.dispatch ).toBeCalledWith( action( NS_PAYMENT, setAmount ), expectedPayload );
	} );

	it( 'sends interval to store when interval selection emits event ', () => {
		const wrapper = shallowMount( Payment, {
			localVue,
			propsData: {
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				paymentTypes: [ 'BEZ', 'PPL', 'UEB', 'BTC' ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: jest.fn(),
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( PaymentInterval ).vm.$emit( 'interval-selected', 6 );

		expect( store.dispatch ).toBeCalledWith( action( NS_PAYMENT, setInterval ), 6 );
	} );

	it( 'sends payment type to store when payment selection emits event ', () => {
		const wrapper = shallowMount( Payment, {
			localVue,
			propsData: {
				paymentAmounts: [ 5 ],
				paymentIntervals: [ 0, 1, 3, 6, 12 ],
				paymentTypes: [ 'BEZ', 'PPL', 'UEB', 'BTC' ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: jest.fn(),
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( PaymentType ).vm.$emit( 'payment-type-selected', 'PPL' );

		expect( store.dispatch ).toBeCalledWith( action( NS_PAYMENT, setType ), 'PPL' );
	} );
} );
