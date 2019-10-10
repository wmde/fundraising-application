<template>
	<fieldset>
		<legend class="title is-size-5">{{ title }}</legend>
		<div>
			<div v-for="paymentType in paymentTypes" :key="paymentType">
				<b-radio :class="{ 'is-active': selectedType === paymentType }"
						:id="'payment-' + paymentType.toLowerCase()"
						name="payment"
						v-model="selectedType"
						:native-value="paymentType"
						:disabled="disabledPaymentTypes.indexOf( paymentType ) > -1"
						@change.native="setType">
					{{ $t( paymentType ) }}
				</b-radio>
			</div>
		</div>
		<span class="help is-danger" v-if="error">{{ error }}</span>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { TypeData } from '@/view_models/Payment';

export default Vue.extend( {
	name: 'PaymentType',
	data: function (): TypeData {
		return {
			selectedType: this.$props.currentType,
		};
	},
	props: {
		currentType: String,
		error: {
			type: String,
			default: '',
		},
		paymentTypes: Array,
		title: String,
		disabledPaymentTypes: {
			type: Array,
			default: () => [],
		},
	},
	methods: {
		setType(): void {
			this.$emit( 'payment-type-selected', this.$data.selectedType );
		},
	},
	watch: {
		currentType: function ( newType ) {
			this.selectedType = newType;
		},
	},
} );
</script>
