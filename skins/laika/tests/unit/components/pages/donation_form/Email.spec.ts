import { mount, createLocalVue } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import Email from '@/components/pages/donation_form/Email.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_ADDRESS } from '@/store/namespaces';
import { setEmail } from '@/store/address/actionTypes';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'Email', () => {

	it( 'shows an error if the entered email has an invalid format', () => {
		const wrapper = mount( Email, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const email = wrapper.find('#email');
		wrapper.setData( { emailValue: 'abc@' } );
		email.trigger( 'blur' );
		const hasError = wrapper.vm.$data.emailHasError;
		expect( hasError ).toBe( true );
	} );

	it( 'shows an error if the email field is still empty on blur', () => {
		const wrapper = mount( Email, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const email = wrapper.find( '#email' );
		wrapper.setData( { emailValue: '' } );
		email.trigger( 'blur' );
		const hasError = wrapper.vm.$data.emailHasError;
		expect( hasError ).toBe( true );
	} );

	it( 'does not show an error on initial render even though the field is empty', () => {
		const wrapper = mount( Email, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const hasError = wrapper.vm.$data.emailHasError;
		expect( hasError ).toBe( false );
	} );

	it( 'sends email to store if it is has valid format', () => {
		const wrapper = mount( Email, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setEmail );
		const email = wrapper.find( '#email' );
		wrapper.setData( { emailValue: 'abc@def.ghi' } );
		email.trigger( 'blur' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, 'abc@def.ghi' );
	} );
} );
