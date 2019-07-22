import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import MembershipSummary from '@/components/pages/membership_confirmation/MembershipSummary.vue';
import { createStore } from '@/store/donation_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const privateAddress = {
	applicantType: 'person',
	city: 'Berlin',
	countryCode: 'DE',
	email: 'testperson@wikimedia.de',
	fullName: 'Prof. Dr. Testy MacTest',
	postalCode: '10963',
	salutation: 'Herr',
	streetAddress: 'Tempelhofer Ufer 26',
	title: 'Prof. Dr.',
};

const companyAddress = {
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

const monthlyPayment = {
	id: 1,
	membershipFee: '15.00',
	membershipType: 'sustaining',
	paymentIntervalInMonths: 1,
	paymentType: 'BEZ',
	status: 'status-booked',
	updateToken: '16a9e7a092959b9507e86a0c94dfbb9c',
};

const quarterlyPayment = {
	...monthlyPayment,
	membershipFee: '45.00',
	paymentIntervalInMonths: 3,
};

const yearlyPayment = {
	...monthlyPayment,
	membershipFee: '180.00',
	paymentIntervalInMonths: 12,
};

describe( 'MembershipSummary', () => {
	it( 'renders personal membership confirmation data', () => {
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: {
				address: privateAddress,
				membershipApplication: monthlyPayment,
			},
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		expect( wrapper.find( '.payment-summary' ).text() ).toContain( 'Herr Prof. Dr. Testy MacTest, Tempelhofer Ufer 26, 10963 Berlin' );
	} );

	it( 'renders company membership confirmation data', () => {
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: {
				address: companyAddress,
				membershipApplication: monthlyPayment,
			},
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		expect( wrapper.find( '.payment-summary' ).text() ).toContain( 'Test Company, Teststreet 123, 12345 Company City,' );
	} );

	it( 'renders monthly payments', () => {
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: {
				address: companyAddress,
				membershipApplication: monthlyPayment,
			},
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFee": "15,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFeeYearly": "180,00"' );
	} );

	it( 'renders quarterly payments', () => {
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: {
				address: companyAddress,
				membershipApplication: quarterlyPayment,
			},
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFee": "45,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFeeYearly": "180,00"' );
	} );

	it( 'renders yearly payments', () => {
		const wrapper = mount( MembershipSummary, {
			localVue,
			propsData: {
				address: companyAddress,
				membershipApplication: yearlyPayment,
			},
			store: createStore(),
			mocks: {
				$t: ( key: string, params: Object ) => params,
			},
		} );

		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFee": "180,00"' );
		expect( wrapper.find( '.payment-summary' ).text() ).toContain( '"membershipFeeYearly": "180,00"' );
	} );
} );
