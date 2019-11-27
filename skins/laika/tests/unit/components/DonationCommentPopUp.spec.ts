import { createLocalVue, mount } from '@vue/test-utils';
import { createStore } from '@/store/donation_store';
import DonationCommentPopUp from '@/components/DonationCommentPopUp.vue';
import Vuex from 'vuex';
import Buefy from 'buefy';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );

describe( 'DonationCommentPopUp', () => {
	function getDefaultConfirmationData( isAnonymous: boolean ): any {
		const sampleDonationData = {
			accessToken: 'a839bc8045aba4c8b600bc0477dbbf10',
			amount: 12.35,
			bankTransferCode: 'XW-XLK-M3F-Z',
			id: 1,
			interval: 0,
			optsIntoDonationReceipt: true,
			optsIntoNewsletter: false,
			paymentType: 'UEB',
			updateToken: 'd387cebd6cc05efbd117545492cb0e99',
		};
		if ( !isAnonymous ) {
			return {
				donation: sampleDonationData,
				addressType: addressTypeName( AddressTypeModel.PERSON ),
				address: {
					city: 'Berlin',
					countryCode: 'DE',
					email: 'test@wikimedia.de',
					firstName: 'Tester',
					fullName: 'Prof. Dr. Tester McTest',
					lastName: 'McTest',
					postalCode: '10963',
					salutation: 'Herr',
					streetAddress: 'Tempelhofer Ufer 23-24',
				},
			};
		}
		return {
			donation: sampleDonationData,
			addressType: addressTypeName( AddressTypeModel.ANON ),
			address: {},
		};
	}

	it( 'displays anyonmous comment toggle for private / company donations', () => {
		const wrapper = mount( DonationCommentPopUp, {
			localVue,
			propsData: {
				confirmationData: getDefaultConfirmationData( false ),
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#isAnonymous' ).exists() ).toBeTruthy();
	} );

	it( 'hides anyonmous comment toggle for anonymous donations', () => {
		const wrapper = mount( DonationCommentPopUp, {
			localVue,
			propsData: {
				confirmationData: getDefaultConfirmationData( true ),
			},
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#isAnonymous' ).exists() ).toBeFalsy();
	} );
} );
