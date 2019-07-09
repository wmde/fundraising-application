import { getters } from '@/store/membership_address/getters';
import { actions } from '@/store/membership_address/actions';
import { mutations } from '@/store/membership_address/mutations';
import {
	validateAddress,
	setAddressType,
	setEmail,
	setAddressField,
	setReceiptOptOut,
	setDate,
	setMembershipType,
} from '@/store/membership_address/actionTypes';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELDS_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	SET_ADDRESS_TYPE,
	SET_ADDRESS_FIELD,
	SET_ADDRESS_FIELDS,
	SET_EMAIL,
	SET_DATE,
	SET_RECEIPT_OPTOUT,
	SET_MEMBERSHIP_TYPE,
} from '@/store/membership_address/mutationTypes';
import { AddressTypeModel, addressTypeName } from '@/view_models/AddressTypeModel';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import { MembershipAddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { REQUIRED_FIELDS } from '@/store/address/constants';
import each from 'jest-each';
import moxios from 'moxios';

function newMinimalStore( overrides: Object ): MembershipAddressState {
	return Object.assign(
		{
			isValidating: false,
			addressType: AddressTypeModel.PERSON,
			membershipType: MembershipTypeModel.SUSTAINING,
			receiptOptOut: false,
			values: {
				salutation: '',
				title: '',
				firstName: '',
				lastName: '',
				companyName: '',
				street: '',
				postcode: '',
				city: '',
				country: 'DE',
				email: '',
				date: '',
			},
			validity: {
				salutation: Validity.INCOMPLETE,
				title: Validity.VALID,
				firstName: Validity.INCOMPLETE,
				lastName: Validity.INCOMPLETE,
				companyName: Validity.INCOMPLETE,
				street: Validity.INCOMPLETE,
				postcode: Validity.INCOMPLETE,
				city: Validity.INCOMPLETE,
				country: Validity.VALID,
				email: Validity.INCOMPLETE,
				date: Validity.VALID,
				addressType: Validity.VALID,
			},
		},
		overrides
	);
}

describe( 'MembershipAddress', () => {

	describe( 'Getters/invalidFields', () => {
		const requiredFields = REQUIRED_FIELDS[ AddressTypeModel.PERSON ];

		it( 'does not return unrequired fields as invalid when they are not set', () => {
			expect( getters.invalidFields(
				newMinimalStore( {
					addressType: AddressTypeModel.PERSON,
				} ),
				null,
				null,
				null
			) ).toStrictEqual( requiredFields );
		} );

		it( 'returns an array of all invalid and incomplete fields', () => {
			// remove email and city because they are VALID
			var expectedInvalidFields = requiredFields.filter( e => e !== 'email' && e !== 'city' );
			expect( getters.invalidFields(
				newMinimalStore( {
					validity: {
						street: Validity.INVALID,
						postcode: Validity.INVALID,
						email: Validity.VALID,
						city: Validity.VALID,
					},
				} ),
				getters,
				null,
				null
			) ).toStrictEqual( expectedInvalidFields );
		} );
	} );

	describe( 'Getters/requiredFieldsAreValid', () => {

		it( 'returns true when all required fields are correctly filled', () => {
			const invalidFields: Array<String> = [];
			expect( getters.requiredFieldsAreValid(
				newMinimalStore( {} ),
				{ invalidFields },
				null,
				null
			) ).toBe( true );
		} );

		it( 'returns false if there are wrongly filled required fields', () => {
			const invalidFields: Array<String> = [ 'street', 'postcode' ];
			expect( getters.requiredFieldsAreValid(
				newMinimalStore( {} ),
				{ invalidFields },
				null,
				null
			) ).toBe( false );
		} );
	} );

	describe( 'Getters/addressType', () => {

		it( 'returns address type from the store', () => {
			expect( getters.addressType(
				newMinimalStore( {
					addressType: AddressTypeModel.COMPANY,
				} ),
				null,
				null,
				null
			) ).toBe( AddressTypeModel.COMPANY );
		} );
	} );

	describe( 'Getters/membershipType', () => {

		it( 'returns membership type from the store', () => {
			expect( getters.membershipType(
				newMinimalStore( {
					membershipType: MembershipTypeModel.ACTIVE,
				} ),
				null,
				null,
				null
			) ).toBe( MembershipTypeModel.ACTIVE );
		} );
	} );

	describe( 'Getters/fullName', () => {

		it( 'returns company name when address type is company', () => {
			expect( getters.fullName(
				newMinimalStore( {
					addressType: AddressTypeModel.COMPANY,
					values: {
						companyName: 'Testmedia Deutschland',
					},
				} ),
				null,
				null,
				null
			) ).toBe( 'Testmedia Deutschland' );
		} );

		it( 'returns full name and title when address type is person', () => {
			expect( getters.fullName(
				newMinimalStore( {
					addressType: AddressTypeModel.PERSON,
					values: {
						firstName: 'Testina',
						lastName: 'Testingson',
						title: 'Prof. Dr.',
					},
				} ),
				null,
				null,
				null
			) ).toBe( 'Prof. Dr. Testina Testingson' );
		} );

		it( 'returns only first and last name when title is not chosen', () => {
			expect( getters.fullName(
				newMinimalStore( {
					addressType: AddressTypeModel.PERSON,
					values: {
						firstName: 'Testina',
						lastName: 'Testingson',
						title: '',
					},
				} ),
				null,
				null,
				null
			) ).toBe( 'Testina Testingson' );
		} );
	} );

	describe( 'Actions/setAddressField', () => {
		it( 'commits to mutation [SET_ADDRESS_FIELD] and [VALIDATE_INPUT] with the correct field', () => {
			const commit = jest.fn(),
				action = actions.setAddressField as any,
				field = {
					name: 'postcode',
					value: '',
					pattern: '^[0-9]{4,5}$',
					optionalField: false,
				};
			action( { commit }, field );
			expect( commit ).toBeCalledWith(
				'SET_ADDRESS_FIELD',
				field
			);
			expect( commit ).toBeCalledWith(
				'VALIDATE_INPUT',
				field
			);
		} );
	} );

	describe( 'Actions/validateAddress', () => {
		beforeEach( function () {
			moxios.install();
		} );

		afterEach( function () {
			moxios.uninstall();
		} );
		it( 'commits to mutation [MARK_EMPTY_FIELDS_INVALID] and [BEGIN_ADDRESS_VALIDATION]', () => {
			const context = {
					commit: jest.fn(),
					getters: {
						requiredFieldsAreValid: true,
					},
					state: newMinimalStore( {
						addressType: AddressTypeModel.PERSON,
						values: {
							salutation: 'Mrs',
							title: 'Pro. Dr.',
							firstName: 'Testina',
							lastName: 'Testington',
							street: 'Testenhofen Ufer 23-24',
							postcode: '12345',
							city: 'Testlin',
							country: 'DE',
							email: 'test@testmedia.de',
							date: '01.01.1942',
						},
					} ),
				},
				validationUrl = '/check-address',
				action = actions.validateAddress as any;
			action( context, validationUrl );
			expect( context.commit ).toBeCalledWith(
				'MARK_EMPTY_FIELDS_INVALID'
			);
			expect( context.commit ).toBeCalledWith(
				'BEGIN_ADDRESS_VALIDATION'
			);
		} );

		it( 'sends post request for validation when required fields are valid and commits to mutation [FINISH_ADDRESS_VALIDATION]', ( done ) => {
			const context = {
					commit: jest.fn(),
					getters: {
						requiredFieldsAreValid: true,
					},
					state: newMinimalStore( {
						addressType: AddressTypeModel.PERSON,
						values: {
							salutation: 'Mrs',
							title: 'Pro. Dr.',
							firstName: 'Testina',
							lastName: 'Testington',
							street: 'Testenhofen Ufer 23-24',
							postcode: '12345',
							city: 'Testlin',
							country: 'DE',
							email: 'test@testmedia.de',
							date: '01.01.1942',
						},
					} ),
				},
				validationUrl = '/check-address',
				action = actions.validateAddress as any;

			action( context, validationUrl );
			moxios.wait( function () {
				let request = moxios.requests.mostRecent();
				request.respondWith( {
					status: 200,
					response: {
						status: 'OK',
					} as any,
				} ).then( function () {
					expect( context.commit ).toHaveBeenCalledWith( 'FINISH_ADDRESS_VALIDATION', {
						status: 'OK',
					} );
					done();
				} );
			} );
		} );

		it( 'does not send a post request when required fields are invalid and returns an error', ( done ) => {
			const context = {
					commit: jest.fn(),
					getters: {
						requiredFieldsAreValid: false,
					},
					state: newMinimalStore( {
						addressType: AddressTypeModel.PERSON,
						values: {
							salutation: '',
							title: '',
							firstName: '',
							lastName: '',
							street: '',
							postcode: 'I AM DEFINITELY INVALID',
							city: '',
							country: '',
							email: '',
							date: '',
						},
					} ),
				},
				validationUrl = '/check-address',
				action = actions.validateAddress as any;
			action( context, validationUrl ).then( function ( resp: any ) {
				expect( resp ).toStrictEqual( { status: 'ERR', messages: [] } );
				done();
			} );
		} );
	} );

	describe( 'Actions/setAddressType', () => {
		it( 'commits to mutation [SET_ADDRESS_TYPE] with the chosen type', () => {
			const commit = jest.fn(),
				action = actions.setAddressType as any,
				type = AddressTypeModel.COMPANY;
			action( { commit }, type );
			expect( commit ).toBeCalledWith(
				'SET_ADDRESS_TYPE',
				type
			);
		} );
	} );

	describe( 'Actions/setEmail', () => {
		it( 'commits to mutation [SET_EMAIL] with the entered email', () => {
			const commit = jest.fn(),
				action = actions.setEmail as any,
				email = 'email@email.com';
			action( { commit }, email );
			expect( commit ).toBeCalledWith(
				'SET_EMAIL',
				email
			);
		} );
	} );

	describe( 'Actions/setDate', () => {
		it( 'commits to mutation [SET_DATE] with the entered birth date', () => {
			const commit = jest.fn(),
				action = actions.setDate as any,
				date = '19.19.1919';
			action( { commit }, date );
			expect( commit ).toBeCalledWith(
				'SET_DATE',
				date
			);
		} );
	} );

	describe( 'Actions/setReceiptOptOut', () => {
		it( 'commits to mutation [SET_RECEIPT_OPTOUT] with the entered choice', () => {
			const commit = jest.fn(),
				action = actions.setReceiptOptOut as any,
				choice = true;
			action( { commit }, choice );
			expect( commit ).toBeCalledWith(
				'SET_RECEIPT_OPTOUT',
				choice
			);
		} );
	} );

	describe( 'Actions/setMembershipType', () => {
		it( 'commits to mutation [SET_MEMBERSHIP_TYPE] with the chosen membership type', () => {
			const commit = jest.fn(),
				action = actions.setMembershipType as any,
				choice = MembershipTypeModel.ACTIVE;
			action( { commit }, choice );
			expect( commit ).toBeCalledWith(
				'SET_MEMBERSHIP_TYPE',
				choice
			);
		} );
	} );

} );
