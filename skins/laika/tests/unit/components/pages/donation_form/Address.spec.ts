import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import Address from '@/components/pages/donation_form/Address.vue';
import Name from '@/components/shared/Name.vue';
import Postal from '@/components/shared/Postal.vue';
import ReceiptOptOut from '@/components/shared/ReceiptOptOut.vue';
import AddressType from '@/components/pages/donation_form/AddressType.vue';
import Email from '@/components/shared/Email.vue';
import PaymentBankData from '@/components/shared/PaymentBankData.vue';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { createStore } from '@/store/donation_store';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { NS_ADDRESS } from '@/store/namespaces';
import { setAddressField, setReceiptOptOut, setAddressType, setEmail } from '@/store/address/actionTypes';
import { action } from '@/store/util';

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
				countries: [ 'DE' ],
				initialFormValues: '',
			},
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
	} );
	it( 'renders components which are part of the donation address page', () => {
		expect( wrapper.contains( Name ) ).toBe( true );
		expect( wrapper.contains( Postal ) ).toBe( true );
		expect( wrapper.contains( ReceiptOptOut ) ).toBe( true );
		expect( wrapper.contains( AddressType ) ).toBe( true );
		expect( wrapper.contains( Email ) ).toBe( true );
		expect( wrapper.contains( NewsletterOptIn ) ).toBe( true );
	} );

	it( 'renders Bank Data component only if payment is direct debit', () => {
		expect( wrapper.contains( PaymentBankData ) ).toBe( false );
		// Stub payment option direct debit (BEZ) being selected
		const comp = wrapper.vm.$options!.computed;
		if ( typeof comp.isDirectDebit === 'function' ) {
			comp.isDirectDebit = jest.fn( () => true );
			expect( wrapper.contains( PaymentBankData ) ).toBe( true );
		}
	} );

	it( 'does not render postal and receipt opt out if adress type is anonymous', () => {
		wrapper.find( AddressType ).vm.$emit( 'address-type', AddressTypeModel.ANON );
		expect( wrapper.contains( Postal ) ).toBe( false );
		expect( wrapper.contains( ReceiptOptOut ) ).toBe( false );
	} );

	it( 'sets address type in store when it receives address-type event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setAddressType );
		const expectedPayload = AddressTypeModel.ANON;
		wrapper.find( AddressType ).vm.$emit( 'address-type', AddressTypeModel.ANON );
		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'sets address field in store when it receives field-changed event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setAddressField );
		const firstNameValue = 'Vuetiful';
		const postCode = '420';
		wrapper.vm.$data.formData.firstName.value = firstNameValue;
		wrapper.vm.$data.formData.postcode.value = postCode;

		wrapper.find( Name ).vm.$emit( 'field-changed', 'firstName' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, {
			'name': 'firstName',
			'optionalField': false,
			'pattern': '^.+$',
			'value': firstNameValue,
		} );

		wrapper.find( Postal ).vm.$emit( 'field-changed', 'postcode' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, {
			'name': 'postcode',
			'optionalField': false,
			'pattern': '^[0-9]{4,5}$',
			'value': postCode,
		} );
	} );

	it( 'sets receipt opt out preference in store when it receives opted-out event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setReceiptOptOut );
		const expectedPayload = true;
		wrapper.find( ReceiptOptOut ).vm.$emit( 'opted-out', true );
		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'sets email in store when it receives email event', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setEmail );
		const email = 'britney@toxic.com';
		wrapper.find( Email ).vm.$emit( 'email', email );
		expect( store.dispatch ).toBeCalledWith( expectedAction, email );
	} );

} );
