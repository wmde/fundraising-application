import { mount, createLocalVue } from '@vue/test-utils';
import Postal from '@/components/shared/Postal.vue';
import Buefy from 'buefy';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

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
					value: 'Testenhofen Ufer',
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
			countries: [],
			addressType: AddressTypeModel.PERSON,
		},
		overrides
	);
}

describe( 'Postal.vue', () => {
	it( 'shows street number warning if street field does not contain numbers', () => {
		const wrapper = mount( Postal, {
				localVue,
				mocks: {
					$t: () => { },
				},
				propsData: newTestProperties( {} ),
			} ),
			street = wrapper.find( '#street' );
		street.trigger( 'blur' );
		expect( wrapper.vm.$data.showWarning ).toBe( true );
	} );

	it( 'emits field changed event on blur', () => {
		const wrapper = mount( Postal, {
				localVue,
				mocks: {
					$t: () => { },
				},
				propsData: newTestProperties( {} ),
			} ),
			event = 'field-changed',
			field = wrapper.find( '#city' );
		field.trigger( 'blur' );
		expect( wrapper.emitted( event ) ).toHaveLength( 1 );
	} );
} );
