import { getters } from '@/store/payment/getters'
import { actions } from '@/store/payment/actions'
import { mutations } from '@/store/payment/mutations'
import { Payment } from "@/view_models/Payment";
import { Validity } from "@/view_models/Validity";
import { checkIfEmptyAmount, markEmptyValuesAsInvalid } from "@/store/payment/actionTypes";
import each from 'jest-each';
import moxios from 'moxios';
import { SET_AMOUNT } from "@/store/payment/mutationTypes";

function newMinimalStore( overrides: Object ): Payment {
	return Object.assign(
		{
			validity: {
				amount: Validity.INCOMPLETE,
				option: Validity.INCOMPLETE,
			},
			values: {
				amount: '',
				interval: '0',
				option: '',
			},
		},
		overrides
	);
}

describe( 'Payment', () => {

	const validityCases = [
		[ Validity.VALID, true ],
		[ Validity.INVALID, false ],
		[ Validity.INCOMPLETE, true ]
	];

	describe( 'Getters/amountIsValid', () => {
		it( 'does not return invalid amount on initalization', () => {
			expect( getters.amountIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( true );
		} );

		each( validityCases ).it( 'converts validity types to boolean state (test index %#)',
			( amountValidity, isValid ) => {
				const state = {
					validity: {
						amount: amountValidity,
						option: Validity.INCOMPLETE,
					}
				};
				expect( getters.amountIsValid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isValid );
			},
		);
	} );

	describe( 'Getters/optionIsValid', () => {
		it( 'does not return invalid option on initalization', () => {
			expect( getters.optionIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( true );
		} );

		each( validityCases ).it(
			'returns the expected validity for a given option (test index %#)',
			( optionValidity, isValid ) => {
				const state = {
					validity: {
						amount: Validity.INCOMPLETE,
						option: optionValidity,
					}
				};
				expect( getters.optionIsValid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isValid );
			},
		);
	} );

	describe( 'Actions/checkIfEmptyAmount', () => {
		it( 'commits to mutation [MARK_EMPTY_AMOUNT_INVALID]', () => {
			const commit = jest.fn();
			const action = actions[ checkIfEmptyAmount ] as any;
			action( { commit }, {
				amountValue: '5000',
				amountCustomValue: ''
			} );
			expect( commit ).toBeCalledWith(
				'MARK_EMPTY_AMOUNT_INVALID',
				{ amountValue: '5000', amountCustomValue: '' }
			)
		} )
	} );

	describe( 'Actions/markEmptyValuesAsInvalid', () => {
		it( 'commits to mutation [MARK_EMPTY_FIELDS_INVALID]', () => {
			const context = {
				commit: jest.fn(),
				getters: {
					'payment/paymentDataIsValid': true,
				},
			};
			const action = actions.markEmptyValuesAsInvalid as any;
			action( context );
			expect( context.commit ).toBeCalledWith(
				'MARK_EMPTY_FIELDS_INVALID'
			);
		} );
	} );

	describe( 'Actions/setAmount', () => {
		beforeEach( function () {
			moxios.install();
		} );
		afterEach( function () {
			moxios.uninstall();
		} );
		it( 'commits to mutation [SET_AMOUNT]', () => {
			const context = {
					commit: jest.fn(),
				},
				payload = {
					amountValue: '2500',
					validateAmountURL: '/validation-amount-url',
				};
			moxios.stubRequest( payload.validateAmountURL, {
				status: 200,
				responseText: 'OK',
			} );
			const action = actions.setAmount as any;
			action( context, payload );
			expect( context.commit ).toHaveBeenCalledWith(
				'SET_AMOUNT',
				payload.amountValue
			);
		} );
	} );

	describe( 'Mutations', () => {
		const amountInputStates = [
			[ { amountValue: '1200', amountCustomValue: '' }, Validity.VALID ],
			[ { amountValue: '', amountCustomValue: '1500' }, Validity.VALID ],
			[ { amountValue: '', amountCustomValue: '' }, Validity.INVALID ]
		];

		each( amountInputStates ).it(
			'mutates the state with the correct validity for a given amount (test index %#)',
			( inputData, isValid ) => {
				const store = newMinimalStore( {} );
				mutations[ 'MARK_EMPTY_AMOUNT_INVALID' ]( store, inputData );
				expect( store.validity.amount ).toStrictEqual( isValid );
			} );

		const serverResponseStates = [
			[ { data: { status: 'OK' } }, Validity.VALID ],
			[ { data: { status: 'ERR' } }, Validity.INVALID ]
		];

		each( serverResponseStates ).it(
			'mutates the state with the correct validity for a given server response (test index %#)',
			( inputData, isValid ) => {
				const store = newMinimalStore( {} );
				mutations.SET_AMOUNT_VALIDITY( store, inputData );
				expect( store.validity.amount ).toStrictEqual( isValid );
			} );

		it( 'mutates the amount', () => {
			const store = newMinimalStore( {} );
			mutations.SET_AMOUNT( store, 2500 );
			expect( store.values.amount ).toStrictEqual( 2500 );
			mutations.SET_AMOUNT( store, 5500 );
			expect( store.values.amount ).toStrictEqual( 5500 );
		} );

		it( 'mutates the interval', () => {
			const store = newMinimalStore( {} );
			mutations.SET_INTERVAL( store, 3 );
			expect( store.values.interval ).toStrictEqual( 3 );
			mutations.SET_INTERVAL( store, 0 );
			expect( store.values.interval ).toStrictEqual( 0 );
		} );

		it( 'mutates the payment option', () => {
			const store = newMinimalStore( {} );
			mutations.SET_OPTION( store, 'UEB' );
			expect( store.values.option ).toStrictEqual( 'UEB' );
			mutations.SET_OPTION( store, 'BEZ' );
			expect( store.values.option ).toStrictEqual( 'BEZ' );
		} );
	} );
} );