import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import PaymentAmount from '@/components/pages/donation_form/PaymentAmount.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyAmountAsInvalid, setAmount } from '@/store/payment/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );

describe( 'PaymentAmount', () => {

	it( 'sends new amount to store when amount is selected', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 5, 10, 100, 299 ],
				validateAmountURL: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( '#amount-29900' ).trigger( 'click' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '29900',
			validateAmountURL: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'clears custom amount when amount is selected', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 5, 10, 100, 299 ],
				validateAmountURL: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '.custom-amount' );
		( customAmountInput.element as HTMLInputElement ).value = '5';
		customAmountInput.trigger( 'blur' );
		wrapper.find( '#amount-29900' ).trigger( 'click' );

		expect( ( customAmountInput.element as HTMLInputElement ).value ).toBe( '' );
	} );

} );
