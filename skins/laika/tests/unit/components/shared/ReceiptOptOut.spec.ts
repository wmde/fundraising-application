import { mount, createLocalVue } from '@vue/test-utils';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'ReceiptOptOut', () => {

	it( 'receipt checkbox is checked on initial render and can be opted-out from', () => {
		const wrapper = mount( ReceiptOptOut, {
				localVue,
				mocks: {
					$t: () => { },
				},
			} ),
			checkBox = wrapper.find( '#donation_receipt' );

		expect( checkBox.props().value ).toBe( true );
	} );

	it( 'emits opt out event on change', () => {
		const wrapper = mount( ReceiptOptOut, {
				localVue,
				mocks: {
					$t: () => { },
				},
			} ),
			event = 'opted-out',
			checkBox = wrapper.find( '#donation_receipt' );
		checkBox.trigger( 'click' );
		expect( wrapper.emitted( event )![ 0 ] ).toEqual( [ true ] );
	} );

} );
