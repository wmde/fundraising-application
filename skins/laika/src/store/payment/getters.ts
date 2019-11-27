import { GetterTree } from 'vuex';
import { AmountValidity, Payment } from '@/view_models/Payment';
import { Validity } from '@/view_models/Validity';

export const getters: GetterTree<Payment, any> = {
	amountIsValid: function ( state: Payment ): boolean {
		return state.validity.amount !== Validity.INVALID;
	},
	typeIsValid: function ( state: Payment ): boolean {
		return state.validity.type !== Validity.INVALID;
	},
	amountValidity: function ( state: Payment ): AmountValidity {
		/* TODO reuse configuration amounts from config files:
		    "donation-minimum-amount": 1,
		    "donation-maximum-amount": 100000,
		    see https://phabricator.wikimedia.org/T239349
		    */
		if ( state.validity.amount !== Validity.INVALID ) {
			return AmountValidity.AMOUNT_VALID;
		}
		if ( Number( state.values.amount ) > 100000 ) {
			return AmountValidity.AMOUNT_TOO_HIGH;
		}
		return AmountValidity.AMOUNT_TOO_LOW;
	},
	paymentDataIsValid: function ( state: Payment ): boolean {
		for ( const prop in state.validity ) {
			if ( state.validity[ prop ] !== Validity.VALID ) {
				return false;
			}
		}
		return true;
	},
	isDirectDebitPayment: function ( state: Payment ): boolean {
		return state.values.type === 'BEZ';
	},
	isBankTransferPayment: function ( state: Payment ): boolean {
		return state.values.type === 'UEB';
	},
	isExternalPayment: ( state: Payment ): boolean => {
		const externalPaymentTypes = [ 'PPL', 'MCP', 'SUB' ];
		return externalPaymentTypes.indexOf( state.values.type ) > -1;
	},
};
