import { mount, createLocalVue } from '@vue/test-utils';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Buefy from 'buefy';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

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
		const anon = wrapper.find( '#anonymous' );
		anon.trigger( 'click' );
		expect( wrapper.emitted( event ) ).toHaveLength( 3 );
		expect( wrapper.emitted( event )[ 0 ] ).toEqual( [ AddressTypeModel.COMPANY ] );
		expect( wrapper.emitted( event )[ 1 ] ).toEqual( [ AddressTypeModel.PERSON ] );
		expect( wrapper.emitted( event )[ 2 ] ).toEqual( [ AddressTypeModel.ANON ] );
	} );
} );
