import { getters, actions, mutations } from '../store'
import { AddressState, Validity, ValidationResult } from '../types';

function newMinimalStore( overrides: Object ): AddressState {
	return Object.assign(
		{
			isValidating: false,
			form: {
				salutation: Validity.INCOMPLETE,
				title: Validity.INCOMPLETE,
				firstName: Validity.INCOMPLETE,
				lastName: Validity.INCOMPLETE,
				companyName: Validity.INCOMPLETE,
				street: Validity.INCOMPLETE,
				postcode: Validity.INCOMPLETE,
				city: Validity.INCOMPLETE,
				country: Validity.VALID,
				addressType: Validity.VALID
			}
		},
		overrides
	);
}

describe( 'Store', () => {

	describe( 'Getters/invalidFields', () => {
		it( 'returns no invalid fields on initialization', () => {
			expect( getters.invalidFields(
				newMinimalStore( {} ),
				null,
				null,
				null
				) ).toStrictEqual( [] );
		} );
		it('returns a list of the invalid fields', () => {
			const state = {
				form: {
					title: Validity.INVALID,
					street: Validity.INVALID,
					city: Validity.INVALID
				}
			}
			expect( getters.invalidFields( 
				newMinimalStore( state ),
				null,
				null,
				null
				) ).toStrictEqual( ['title', 'street', 'city'] );
		});
		
	} );
	
	describe( 'Getters/allFieldsAreValid', () => {
		it( 'asserts all fields are valid on initialization', () => {
			const invalidFields: Array<String> = [];
			expect( getters.allFieldsAreValid(
				newMinimalStore( {} ),
				{ invalidFields },
				null,
				null
				) ).toBe( true );
		} );
		it( 'asserts not all fields are valid when there are invalid fields', () => {
			const state = {
				form: {
					title: Validity.INVALID,
					street: Validity.INVALID,
					city: Validity.INVALID
				}
			}
			const invalidFields: Array<String> = ['title', 'street', 'city'];
			expect( getters.allFieldsAreValid(
				newMinimalStore( { state } ),
				{ invalidFields },
				null,
				null
				) ).toBe( false );
		} );
	} );

	describe( 'Actions/validateInput', () => {
		it( 'commits to mutation [VALIDATE_INPUT] with the correct field', () => {
			const commit = jest.fn();
			const action = actions[ 'validateInput' ] as any;
			action( { commit }, 'postcode' );
			expect( commit ).toBeCalledWith(
				'VALIDATE_INPUT',
				'postcode'
			)
		} )
	} );

	describe( 'Actions/storeAddressFields', () => {
		const fakeFormData = {
			salutation: {
				name: 'salutation',
				value: '',
				pattern: '^(Herr|Frau)$',
				optionalField: false
			},
			title: {
				name: 'title',
				value: '',
				pattern: '',
				optionalField: false
			},
			companyName: {
				name: 'companyName',
				value: '',
				pattern: '^.+$',
				optionalField: true
			},
			firstName: {
				name: 'firstName',
				value: '',
				pattern: '^.+$',
				optionalField: false
			},
			lastName: {
				name: 'lastName',
				value: '',
				pattern: '^.+$',
				optionalField: false
			},
			street: {
				name: 'street',
				value: '',
				pattern: '^.+$',
				optionalField: false
			},
			city: {
				name: 'city',
				value: '',
				pattern: '^.+$',
				optionalField: false
			},
			postcode: {
				name: 'postcode',
				value: '',
				pattern: '[0-9]{4,5}$',
				optionalField: false
			},
			country: {
				name: 'country',
				value: 'DE',
				pattern: '',
				optionalField: false
			},
			addressType: {
				name: 'addressType',
				value: 'person',
				pattern: '',
				optionalField: false
			}
		},
		 payload = {
			transport: {
				postData: jest.fn( function( validationResult ) { return Promise.resolve() } )
			},
			validateAddressURL: '',
			formData: fakeFormData
		};

		it( 'commits to mutation [MARK_EMPTY_FIELD_INVALID] with form data', () => {
			const action = actions[ 'storeAddressFields' ] as any,
			context = {
				commit: jest.fn(),
				getters: {
					allFieldsAreValid: false
				}
			};
			action( context, payload );
			expect( context.commit ).toBeCalledWith(
				'MARK_EMPTY_FIELD_INVALID',
				fakeFormData
			);
		} );

		it( 'does not start address validation if there are invalid fields', () => {
			const action = actions[ 'storeAddressFields' ] as any,
			context = {
				commit: jest.fn(),
				getters: {
					allFieldsAreValid: false
				}
			},
			resp = action( context, payload );
			expect( resp ).toEqual( Promise.resolve() );
		} );

		it( 'commits [BEGIN_ADDRESS_VALIDATION] when all fields are valid', () => {
			const action = actions[ 'storeAddressFields' ] as any,
			context = {
				commit: jest.fn(),
				getters: {
					allFieldsAreValid: true
				}
			};
			action( context, payload );
			expect( context.commit ).toBeCalledWith(
				'BEGIN_ADDRESS_VALIDATION'
			);
		} );

		it( 'makes a post request with the correct form data', () => {
			const action = actions[ 'storeAddressFields' ] as any,
			context = {
				commit: jest.fn(),
				getters: {
					allFieldsAreValid: true
				}
			},
			postDataResp = {
				'addressType': 'person',
				'city': '',
				'companyName': '',
				'country': 'DE',
				'firstName': '',
				'lastName': '',
				'postcode': '',
				'salutation': '',
				'street': '',
				'title': '',
			};
			action( context, payload );
			expect( payload.transport.postData ).toBeCalledWith(
				payload.validateAddressURL,
				postDataResp
			);
		} );

		it( 'commits [FINISH_ADDRESS_VALIDATION] after respone from the post request is received', ( done ) => {
			const action = actions[ 'storeAddressFields' ] as any,
			context = {
				commit: jest.fn(),
				getters: {
					allFieldsAreValid: true
				}
			},
			resp = {
				status: 'ERR',
				messages: {
					title: 'error',
					addressType: 'error'
				}
			};
			action( context, payload ).then( ( resp: ValidationResult ) => {
				expect( context.commit ).toBeCalledWith(
					'FINISH_ADDRESS_VALIDATION',
					resp
				);
				done();
			} );
		} );
	});

	describe( 'Mutations/VALIDATE_INPUT', () => {
		it( 'sets validity to incomplete for empty optional fields', () => {
			const inputField = {
				name: 'title',
				value: '',
				pattern: '',
				optionalField: true
			},
			store = newMinimalStore( {} );
			mutations['VALIDATE_INPUT'](store, inputField);
			expect( store.form.title ).toStrictEqual( Validity.INCOMPLETE );
		} );

		it( 'sets validity to valid for correctly filled fields', () => {
			const inputField = {
				name: 'firstName',
				value: 'Testina',
				pattern: '^.+$',
				optionalField: false
			},
			store = newMinimalStore( {} );
			mutations['VALIDATE_INPUT'](store, inputField);
			expect( store.form.firstName ).toStrictEqual( Validity.VALID );
		} );

		it( 'sets validity to invalid for incorrectly filled fields', () => {
			const inputField = {
				name: 'postcode',
				value: '666666',
				pattern: '^[0-9]{4,5}$',
				optionalField: false
			},
			store = newMinimalStore( {} );
			mutations['VALIDATE_INPUT'](store, inputField);
			expect( store.form.postcode ).toStrictEqual( Validity.INVALID );
		} );
	} );

	describe( 'Mutations/MARK_EMPTY_FIELD_INVALID', () => {
		it( 'sets validity to invalid for empty mandatory fields', () => {
			const fakeFormData = {
				firstName: {
					name: 'firstName',
					value: '',
					pattern: '^.+$',
					optionalField: false
				},
				lastName: {
					name: 'lastName',
					value: '',
					pattern: '^.+$',
					optionalField: false
				}
			},
			store = newMinimalStore( {} );
			expect( store.form.firstName ).toStrictEqual( Validity.INCOMPLETE );
			expect( store.form.lastName ).toStrictEqual( Validity.INCOMPLETE );
			mutations['MARK_EMPTY_FIELD_INVALID'](store, fakeFormData);
			expect( store.form.firstName ).toStrictEqual( Validity.INVALID );
			expect( store.form.lastName ).toStrictEqual( Validity.INVALID );
		} )
	} );

	describe( 'Mutations/BEGIN_ADDRESS_VALIDATION', () => {
		it( 'sets validation flag to true', () => {
			const store = newMinimalStore( {} );
			mutations['BEGIN_ADDRESS_VALIDATION']( store, null );
			expect( store.isValidating ).toBe( true );
		} )
	} );

	describe( 'Mutations/FINISH_ADDRESS_VALIDATION', () => {
		it( 'sets validation flag to false if there are no errors', () => {
			const store = newMinimalStore( {} ),
			resp = {
				status: 'OK',
				messages: {}
			};
			mutations['FINISH_ADDRESS_VALIDATION']( store, resp );
			expect( store.isValidating ).toBe( false );
		} );

		it( 'sets validity to invalid for the appropriate form fields according to the response of the post request', () => {
			const store = newMinimalStore( {} ),
			resp = {
				status: 'ERR',
				messages: {
					title: 'error',
					addressType: 'error'
				}
			};
			expect( store.form.title ).toStrictEqual( Validity.INCOMPLETE );
			expect( store.form.addressType ).toStrictEqual( Validity.VALID );

			mutations['FINISH_ADDRESS_VALIDATION']( store, resp );

			expect( store.form.title ).toStrictEqual( Validity.INVALID );
			expect( store.form.addressType ).toStrictEqual( Validity.INVALID );
		} );

	} );

} );