import { mount, createLocalVue } from '@vue/test-utils';
import Postal from '@/components/shared/Postal.vue';
import Buefy from 'buefy';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import countries from '@/../tests/data/countries';
import { addressValidationPatterns } from '@/validation';

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
					pattern: '^.+$',
					optionalField: false,
				},
				country: {
					name: 'country',
					value: 'DE',
					pattern: '',
					optionalField: false,
				},
			},
			countries: countries,
			addressType: AddressTypeModel.PERSON,
			postCodeValidation: addressValidationPatterns.postcode,
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
		expect( wrapper.emitted( event )![ 0 ] ).toEqual( [ 'city' ] );
	} );

	it( 'sets the correct postcode regex on country change', async () => {
		const wrapper = mount( Postal, {
				localVue,
				mocks: {
					$t: () => { },
				},
				propsData: newTestProperties( {} ),
			} ),
			event = 'field-changed',
			field = wrapper.find( '#country' );

		field.trigger( 'focus' );
		wrapper.vm.$data.countryInput = '';
		field.trigger( 'blur' );

		await wrapper.vm.$nextTick();
		expect( wrapper.vm.$props.formData.postcode.pattern ).toEqual( addressValidationPatterns.postcode );

		field.trigger( 'focus' );
		wrapper.vm.$data.countryInput = countries[ 0 ].countryFullName;
		field.trigger( 'blur' );

		await wrapper.vm.$nextTick();
		expect( wrapper.vm.$props.formData.postcode.pattern ).toEqual( countries[ 0 ].postCodeValidation );
	} );
} );
