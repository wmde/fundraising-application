import { MutationTree } from 'vuex';
import Payment from "@/view_models/Payment";
import { Validity } from "@/view_models/Validity";

const MARK_EMPTY_FIELD_INVALID: string = 'MARK_EMPTY_FIELD_INVALID';

export const mutations: MutationTree<Payment> = {
	[ MARK_EMPTY_FIELD_INVALID ]( state, payload ) {

	}
}