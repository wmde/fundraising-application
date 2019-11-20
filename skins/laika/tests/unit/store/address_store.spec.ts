import { getters } from '@/store/address/getters';
import { actions } from '@/store/address/actions';
import { mutations } from '@/store/address/mutations';
import {
	VALIDATE_INPUT,
	MARK_EMPTY_FIELDS_INVALID,
	BEGIN_ADDRESS_VALIDATION,
	FINISH_ADDRESS_VALIDATION,
	BEGIN_EMAIL_VALIDATION,
	FINISH_EMAIL_VALIDATION,
	SET_ADDRESS_FIELD,
	SET_ADDRESS_TYPE,
	SET_NEWSLETTER_OPTIN,
	SET_RECEIPT_OPTOUT,
} from '@/store/address/mutationTypes';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { AddressState } from '@/view_models/Address';
import { Validity } from '@/view_models/Validity';
import { REQUIRED_FIELDS } from '@/store/address/constants';
import moxios from 'moxios';

function newMinimalStore( overrides: Object ): AddressState {
	return Object.assign(
		{
			serverSideValidationCount: 0,
			addressType: AddressTypeModel.PERSON,
			newsletterOptIn: false,
			receiptOptOut: false,
			requiredFields: REQUIRED_FIELDS,
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
			},
			validity: {
				salutation: Validity.INCOMPLETE,
				title: Validity.INCOMPLETE,
				firstName: Validity.INCOMPLETE,
				lastName: Validity.INCOMPLETE,
				companyName: Validity.INCOMPLETE,
				street: Validity.INCOMPLETE,
				postcode: Validity.INCOMPLETE,
				city: Validity.INCOMPLETE,
				country: Validity.VALID,
				email: Validity.INCOMPLETE,
				addressType: Validity.VALID,
			},
		},
		overrides
	);
}

