<template>
    <div class="payment-page">
        <payment v-bind="$props"></payment>
        <div class="level has-margin-top-36">
            <div class="level-left">
                <b-button id="previous-btn" class="level-item"
                          @click="$emit( 'previous-page' )"
                          type="is-primary is-main"
                          outlined>
                    {{ $t('donation_form_section_back') }}
                </b-button>
            </div>
            <div class="level-right">
                <b-button id="submit-btn" class="level-item"
                          @click="submit"
                          type="is-primary is-main">
                    {{ $t('donation_form_finalize') }}
                </b-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import Payment from '@/components/pages/membership_form/Payment.vue';
import { NS_BANKDATA, NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';

export default Vue.extend( {
	name: 'PaymentPage',
	components: {
		Payment,
	},
	props: {
		validateFeeUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
	},
	methods: {
		submit() {
			( this.$refs.address as any ).validateForm().then( () => {
				if ( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/requiredFieldsAreValid' ] &&
						this.$store.getters[ NS_BANKDATA + '/bankDataIsValid' ] ) {
					this.$emit( 'submit-membership' );
					return;
				}
			} );
		},
	},
} );
</script>

<style scoped>

</style>
