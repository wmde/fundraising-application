import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import Address from '@/components/pages/membership_form/Address.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import AddressType from '@/components/pages/membership_form/AddressType.vue';
import Email from '@/components/shared/Email.vue';
import DateOfBirth from '@/components/pages/membership_form/DateOfBirth.vue';
import { createStore } from '@/store/membership_store';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import {
	setAddressField,
	setReceiptOptOut,
	setAddressType,
	initializeAddress,
} from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';
import countries from '@/../tests/data/countries';
import { Validity } from '@/view_models/Validity';
import { addressValidationPatterns } from '../../../../data/validation';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'Address.vue', () => {
	let wrapper: any;
	beforeEach( () => {
		wrapper = mount( Address, {
			localVue,
			propsData: {
				validateAddressUrl: 'validate-address',
				countries: countries,
				addressValidationPatterns: addressValidationPatterns,
			},
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
	} );
	it( 'renders components which are part of the donation address page', () => {
		expect( wrapper.findComponent( Name ).exists() ).toBe( true );
		expect( wrapper.findComponent( Postal ).exists() ).toBe( true );
		expect( wrapper.findComponent( ReceiptOptOut ).exists() ).toBe( true );
		expect( wrapper.findComponent( AddressType ).exists() ).toBe( true );
		expect( wrapper.findComponent( Email ).exists() ).toBe( true );
		expect( wrapper.findComponent( DateOfBirth ).exists() ).toBe( true );
	} );

	it( 'sets address type in store when it receives address-type event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setAddressType );
		const expectedPayload = AddressTypeModel.PERSON;
		wrapper.findComponent( AddressType ).vm.$emit( 'address-type', AddressTypeModel.PERSON );
		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'sets address field in store when it receives field-changed event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setAddressField );
		const firstNameValue = 'Vuetiful';
		wrapper.vm.$data.formData.firstName.value = firstNameValue;

		wrapper.findComponent( Name ).vm.$emit( 'field-changed', 'firstName' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, {
			'name': 'firstName',
			'optionalField': false,
			'pattern': addressValidationPatterns.firstName,
			'value': firstNameValue,
		} );
	} );

	it( 'sets receipt opt out preference in store when it receives opted-out event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setReceiptOptOut );
		const expectedPayload = true;
		wrapper.findComponent( ReceiptOptOut ).vm.$emit( 'opted-out', true );
		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'sets email in store when it receives email event', () => {
		const store = wrapper.vm.$store;
		const testEmail = 'test@wikimedia.de';
		store.dispatch = jest.fn();
		wrapper.vm.$data.formData.email.value = testEmail;
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setAddressField );
		wrapper.findComponent( Email ).vm.$emit( 'field-changed', 'email' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, {
			'name': 'email',
			'optionalField': false,
			'pattern': '^(.+)@(.+)\\.(.+)$',
			'value': testEmail,
		} );
	} );

	it( 'populates form data and validates if initial data is available', async () => {
		const firstName = 'Testina',
			lastName = 'Testinson',
			title = 'Prof Dr.';
		const store = createStore();
		await store.dispatch( action( NS_MEMBERSHIP_ADDRESS, initializeAddress ), {
			addressType: AddressTypeModel.PERSON,
			fields: [
				{
					name: 'email',
					value: 'playthatfunkymusic@example.com',
					validity: Validity.RESTORED,
				},
				{
					name: 'firstName',
					value: firstName,
					validity: Validity.RESTORED,
				},
				{
					name: 'lastName',
					value: lastName,
					validity: Validity.RESTORED,
				},
				{
					name: 'title',
					value: title,
					validity: Validity.RESTORED,
				},
			],
		} ).then( () => {
			wrapper = mount( Address, {
				localVue,
				propsData: {
					validateAddressUrl: 'validate-address',
					countries: countries,
					addressValidationPatterns: addressValidationPatterns,
				},
				store,
				mocks: {
					$t: () => { },
				},
			} );
		} );
		expect( wrapper.vm.$data.formData.firstName.value ).toBe( firstName );
		expect( wrapper.vm.$data.formData.lastName.value ).toBe( lastName );
		expect( wrapper.vm.$data.formData.title.value ).toBe( title );
		expect( store.state.membership_address.validity.firstName ).not.toBe( Validity.RESTORED );
		expect( store.state.membership_address.validity.lastName ).not.toBe( Validity.RESTORED );
	} );

} );
