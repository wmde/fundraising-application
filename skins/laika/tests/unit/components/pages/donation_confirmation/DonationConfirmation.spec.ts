import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Buefy from 'buefy';
import DonationConfirmation from '@/components/pages/DonationConfirmation.vue';
import { createStore } from '@/store/donation_store';
import { FeatureTogglePlugin } from '@/FeatureToggle';

const localVue = createLocalVue();
localVue.use( Vuex );
localVue.use( Buefy );
localVue.use( FeatureTogglePlugin, { activeFeatures: [ 'campaigns.confirmation_page_layout.old_layout' ] } );

const testBankTransferCode = 'XW-XLK-M3F-Z';

function getDefaultConfirmationData(): any {
	return {
		addressType: 'person',
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
		donation: {
			accessToken: 'a839bc8045aba4c8b600bc0477dbbf10',
			amount: 12.35,
			bankTransferCode: testBankTransferCode,
			id: 1,
			interval: 0,
			optsIntoDonationReceipt: true,
			optsIntoNewsletter: false,
			paymentType: 'UEB',
			updateToken: 'd387cebd6cc05efbd117545492cb0e99',
		},
		countries: [
			{
				'countryCode': 'DE',
				'countryFullName': 'Deutschland',
				'isFrequentCountry': true,
				'postCodeValidation': '^[0-9]{5}$',
			},
			{
				'countryCode': 'AT',
				'countryFullName': 'Österreich',
				'isFrequentCountry': true,
				'postCodeValidation': '^[0-9]{4}$',
			},
		],
	};
}

describe( 'DonationConfirmation', () => {
	it( 'displays bank data for bank transaction payments', () => {
		let confirmationData = getDefaultConfirmationData();
		const wrapper = mount( DonationConfirmation, {
			localVue,
			propsData: confirmationData,
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#bank-data' ).html() ).toContain( testBankTransferCode );
	} );

	it( 'does not display the bank data element for other payment methods', () => {
		let confirmationData = getDefaultConfirmationData();
		confirmationData.donation.paymentType = 'PPL';
		const wrapper = mount( DonationConfirmation, {
			localVue,
			propsData: confirmationData,
			store: createStore(),
			mocks: {
				$t: ( key: string ) => key,
			},
		} );

		expect( wrapper.find( '#bank-data' ).exists() ).toBeFalsy();
	} );

} );
