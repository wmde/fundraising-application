import { ActionTree } from 'vuex';
import { Payment } from "@/view_models/Payment";

export const actions: ActionTree<Payment, any> = {
    validateAmount( { commit }, amountData ) {
        commit( 'MARK_EMPTY_FIELDS_INVALID', amountData );
    }
};
