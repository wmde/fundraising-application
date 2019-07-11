import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import MembershipInfo from '@/components/pages/donation_confirmation/MembershipInfo.vue';
import { createStore } from '@/store/donation_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'MembershipInfo', () => {
	it( 'renders access token and donation ID in membership application URL', () => {
		const wrapper = mount( MembershipInfo, {
			localVue,
			propsData: {
				confirmationData: {
					donation: {
						id: 1,
						accessToken: 'a839bc8045aba4c8b600bc0477dbbf10',
					},
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		let href = wrapper.find( '#membership-application-url' ).element.attributes.getNamedItem( 'href' );
		expect( href!.value ).toMatch( 'apply-for-membership?donationId=1&donationAccessToken=a839bc8045aba4c8b600bc0477dbbf10&type=sustaining' );
	} );
} );
