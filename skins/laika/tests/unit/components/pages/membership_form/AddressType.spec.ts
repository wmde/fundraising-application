import { mount, createLocalVue } from '@vue/test-utils';
import AddressType from '@/components/pages/membership_form/AddressType.vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
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
		company.trigger( 'click' );
		const person = wrapper.find( '#personal' );
		person.trigger( 'click' );
		expect( wrapper.emitted( event ) ).toHaveLength( 2 );
		expect( wrapper.emitted( event )![ 0 ] ).toEqual( [ AddressTypeModel.COMPANY ] );
		expect( wrapper.emitted( event )![ 1 ] ).toEqual( [ AddressTypeModel.PERSON ] );
	} );
} );
