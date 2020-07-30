import { createLocalVue, mount } from '@vue/test-utils';
import AutocompleteCity from '@/components/shared/AutocompleteCity.vue';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

describe( 'AutocompleteCity.vue', () => {
	it( 'emits field changed event on blur', () => {
		const wrapper = mount( AutocompleteCity, {
				localVue,
				mocks: {
					$t: () => { },
				},
				propsData: {
					placeholder: '',
					city: { value: '' },
					showError: false,
					postcode: '',
					postalLocalityResource: { getPostalLocalities: async () => {} },
				},
			} ),
			event = 'field-changed',
			field = wrapper.find( '#city' );
		field.trigger( 'blur' );
		expect( wrapper.emitted( event )![ 0 ] ).not.toBeNull();
	} );
} );
