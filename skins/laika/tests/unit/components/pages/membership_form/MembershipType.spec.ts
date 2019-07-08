import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import MembershipType from '@/components/pages/membership_form/MembershipType.vue';
import { createStore } from '@/store/membership_store';
import { action } from '@/store/util';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setMembershipType } from '@/store/membership_address/actionTypes';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import Buefy from 'buefy';

const localVue = createLocalVue();
localVue.use( Buefy );
localVue.use( Vuex );

describe( 'MembershipType.vue', () => {

	it( 'sends selected membership type to the store on change', () => {
		const wrapper = mount( MembershipType, {
			localVue,
			store: createStore(),
			mocks: {
				$t: () => { },
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();
		wrapper.find( '#active' ).trigger( 'click' );
		const expectedAction = action( NS_MEMBERSHIP_ADDRESS, setMembershipType );
		const expectedPayload = MembershipTypeModel.ACTIVE;

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );
} );
