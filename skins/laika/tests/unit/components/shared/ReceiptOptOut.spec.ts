import { mount, createLocalVue } from '@vue/test-utils';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'ReceiptOptOut', () => {

	it( 'is unchecked on initial render', () => {
		const wrapper = mount( ReceiptOptOut, {
				localVue,
				mocks: {
					$t: () => { },
				},
			} ),
			checkBox = wrapper.find( '#donation_receipt' );

		expect( checkBox.props().value ).toBe( false );
	} );

	it( 'emits opt out event on change', () => {
		const wrapper = mount( ReceiptOptOut, {
				localVue,
				mocks: {
					$t: () => { },
				},
			} ),
			event = 'optedOut',
			checkBox = wrapper.find( '#donation_receipt' );
		checkBox.trigger( 'change' );

		expect( wrapper.emitted( event ) ).toHaveLength( 1 );
	} );

} );
