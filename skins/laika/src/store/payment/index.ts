import { Module } from 'vuex';
import Payment from "@/view_models/Payment";
import { actions } from "@/store/payment/actions";
import { getters } from "@/store/payment/getters";
import { mutations } from "@/store/payment/mutations";
import { Validity } from "@/view_models/Validity";

export default function (): Module<Payment, any> {
	const state: Payment = {
		amount: Validity.INCOMPLETE
	};
	const namespaced = true;

	return {
		namespaced,
		state,
		getters,
		mutations,
		actions,
	};
}
