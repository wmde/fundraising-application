import { GetterTree } from 'vuex';
import { MembershipFee } from '@/view_models/MembershipFee';
import { Validity } from '@/view_models/Validity';

export const getters: GetterTree<MembershipFee, any> = {
	feeIsValid: function ( state: MembershipFee ): boolean {
		return state.validity.fee !== Validity.INVALID;
	},
	typeIsValid: function ( state: MembershipFee ): boolean {
		return state.validity.type !== Validity.INVALID;
	},
	paymentDataIsValid: function ( state: MembershipFee ): boolean {
		for ( const prop in state.validity ) {
			if ( state.validity[ prop ] !== Validity.VALID ) {
				return false;
			}
		}
		return true;
	},
};
