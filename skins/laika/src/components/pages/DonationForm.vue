<template>
	<div class="column is-full">
		<form>
		<keep-alive>
			<component
				:is="currentFormComponent"
				v-bind="currentProperties"
				v-on:change-component="changeCurrentFormComponent( $event )">
			</component>
		</keep-alive>
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
	props: {
		validateAddressUrl: String,
		validateAmountUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		paymentTypes: Array as () => Array<String>,
		addressCountries: Array as () => Array<String>,
	},
	data: function () {
		return {
			currentFormComponent: 'Payment',
		};
	},
	computed: {
		currentProperties: {
			get() {
				if ( this.$data.currentFormComponent === 'Payment' ) {
					return {
						validateAmountUrl: this.$props.validateAmountUrl,
						paymentAmounts: this.$props.paymentAmounts,
						paymentIntervals: this.$props.paymentIntervals,
						paymentTypes: this.$props.paymentTypes,
					};
				} else if ( this.$data.currentFormComponent === 'AddressForm' ) {
					return {
						validateAddressUrl: this.$props.validateAddressUrl,
						countries: this.$props.addressCountries,
					};
				}
			},
		},
	},
	methods: {
		changeCurrentFormComponent( newComponent: string ) {
			this.$data.currentFormComponent = newComponent;
		},
	},
} );
</script>