describe( 'Address', () => {

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

	describe( 'Getters/addressTypeIsNotAnon', () => {

		it( 'returns true for address types person and company', () => {
			const addressType: AddressTypeModel = AddressTypeModel.COMPANY;
			expect( getters.addressTypeIsNotAnon(
				newMinimalStore( {
					addressType: AddressTypeModel.COMPANY,
				} ),
				{ addressType },
				null,
				null
			) ).toBe( true );
		} );

		it( 'returns false for anon users', () => {
			const addressType: AddressTypeModel = AddressTypeModel.ANON;
			expect( getters.addressTypeIsNotAnon(
				newMinimalStore( {
					addressType: AddressTypeModel.ANON,
				} ),
				{ addressType },
				null,
				null
			) ).toBe( false );
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
			action( { commit, getters }, type );
			expect( commit ).toBeCalledWith(
				'SET_ADDRESS_TYPE',
				type
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

	describe( 'Actions/setNewsletterOptIn', () => {
		it( 'commits to mutation [SET_NEWSLETTER_OPTIN] with the entered choice', () => {
			const commit = jest.fn(),
				action = actions.setNewsletterOptIn as any,
				choice = true;
			action( { commit }, choice );
			expect( commit ).toBeCalledWith(
				'SET_NEWSLETTER_OPTIN',
				choice
			);
		} );
	} );

	describe( 'Mutations/VALIDATE_INPUT', () => {
		it( 'sets validity to valid for optional unfilled fields', () => {
			const inputField = {
					name: 'title',
					value: '',
					pattern: '',
					optionalField: true,
				},
				store = newMinimalStore( {} );
			mutations.VALIDATE_INPUT( store, inputField );
			expect( store.validity.title ).toStrictEqual( Validity.VALID );
		} );

		it( 'sets validity to valid for correctly filled fields', () => {
			const inputField = {
					name: 'firstName',
					value: 'Testina',
					pattern: '^.+$',
					optionalField: false,
				},
				store = newMinimalStore( {} );
			mutations.VALIDATE_INPUT( store, inputField );
			expect( store.validity.firstName ).toStrictEqual( Validity.VALID );
		} );

		it( 'sets validity to invalid for incorrectly filled fields', () => {
			const inputField = {
					name: 'postcode',
					value: '666666',
					pattern: '^[0-9]{4,5}$',
					optionalField: false,
				},
				store = newMinimalStore( {} );
			mutations.VALIDATE_INPUT( store, inputField );
			expect( store.validity.postcode ).toStrictEqual( Validity.INVALID );
		} );
	} );

	describe( 'Mutations/MARK_EMPTY_FIELD_INVALID', () => {
		it( 'sets validity to invalid for empty mandatory fields', () => {
			const fakeFormData = {
					firstName: {
						name: 'firstName',
						value: '',
						pattern: '^.+$',
						optionalField: false,
					},
					lastName: {
						name: 'lastName',
						value: '',
						pattern: '^.+$',
						optionalField: false,
					},
				},
				store = newMinimalStore( {} );
			expect( store.validity.firstName ).toStrictEqual( Validity.INCOMPLETE );
			expect( store.validity.lastName ).toStrictEqual( Validity.INCOMPLETE );
			mutations.MARK_EMPTY_FIELDS_INVALID( store, fakeFormData );
			expect( store.validity.firstName ).toStrictEqual( Validity.INVALID );
			expect( store.validity.lastName ).toStrictEqual( Validity.INVALID );
		} );
	} );

	describe( 'Mutations/MARK_EMPTY_FIELDS_INVALID', () => {
		it( 'sets validity to invalid for empty mandatory fields', () => {
			const fakeFormData = {
					firstName: {
						name: 'firstName',
						value: '',
						pattern: '^.+$',
						optionalField: false,
					},
					lastName: {
						name: 'lastName',
						value: '',
						pattern: '^.+$',
						optionalField: false,
					},
				},
				store = newMinimalStore( {} );
			expect( store.validity.firstName ).toStrictEqual( Validity.INCOMPLETE );
			expect( store.validity.lastName ).toStrictEqual( Validity.INCOMPLETE );
			mutations.MARK_EMPTY_FIELDS_INVALID( store, fakeFormData );
			expect( store.validity.firstName ).toStrictEqual( Validity.INVALID );
			expect( store.validity.lastName ).toStrictEqual( Validity.INVALID );
		} );
	} );

	describe( 'Mutations/BEGIN_ADDRESS_VALIDATION', () => {
		it( 'increases validation counter', () => {
			const store = newMinimalStore( {} );
			mutations.BEGIN_ADDRESS_VALIDATION( store, null );
			expect( store.serverSideValidationCount ).toBe( 1 );
		} );
	} );

	describe( 'Mutations/FINISH_ADDRESS_VALIDATION', () => {
		it( 'sets validation counter to 0 if there are no errors after the server responds', () => {
			const store = newMinimalStore( { serverSideValidationCount: 1 } ),
				resp = {
					status: 'OK',
					messages: {},
				};
			mutations.FINISH_ADDRESS_VALIDATION( store, resp );
			expect( store.serverSideValidationCount ).toBe( 0 );
		} );

		it( 'sets validity to invalid for the appropriate form fields according to the response from the server', () => {
			const store = newMinimalStore( {} ),
				resp = {
					status: 'ERR',
					messages: {
						postcode: 'error',
						street: 'error',
					},
				};
			expect( store.validity.postcode ).toStrictEqual( Validity.INCOMPLETE );
			expect( store.validity.street ).toStrictEqual( Validity.INCOMPLETE );

			mutations.FINISH_ADDRESS_VALIDATION( store, resp );

			expect( store.validity.postcode ).toStrictEqual( Validity.INVALID );
			expect( store.validity.street ).toStrictEqual( Validity.INVALID );
		} );

	} );

	describe( 'Mutations/BEGIN_EMAIL_VALIDATION', () => {
		it( 'increases validation counter', () => {
			const store = newMinimalStore( {} );
			mutations.BEGIN_EMAIL_VALIDATION( store, null );
			expect( store.serverSideValidationCount ).toBe( 1 );
		} );
	} );

	describe( 'Mutations/FINISH_EMAIL_VALIDATION', () => {
		it( 'sets validation counter to 0 if there are no errors after the server responds', () => {
			const store = newMinimalStore( { serverSideValidationCount: 1 } ),
				resp = {
					status: 'OK',
					messages: {},
				};
			mutations.FINISH_EMAIL_VALIDATION( store, resp );
			expect( store.serverSideValidationCount ).toBe( 0 );
		} );

		it( 'sets validity to invalid for the appropriate form fields according to the response from the server', () => {
			const store = newMinimalStore( {} ),
				resp = {
					status: 'ERR',
					messages: {
						postcode: 'error',
						street: 'error',
					},
				};
			expect( store.validity.email ).toStrictEqual( Validity.INCOMPLETE );

			mutations.FINISH_EMAIL_VALIDATION( store, resp );

			expect( store.validity.email ).toStrictEqual( Validity.INVALID );
		} );

	} );

	describe( 'Mutations/SET_ADDRESS_TYPE', () => {
		it( 'sets address type', () => {
			const store = newMinimalStore( {} );
			mutations.SET_ADDRESS_TYPE( store, AddressTypeModel.COMPANY );
			expect( store.addressType ).toBe( AddressTypeModel.COMPANY );
		} );
	} );

	describe( 'Mutations/SET_ADDRESS_FIELDS', () => {
		it( 'sets address fields values in store', () => {
			const store = newMinimalStore( {} );
			const fields = {
				firstName: {
					name: 'firstName',
					value: 'foo bar',
					pattern: '^.+$',
					optionalField: false,
				},
				lastName: {
					name: 'lastName',
					value: 'should be forbidden',
					pattern: '^.+$',
					optionalField: false,
				},
				street: {
					name: 'street',
					value: 'because it makes no sense',
					pattern: '^.+$',
					optionalField: false,
				},
			};
			mutations.SET_ADDRESS_FIELDS( store, fields );
			expect( store.values.firstName ).toBe( 'foo bar' );
			expect( store.values.lastName ).toBe( 'should be forbidden' );
			expect( store.values.street ).toBe( 'because it makes no sense' );
		} );
	} );

	describe( 'Mutations/SET_ADDRESS_FIELD', () => {
		it( 'sets field value in store', () => {
			const store = newMinimalStore( {} );
			const field = {
				name: 'firstName',
				value: 'Amazing',
				pattern: '^.+$',
				optionalField: false,
			};
			mutations.SET_ADDRESS_FIELD( store, field );
			expect( store.values.firstName ).toBe( 'Amazing' );
		} );
	} );

	describe( 'Mutations/SET_RECEIPT_OPTOUT', () => {
		it( 'sets receipt opt out choice', () => {
			const store = newMinimalStore( {} );
			const choice = true;
			mutations.SET_RECEIPT_OPTOUT( store, choice );
			expect( store.receiptOptOut ).toBe( choice );
		} );
	} );

	describe( 'Mutations/SET_NEWSLETTER_OPTIN', () => {
		it( 'sets receipt opt out choice', () => {
			const store = newMinimalStore( {} );
			const choice = true;
			mutations.SET_NEWSLETTER_OPTIN( store, choice );
			expect( store.newsletterOptIn ).toBe( choice );
		} );
	} );

} );
