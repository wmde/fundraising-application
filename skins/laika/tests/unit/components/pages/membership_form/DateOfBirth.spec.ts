import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import DateOfBirth from '@/components/pages/membership_form/DateOfBirth.vue';
import { createStore } from '@/store/membership_store';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setDate } from '@/store/membership_address/actionTypes';

const localVue = createLocalVue();
localVue.use( Buefy );
localVue.use( Vuex );

describe( 'DateOfBirth.vue', () => {

	it( 'does not show an error on inital render', () => {
		const wrapper = mount( DateOfBirth, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		expect( wrapper.vm.$data.dateHasError ).toBe( false );
	} );

	it( 'shows an error when the entered date is the wrong format', () => {
		const wrapper = mount( DateOfBirth, {
				localVue,
				store: createStore(),
				mocks: {
					$t: () => { },
				},
			} ),
			birthDate = wrapper.find( '#birthDate' );

		wrapper.setData( { date: '10-04-1975' } );
		birthDate.trigger( 'blur' );
		expect( wrapper.vm.$data.dateHasError ).toBe( true );

		wrapper.setData( { date: '10/04/1975' } );
		birthDate.trigger( 'blur' );
		expect( wrapper.vm.$data.dateHasError ).toBe( true );

		wrapper.setData( { date: '00.04.1975' } );
		birthDate.trigger( 'blur' );
		expect( wrapper.vm.$data.dateHasError ).toBe( true );

		wrapper.setData( { date: '10.13.1975' } );
		birthDate.trigger( 'blur' );
		expect( wrapper.vm.$data.dateHasError ).toBe( true );

		wrapper.setData( { date: '10.04.0042' } );
		birthDate.trigger( 'blur' );
		expect( wrapper.vm.$data.dateHasError ).toBe( true );
	} );

	it( 'sends the entered birth date to the store when it is correct', () => {
		const wrapper = mount( DateOfBirth, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn().mockResolvedValue( null );
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setDate );
		const birthDate = wrapper.find( '#birthDate' );

		wrapper.setData( { date: '09.11.1989' } );
		birthDate.trigger( 'blur' );
		let expectedPayload = '09.11.1989';
		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );

		wrapper.setData( { date: '09/11/1989' } );
		birthDate.trigger( 'blur' );
		expectedPayload = '09/11/1989';
		expect( store.dispatch ).not.toBeCalledWith( expectedAction, expectedPayload );
	} );
} );
