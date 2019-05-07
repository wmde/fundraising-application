import { getters } from '@/store/payment/getters'
import { actions } from '@/store/payment/actions'
import { mutations } from '@/store/payment/mutations'
import { AmountData, Payment } from "@/view_models/Payment";
import { Validity } from "@/view_models/Validity";
import each from 'jest-each';

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

	describe( 'Getters/amountIsValid', () => {
		it( 'does not return invalid amount on initalization', () => {
			expect( getters.amountIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( true );
		} );
		each( [ [ Validity.VALID, true ], [ Validity.INVALID, false ] ] ).it(
			'returns the expected validity for a given amount',
			( validity, isValid ) => {
				const state = {
					validity: {
						amount: validity,
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
		each( [ [ Validity.VALID, true ], [ Validity.INVALID, false ] ] ).it(
			'returns the expected validity for a given option',
			( validity, isValid ) => {
				const state = {
					validity: {
						amount: Validity.INCOMPLETE,
						option: validity,
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

	describe( 'Actions/validateAmount', () => {
		it( 'commits to mutation [MARK_EMPTY_FIELD_INVALID]', () => {
			const commit = jest.fn();
			const action = actions[ 'validateAmount' ] as any;
			action( { commit }, 1500 );
			expect( commit ).toBeCalledWith(
				'MARK_EMPTY_FIELD_INVALID',
				1500
			)
		} )
	} );

	describe( 'Actions/setAmount', () => {
		xit( 'commits to mutation [SET_AMOUNT]', () => {
			const commit = jest.fn();
			const action = actions[ 'setAmount' ] as any;
			action( { commit }, 2500 );
			expect( commit ).toBeCalledWith(
				'SET_AMOUNT',
				2500
			)
		} );
	} );

	describe( 'Mutations', () => {
		each( [ [ { amountValue: '1200', amountCustomValue: '' }, Validity.VALID ],
			[ { amountValue: '', amountCustomValue: '1500' }, Validity.VALID ],
			[ { amountValue: '', amountCustomValue: '' }, Validity.INVALID ] ] ).it(
			'mutates the state with the correct validity for a given amount',
			( inputData, isValid ) => {
				const store = newMinimalStore( {} );
				mutations[ 'MARK_EMPTY_FIELD_INVALID' ]( store, inputData );
				expect( store.validity.amount ).toStrictEqual( isValid );
			} );

		each( [ [ { data: { status: 'OK' } }, Validity.VALID ],
			[ { data: { status: 'ERR' } }, Validity.INVALID ] ] ).it(
			'mutates the state with the correct validity for a given server response',
			( inputData, isValid ) => {
				const store = newMinimalStore( {} );
				mutations[ 'SET_AMOUNT_VALIDITY' ]( store, inputData );
				expect( store.validity.amount ).toStrictEqual( isValid );
			} );

		it( 'mutates the amount', () => {
			const store = newMinimalStore( {} );
			mutations[ 'SET_AMOUNT' ]( store, 2500 );
			expect( store.values.amount ).toStrictEqual( 2500 );
			mutations[ 'SET_AMOUNT' ]( store, 5500 );
			expect( store.values.amount ).toStrictEqual( 5500 );
		} );

		it( 'mutates the interval', () => {
			const store = newMinimalStore( {} );
			mutations[ 'SET_INTERVAL' ]( store, 3 );
			expect( store.values.interval ).toStrictEqual( 3 );
			mutations[ 'SET_INTERVAL' ]( store, 0 );
			expect( store.values.interval ).toStrictEqual( 0 );
		} );

		it( 'mutates the payment option', () => {
			const store = newMinimalStore( {} );
			mutations[ 'SET_OPTION' ]( store, 'UEB' );
			expect( store.values.option ).toStrictEqual( 'UEB' );
			mutations[ 'SET_OPTION' ]( store, 'BEZ' );
			expect( store.values.option ).toStrictEqual( 'BEZ' );
		} );
	} );
} );