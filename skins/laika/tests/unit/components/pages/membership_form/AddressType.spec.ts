import { mount, createLocalVue } from '@vue/test-utils';
import AddressType from '@/components/pages/membership_form/AddressType.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'AddressType.vue', () => {

	it( 'emits field changed event on blur', () => {
		const wrapper = mount( AddressType, {
				localVue,
				mocks: {
					$t: () => { },
				},
			} ),
			event = 'address-type',
			company = wrapper.find( '#company' );
		company.trigger( 'change' );
		const person = wrapper.find( '#personal' );
		person.trigger( 'change' );
		expect( wrapper.emitted( event ) ).toHaveLength( 2 );
	} );
} );
