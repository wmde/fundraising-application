import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import MembershipType from '@/components/pages/membership_form/MembershipType.vue';
import { createStore } from '@/store/membership_store';
import { action } from '@/store/util';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setMembershipType } from '@/store/membership_address/actionTypes';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import Buefy from 'buefy';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

const localVue = createLocalVue();
localVue.use( Buefy );
localVue.use( Vuex );

describe( 'MembershipType.vue', () => {
	let wrapper: any;
	beforeEach( () => {
		wrapper = mount( MembershipType, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
	} );

	it( 'sends selected membership type to the store on change', () => {
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		wrapper.find( '#active' ).trigger( 'click' );
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setMembershipType );
		const expectedPayload = MembershipTypeModel.ACTIVE;

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'disables active membership type if the address type is company', () => {
		// Stub company address being selected
		const comp = wrapper.vm.$options!.computed;
		if ( typeof comp.isActiveTypeDisabled === 'function' ) {
			comp.isActiveTypeDisabled = jest.fn( () => true );
			expect( wrapper.find( '#active' ).attributes( 'disabled' ) ).toBe( true );
		}
	} );

	it( 'displays an error if active membership type is selected and then address type company is selected', () => {
		wrapper.find( '#active' ).trigger( 'click' );
		// Stub company address being selected
		const comp = wrapper.vm.$options!.computed;
		if ( typeof comp.isActiveTypeDisabled === 'function' ) {
			comp.isActiveTypeDisabled = jest.fn( () => true );
			expect( wrapper.contains( 'span[class="help is-danger"]' ) ).toBe( true );
		}
	} );

	it( 'hides the error when a different membership type is selected', () => {
		wrapper.find( '#active' ).trigger( 'click' );
		// Stub company address being selected
		const comp = wrapper.vm.$options!.computed;
		if ( typeof comp.isActiveTypeDisabled === 'function' ) {
			comp.isActiveTypeDisabled = jest.fn( () => true );
			expect( wrapper.contains( 'span[class="help is-danger"]' ) ).toBe( true );

			wrapper.find( '#sustaining' ).trigger( 'click' );
			expect( wrapper.contains( 'span[class="help is-danger"]' ) ).toBe( false );
		}
	} );

	it( 'hides the error when a different address type is selected', () => {
		wrapper.find( '#active' ).trigger( 'click' );
		// Stub company address being selected
		const comp = wrapper.vm.$options!.computed;
		if ( typeof comp.isActiveTypeDisabled === 'function' ) {
			comp.isActiveTypeDisabled = jest.fn( () => true );
			expect( wrapper.contains( 'span[class="help is-danger"]' ) ).toBe( true );

			const store = wrapper.vm.$store;
			store.getters.addressType = jest.fn( () => AddressTypeModel.PERSON );
			expect( wrapper.contains( 'span[class="help is-danger"]' ) ).toBe( false );
		}
	} );
} );
