import { mount, createLocalVue } from '@vue/test-utils';
import Name from '@/components/shared/Name.vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );

function newTestProperties( overrides: Object ) {
	return Object.assign(
		{
			showError: {
				salutation: false,
				companyName: false,
				firstName: false,
				lastName: false,
				street: false,
				city: false,
				postcode: false,
			},
			formData: {
				salutation: {
					name: 'salutation',
					value: '',
					pattern: '^(Herr|Frau)$',
					optionalField: false,
				},
				title: {
					name: 'title',
					value: '',
					pattern: '',
					optionalField: true,
				},
				companyName: {
					name: 'companyName',
					value: '',
					pattern: '^.+$',
					optionalField: true,
				},
				firstName: {
					name: 'firstName',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				lastName: {
					name: 'lastName',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				street: {
					name: 'street',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				city: {
					name: 'city',
					value: '',
					pattern: '^.+$',
					optionalField: false,
				},
				postcode: {
					name: 'postcode',
					value: '',
					pattern: '[0-9]{4,5}$',
					optionalField: false,
				},
				country: {
					name: 'country',
					value: 'DE',
					pattern: '',
					optionalField: false,
				},
			},
			addressType: AddressTypeModel.PERSON,
		},
		overrides
	);
}

describe( 'Name.vue', () => {

	it( 'emits field changed event on blur', () => {
		const wrapper = mount( Name, {
				localVue,
				mocks: {
					$t: () => { },
				},
				propsData: newTestProperties( {} ),
			} ),
			event = 'field-changed',
			first = wrapper.find( '#first-name' );
		first.trigger( 'blur' );
		expect( wrapper.emitted( event )[ 0 ] ).toEqual( [ 'firstName' ] );
	} );
} );
