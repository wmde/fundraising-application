import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import PaymentNotice from '@/components/pages/donation_confirmation/PaymentNotice.vue';
import { createStore } from '@/store/donation_store';
import each from 'jest-each';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'PaymentNotice', () => {
	each( [
		[ 'UEB', 'donation_confirmation_payment_bank_transfer' ],
		[ 'BEZ', 'donation_confirmation_payment_direct_debit' ],
		[ 'PPL', '' ],
		[ 'MCP', '' ],
		[ 'SUB', '' ],
	] ).test( 'renders the correct text for payment method "%s"', ( paymentType, expectedText ) => {
		const wrapper = mount( PaymentNotice, {
			localVue,
			propsData: {
				payment: {
					paymentType: paymentType,
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '.payment-notice' ).text() ).toMatch( expectedText );
	} );
} );
