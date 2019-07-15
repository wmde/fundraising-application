import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import MembershipInfo from '@/components/pages/donation_confirmation/SummaryLinks.vue';
import { createStore } from '@/store/donation_store';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

const testAccessToken = 'a839bc8045aba4c8b600bc0477dbbf10';
const testUpdateToken = '16a9e7a092959b9507e86a0c94dfbb9c';
const testId = '123';

describe( 'SummaryLinks', () => {
	it( 'allows donations to be commented if the payment method is not UEB', () => {
		const wrapper = mount( MembershipInfo, {
			localVue,
			propsData: {
				confirmationData: {
					donation: {
						id: testId,
						accessToken: testAccessToken,
						updateToken: testUpdateToken,
						paymentType: 'BEZ',
					},
					urls: {
						cancelDonation: 'cancel-donation?id=' + testId + '&updateToken=' + testUpdateToken,
					},
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		let href = wrapper.find( '#comment-link' ).element.attributes.getNamedItem( 'href' );
		expect( href!.value ).toMatch(
			'/add-comment?donationId=' + testId + '&accessToken=' + testAccessToken + '&updateToken=' + testUpdateToken
		);
	} );

	it( 'prevents donations from being commented if the payment method is UEB', () => {
		const wrapper = mount( MembershipInfo, {
			localVue,
			propsData: {
				confirmationData: {
					donation: {
						id: testId,
						accessToken: testAccessToken,
						updateToken: testUpdateToken,
						paymentType: 'UEB',
					},
					urls: {
						cancelDonation: 'cancel-donation?id=' + testId + '&updateToken=' + testUpdateToken,
					},
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#comment-link' ).exists() ).toBeFalsy();
	} );

	it( 'allows donations to be cancelled if the payment method is BEZ', () => {
		const wrapper = mount( MembershipInfo, {
			localVue,
			propsData: {
				confirmationData: {
					donation: {
						id: testId,
						accessToken: testAccessToken,
						updateToken: testUpdateToken,
						paymentType: 'BEZ',
					},
					urls: {
						cancelDonation: 'cancel-donation',
					},
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#cancel-link' ).exists() ).toBeTruthy();
		expect( wrapper.find( '#cancel-link form' ).element.getAttribute( 'action' ) ).toMatch( 'cancel-donation' );
		let hiddenFormFields = wrapper.findAll( '#cancel-link input' );
		expect( hiddenFormFields.length ).toBe( 2 );
		expect( hiddenFormFields.at( 0 ).element.getAttribute( 'value' ) ).toBe( testId );
		expect( hiddenFormFields.at( 1 ).element.getAttribute( 'value' ) ).toBe( testUpdateToken );
	} );

	it( 'prevents donations from being cancelled if the payment method is not BEZ', () => {
		const wrapper = mount( MembershipInfo, {
			localVue,
			propsData: {
				confirmationData: {
					donation: {
						id: 1,
						accessToken: testAccessToken,
						updateToken: testUpdateToken,
						paymentType: 'UEB',
					},
					urls: {
						cancelDonation: 'cancel-donation',
					},
				},
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#cancel-link' ).exists() ).toBeFalsy();
	} );
} );
