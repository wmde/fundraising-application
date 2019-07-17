import { getters } from '@/store/membership_fee/getters';
import { actions } from '@/store/membership_fee/actions';
import { mutations } from '@/store/membership_fee/mutations';
import { MembershipFee } from '@/view_models/MembershipFee';
import { Validity } from '@/view_models/Validity';
import {
	markEmptyFeeAsInvalid,
} from '@/store/membership_fee/actionTypes';
import {
	MARK_EMPTY_FEE_INVALID,
	SET_FEE,
	SET_FEE_VALIDITY,
	SET_INTERVAL,
	SET_INTERVAL_VALIDITY,
} from '@/store/membership_fee/mutationTypes';
import each from 'jest-each';
import moxios from 'moxios';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

function newMinimalStore( overrides: Object ): MembershipFee {
	return Object.assign(
		{
			validity: {
				fee: Validity.INCOMPLETE,
				type: Validity.INCOMPLETE,
			},
			values: {
				fee: '',
				interval: '',
				type: 'BEZ',
			},
		},
		overrides
	);
}

describe( 'MembershipFee', () => {

	const validityCases = [
		[ Validity.VALID, true ],
		[ Validity.INVALID, false ],
		[ Validity.INCOMPLETE, true ],
	];

	describe( 'Getters/feeIsValid', () => {
		it( 'does not return invalid fee on initalization', () => {
			expect( getters.feeIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( true );
		} );

		each( validityCases ).it( 'converts validity types to boolean state (test index %#)',
			( feeValidity, isValid ) => {
				const state = {
					validity: {
						fee: feeValidity,
						type: Validity.INCOMPLETE,
					},
				};
				expect( getters.feeIsValid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isValid );
			},
		);
	} );

	describe( 'Getters/typeIsValid', () => {
		it( 'does not return invalid payment type on initalization', () => {
			expect( getters.typeIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( true );
		} );

		each( validityCases ).it(
			'returns the expected validity for a given type (test index %#)',
			( typeValidity, isValid ) => {
				const state = {
					validity: {
						fee: Validity.INCOMPLETE,
						type: typeValidity,
					},
				};
				expect( getters.typeIsValid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isValid );
			},
		);
	} );

	describe( 'Actions/markEmptyFeeAsInvalid', () => {
		it( 'commits to mutation [MARK_EMPTY_FEE_INVALID]', () => {
			const commit = jest.fn();
			const action = actions[ markEmptyFeeAsInvalid ] as any;
			action( { commit } );
			expect( commit ).toBeCalledWith(
				MARK_EMPTY_FEE_INVALID
			);
		} );
	} );

	describe( 'Actions/markEmptyValuesAsInvalid', () => {
		it( 'commits to mutation [MARK_EMPTY_FIELDS_INVALID]', () => {
			const context = {
				commit: jest.fn(),
				getters: {
					'membership_fee/paymentDataIsValid': true,
				},
			};
			const action = actions.markEmptyValuesAsInvalid as any;
			action( context );
			expect( context.commit ).toBeCalledWith(
				'MARK_EMPTY_FIELDS_INVALID'
			);
		} );
	} );

	describe( 'Actions/setInterval', () => {
		it( 'commits to mutation [SET_INTERVAL] and [SET_INTERVAL_VALIDITY]', () => {
			const context = {
				commit: jest.fn(),
			};
			const action = actions.setInterval as any;
			action( context, 3 );
			expect( context.commit ).toBeCalledWith(
				'SET_INTERVAL',
				3
			);
			expect( context.commit ).toBeCalledWith(
				'SET_INTERVAL_VALIDITY'
			);
		} );
	} );

	describe( 'Actions/setFee', () => {
		beforeEach( function () {
			moxios.install();
		} );

		afterEach( function () {
			moxios.uninstall();
		} );

		it( 'commits to mutation [SET_FEE]', () => {
			const context = {
					commit: jest.fn(),
					state: {
						values: {
							interval: 12,
						},
					},
					rootState: {
						membership_address: { // eslint-disable-line camelcase
							addressType: AddressTypeModel.PERSON,
						},
					},
				},
				payload = {
					feeValue: '2500',
					validateFeeUrl: '/validation-fee-url',
				};
			moxios.stubRequest( payload.validateFeeUrl, {
				status: 200,
				responseText: 'OK',
			} );
			const action = actions.setFee as any;
			action( context, payload );
			expect( context.commit ).toHaveBeenCalledWith(
				SET_FEE,
				payload.feeValue
			);
		} );

		it( 'sends a post request for fee validation', () => {
			const context = {
					commit: jest.fn(),
					state: {
						values: {
							interval: 12,
						},
					},
					rootState: {
						membership_address: { // eslint-disable-line camelcase
							addressType: AddressTypeModel.PERSON,
						},
					},
				},
				payload = {
					feeValue: '2500',
					validateFeeUrl: '/validation-fee-url',
				},
				bodyFormData = new FormData();
			bodyFormData.append( 'amount', '25.00' );
			bodyFormData.append( 'paymentIntervalInMonths', '12' );
			bodyFormData.append( 'addressType', 'person' );
			const action = actions.setFee as any;
			action( context, payload );

			moxios.wait( function () {
				const request = moxios.requests.mostRecent();
				expect( request.config.method ).toBe( 'post' );
				expect( request.config.data ).toStrictEqual( bodyFormData );
			} );
		} );

		it( 'commits to mutation [SET_FEE_VALIDITY] after server side validation', ( done ) => {
			const context = {
					commit: jest.fn(),
					state: {
						values: {
							interval: 12,
						},
					},
					rootState: {
						membership_address: { // eslint-disable-line camelcase
							addressType: AddressTypeModel.PERSON,
						},
					},
				},
				payload = {
					feeValue: '2500',
					validateFeeUrl: '/validation-fee-url',
				},
				action = actions.setFee as any;

			action( context, payload );

			moxios.wait( function () {
				let request = moxios.requests.mostRecent();
				request.respondWith( {
					status: 200,
					response: {
						'status': 'OK',
					},
				} ).then( function () {
					expect( context.commit ).toHaveBeenCalledWith(
						'SET_FEE_VALIDITY',
						Validity.VALID
					);
					done();
				} );
			} );
		} );
	} );

	describe( 'Mutations/MARK_EMPTY_FEE_INVALID', () => {
		const feeStates = [
			[ { values: { fee: '1' } }, Validity.VALID ],
			[ { values: { fee: '' } }, Validity.INVALID ],
			[ { values: { fee: '0' } }, Validity.INVALID ],
			[ { values: { fee: 'hello' } }, Validity.INVALID ],
		];

		each( feeStates ).it(
			'mutates the state with the correct validity for a given fee (test index %#)',
			( feeState, expectedValidity ) => {
				const store = newMinimalStore( feeState );
				mutations.MARK_EMPTY_FEE_INVALID( store, {} );
				expect( store.validity.fee ).toStrictEqual( expectedValidity );
			} );
	} );

	describe( 'Mutations/SET_FEE_VALIDITY', () => {
		it( 'mutates the fee validity', () => {
			const store = newMinimalStore( {} );
			mutations.SET_FEE_VALIDITY( store, Validity.VALID );
			expect( store.validity.fee ).toStrictEqual( Validity.VALID );
			mutations.SET_FEE_VALIDITY( store, Validity.INVALID );
			expect( store.validity.fee ).toStrictEqual( Validity.INVALID );
		} );
	} );

	describe( 'Mutations/SET_FEE', () => {
		it( 'mutates the fee', () => {
			const store = newMinimalStore( {} );
			mutations.SET_FEE( store, 2500 );
			expect( store.values.fee ).toStrictEqual( 2500 );
			mutations.SET_FEE( store, 5500 );
			expect( store.values.fee ).toStrictEqual( 5500 );
		} );
	} );

	describe( 'Mutations/SET_INTERVAL', () => {
		it( 'mutates the interval', () => {
			const store = newMinimalStore( {} );
			mutations.SET_INTERVAL( store, 3 );
			expect( store.values.interval ).toStrictEqual( 3 );
			mutations.SET_INTERVAL( store, 0 );
			expect( store.values.interval ).toStrictEqual( 0 );
		} );
	} );

	describe( 'Mutations/SET_TYPE', () => {
		it( 'mutates the payment type', () => {
			const store = newMinimalStore( {} );
			mutations.SET_TYPE( store, 'UEB' );
			expect( store.values.type ).toStrictEqual( 'UEB' );
			mutations.SET_TYPE( store, 'BEZ' );
			expect( store.values.type ).toStrictEqual( 'BEZ' );
		} );
	} );
} );
