import { createLocalVue, mount } from '@vue/test-utils';
import Vuex from 'vuex';
import SubmitValues from '@/components/pages/membership_form/SubmitValues.vue';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel, membershipTypeName } from '@/view_models/MembershipTypeModel';

const localVue = createLocalVue();
localVue.use( Vuex );

describe( 'SubmitValues.vue', () => {
	let wrapper: any;
	beforeEach( () => {
		wrapper = mount( SubmitValues, {
			localVue,
			store: new Vuex.Store( {
				modules: {
					[ NS_MEMBERSHIP_ADDRESS ]: {
						namespaced: true,
						state: {
							membershipType: MembershipTypeModel.SUSTAINING,
							receiptOptOut: false,
							addressType: AddressTypeModel.PERSON,
							values: {
								firstName: 'Victor',
								lastName: 'van Doom',
								salutation: 'Herr',
								title: 'Dr.',
								street: 'Untere Straße 5',
								postcode: '08114',
								city: 'Haasenstadt',
								country: 'DE',
								email: 'doom@untergang.biz',
								date: '25.01.1948',
							},
						},

					},
					[ NS_BANKDATA ]: {
						namespaced: true,
						state: {
							values: {
								iban: 'DE12500105170648489890',
								bic: 'INGDDEFFXXX',
							},
						},
					},
					[ NS_MEMBERSHIP_FEE ]: {
						namespaced: true,
						state: {
							values: {
								fee: '2349',
								interval: '6',
								type: 'BEZ',
							},
						},
					},
				},
			} ),
		} );
	} );

	it( 'renders input fields', () => {
		expect( wrapper.element ).toMatchSnapshot();
	} );

	it( 'renders the amount as German-Formatted decimal number', () => {
		expect( ( wrapper.find( 'input[name=membership_fee]' ).element as HTMLInputElement ).value ).toBe( '23,49' );
	} );

	it( 'renders the membership type as string', () => {
		expect( ( wrapper.find( 'input[name=membership_type]' ).element as HTMLInputElement ).value ).toBe( membershipTypeName( MembershipTypeModel.SUSTAINING ) );
	} );

	it( 'renders the address type as string', () => {
		expect( ( wrapper.find( 'input[name=adresstyp]' ).element as HTMLInputElement ).value ).toBe( addressTypeName( AddressTypeModel.PERSON ) );
	} );
} );
