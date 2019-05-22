import { mount, createLocalVue } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import Buefy from 'buefy';
import NewsletterOptIn from '@/components/pages/donation_form/NewsletterOptIn.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_ADDRESS } from '@/store/namespaces';
import { setNewsletterOptIn } from '@/store/address/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'NewsletterOptIn', () => {
	it( 'renders the component with the checkbox unselected', () => {
		const wrapper = mount( NewsletterOptIn, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		expect( wrapper.vm.$data.newsletterOptIn ).toBe( false );
	} );

	it( 'sends opt in preference to store on change', () => {
		const wrapper = mount( NewsletterOptIn, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		const expectedAction = action( NS_ADDRESS, setNewsletterOptIn );
		const checkbox = wrapper.find( '#newsletter' );
		wrapper.setData( { newsletterOptIn: true } );
		checkbox.trigger( 'click' );
		expect( store.dispatch ).toBeCalledWith( expectedAction, true );
	} );
} );
