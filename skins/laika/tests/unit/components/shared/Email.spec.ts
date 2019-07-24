import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Email from '@/components/shared/Email.vue';
import { createStore } from '@/store/donation_store';
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
				$t: ( key: string ) => key,
			},
			propsData: {
				showError: true,
				formData: {
					email: {
						value: 'notanemail',
					},
				},
			},
		} );
		const errorElement = wrapper.find( '.help.is-danger' );
		expect( errorElement.text() ).toMatch( 'donation_form_email_error' );
	} );
} );
