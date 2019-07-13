import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import MembershipSummary from '@/components/pages/membership_confirmation/MembershipSummary.vue';
import { createStore } from '@/store/donation_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

function getDefaultConfirmationData(): any {
	return {
		address: {
			applicantType: 'person',
			city: 'Berlin',
			countryCode: 'DE',
			email: 'testperson@wikimedia.de',
			fullName: 'Prof. Dr. Testy MacTest',
			postalCode: '10963',
			salutation: 'Herr',
			streetAddress: 'Tempelhofer Ufer 26',
			title: 'Prof. Dr.',
		},
		membershipApplication: {
			id: 1,
			membershipFee: '15.00',
			membershipType: 'sustaining',
			paymentIntervalInMonths: 1,
			paymentType: 'BEZ',
			status: 'status-booked',
			updateToken: '16a9e7a092959b9507e86a0c94dfbb9c',
		},
	};
}

describe( 'MembershipSummary', () => {
	it( 'renders personal membership confirmation data', () => {
		let confirmationData = getDefaultConfirmationData();
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: confirmationData,
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		// We cannot test for everything here due to the limitations of the translation module
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFee": "15,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFeeYearly": "180,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( 'Herr Prof. Dr. Testy MacTest, Tempelhofer Ufer 26, 10963 Berlin' );
	} );

	it( 'renders company membership confirmation data', () => {
		let confirmationData = getDefaultConfirmationData();
		confirmationData.address = {
			applicantType: 'firma',
			city: 'Company City',
			countryCode: 'DE',
			email: 'testcompany@wikimedia.de',
			fullName: 'Test Company',
			postalCode: '12345',
			salutation: 'Firma',
			streetAddress: 'Teststreet 123',
			title: '',
		};

		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: confirmationData,
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		// We cannot test for everything here due to the limitations of the translation module
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFee": "15,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFeeYearly": "180,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( 'Test Company, Teststreet 123, 12345 Company City' );
	} );
} );
