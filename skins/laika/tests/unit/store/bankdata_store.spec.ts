import { getters } from '@/store/bankdata/getters';
import { actions } from '@/store/bankdata/actions';
import { Validity } from '@/view_models/Validity';
import each from 'jest-each';
import { BankAccount, BankAccountRequest, BankAccountResponse } from '@/view_models/BankAccount';
import moxios from 'moxios';

function newMinimalStore( overrides: Object ): BankAccount {
	return Object.assign(
		{
			validity: {
				bankdata: Validity.INCOMPLETE,
			},
			values: {
				bankName: '',
				bic: '',
				iban: '',
			},
		},
		overrides
	);
}

describe( 'BankData', () => {
	describe( 'Getters/bankDataIsInvalid', () => {
		it( 'does not return invalid bank data on initalization', () => {
			expect( getters.bankDataIsInvalid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( false );
		} );

		const validityCases = [
			[ Validity.VALID, false ],
			[ Validity.INVALID, true ],
			[ Validity.INCOMPLETE, false ],
		];

		each( validityCases ).it( 'returns correct boolean representation of bank data validity (test index %#)',
			( bankDataValidity, isInvalid ) => {
				const state = {
					validity: {
						bankdata: bankDataValidity,
					},
				};
				expect( getters.bankDataIsInvalid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isInvalid );
			},
		);
	} );

	describe( 'Getters/bankDataIsValid', () => {
		it( 'does not return valid bank data on initalization', () => {
			expect( getters.bankDataIsValid(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( false );
		} );

		const validityCases = [
			[ Validity.VALID, true ],
			[ Validity.INVALID, false ],
			[ Validity.INCOMPLETE, false ],
		];

		each( validityCases ).it( 'returns correct boolean representation of bank data validity (test index %#)',
			( bankDataValidity, isValid ) => {
				const state = {
					validity: {
						bankdata: bankDataValidity,
					},
				};
				expect( getters.bankDataIsValid(
					newMinimalStore( state ),
					null,
					null,
					null
				) ).toBe( isValid );
			},
		);
	} );

	describe( 'Getters/getBankName', () => {
		it( 'does not return a bank name on initalization', () => {
			expect( getters.getBankName(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( '' );
		} );

		it( 'does returns bank name from the store', () => {
			const state = {
				values: {
					bankName: 'Cool Bank 3000',
				},
			};
			expect( getters.getBankName(
				newMinimalStore( state ),
				null,
				null,
				null
			) ).toBe( 'Cool Bank 3000' );
		} );
	} );

	describe( 'Getters/getBankId', () => {
		it( 'does not return a bank identifier on initalization', () => {
			expect( getters.getBankId(
				newMinimalStore( {} ),
				null,
				null,
				null
			) ).toBe( '' );
		} );

		it( 'returns bank identifier from the store', () => {
			const state = {
				values: {
					bic: 'ABCDDEFFXXX',
				},
			};
			expect( getters.getBankId(
				newMinimalStore( state ),
				null,
				null,
				null
			) ).toBe( 'ABCDDEFFXXX' );
		} );
	} );

	describe( 'Actions/setBankData', () => {
		beforeEach( function () {
			moxios.install();
		} );

		afterEach( function () {
			moxios.uninstall();
		} );

		const testIban = 'DE12345605171238489890',
			testBIC = 'ABCDDEFFXXX',
			testAccount = '34560517',
			testBankCode = '50010517',
			testBankName = 'Cool Bank 3000';

		it( 'commits to mutations [SET_BANK_DATA_VALIDITY], [SET_BANKNAME], [SET_BANKDATA]', ( done ) => {
			const context = {
					commit: jest.fn(),
				},
				payload = {
					validationUrl: '/check-iban',
					requestParams: { iban: testIban },
				} as BankAccountRequest,
				action = actions.setBankData as any;

			action( context, payload );
			moxios.wait( function () {
				let request = moxios.requests.mostRecent();
				request.respondWith( {
					status: 200,
					response: {
						status: 'OK',
						bic: testBIC,
						iban: testIban,
						account: testAccount,
						bankCode: testBankCode,
						bankName: testBankName,
					} as BankAccountResponse,
				} ).then( function () {
					expect( context.commit ).toHaveBeenNthCalledWith( 1, 'SET_BANK_DATA_VALIDITY', Validity.VALID );
					expect( context.commit ).toHaveBeenNthCalledWith( 2, 'SET_BANKNAME', testBankName );
					expect( context.commit ).toHaveBeenNthCalledWith( 3, 'SET_BANKDATA', { accountId: testIban, bankId: testBIC } );
					done();
				} );
			} );
		} );

		it( 'resets the bank name via [SET_BANKNAME] on invalid account data', ( done ) => {
			const context = {
					commit: jest.fn(),
				},
				payload = {
					validationUrl: '/check-iban',
					requestParams: { iban: testIban },
				} as BankAccountRequest,
				action = actions.setBankData as any;

			action( context, payload );
			moxios.wait( function () {
				let request = moxios.requests.mostRecent();
				request.respondWith( {
					status: 200,
					response: {
						status: 'ERR',
					} as BankAccountResponse,
				} ).then( function () {
					expect( context.commit ).toHaveBeenNthCalledWith( 1, 'SET_BANK_DATA_VALIDITY', Validity.INVALID );
					expect( context.commit ).toHaveBeenNthCalledWith( 2, 'SET_BANKNAME', '' );
					done();
				} );
			} );
		} );
	} );
} );
