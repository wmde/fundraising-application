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

export default Vue.extend( {
	name: 'DonationForm',
	components: {
		Payment,
		AddressForm,
	},
	props: [ 'validateAddressUrl', 'validateAmountUrl', 'paymentAmounts', 'paymentIntervals', 'paymentTypes', 'addressCountries' ],
} );
</script>
