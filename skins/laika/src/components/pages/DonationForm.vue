<template>
     <div class="column is-full">
        <form>
            <payment
                :validate-amount-url="validateAmountUrl"
                :payment-amounts="paymentAmounts"
                :payment-intervals="paymentIntervals"
                :payment-types="paymentTypes">
            </payment>
            <address-form
                :validate-address-url="validateAddressUrl"
                :countries="addressCountries">
            </address-form>
        </form>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/donation_form/Payment.vue';
import AddressForm from '@/components/pages/donation_form/Address.vue';
import { Interval, Type } from '@/view_models/Payment';

export default Vue.extend( {
	name: 'DonationForm',
	components: {
		Payment,
		AddressForm,
	},
	props: {
		validateAddressUrl: String,
		validateAmountUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Interval>,
		paymentTypes: Array as () => Array<Type>,
		addressCountries: Array as () => Array<String>,
	},
} );
</script>
