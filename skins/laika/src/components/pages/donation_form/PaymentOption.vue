<template>
    <fieldset>
        <h2>Wie möchten Sie zahlen?</h2>
        <div>
            <div v-for="option in paymentOptions" :key="option.type">
                <input type="radio"
                       :id="option.id"
                       name="payment"
                       v-model="selectedOption"
                       :value="option.type"
                       @change="registerOption()">
                <label :for="option.id">
                    <span>{{ $t( option.type ) }}</span>
                </label>
            </div>
        </div>
        <span v-if="hasErrors">{{ $t('form_amount_error') }}</span>
    </fieldset>
</template>

<script lang="ts">
	import Vue from 'vue';
	import { OptionData } from "@/view_models/Payment";

	export default Vue.extend( {
		name: 'PaymentOption',
		data: function (): OptionData {
			return {
				selectedOption: '',
			};
		},
		props: [ 'paymentOptions' ],
		computed: {
			hasErrors: {
				get: function (): boolean {
					return !this.$store.getters[ 'payment/optionIsValid' ];
				},
			},
        },
		methods: {
			registerOption(): void {
				this.$store.dispatch( 'payment/registerOption', this.$data.selectedOption );
			}
		},
	} );
</script>
