<template>
    <fieldset>
        <h2 class="title is-size-5">{{ $t('donation_form_payment_type_title') }}</h2>
        <div>
            <div v-for="paymentMethod in paymentTypes" :key="paymentMethod.type">
                <b-radio class="is-primary"
                       :class="{ 'is-active': selectedType === paymentMethod.type }"
                       type="radio"
                       :id="paymentMethod.id"
                       name="payment"
                       v-model="selectedType"
                       :native-value="paymentMethod.type"
                       @change="setType()">
                    {{ $t( paymentMethod.type ) }}
                </b-radio>
            </div>
        </div>
        <span class="help is-danger" v-if="hasErrors">{{ $t('donation_form_payment_type_error') }}</span>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { TypeData } from '@/view_models/Payment';
import { NS_PAYMENT } from '@/store/namespaces';
import { action } from '@/store/util';
import { setType, markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';

export default Vue.extend( {
	name: 'PaymentType',
	data: function (): TypeData {
		return {
			selectedType: '',
		};
	},
	props: [ 'paymentTypes' ],
	computed: {
		hasErrors: {
			get: function (): boolean {
				return !this.$store.getters[ 'payment/typeIsValid' ];
			},
		},
	},
	methods: {
		setType(): void {
			this.$store.dispatch( action( NS_PAYMENT, setType ), this.$data.selectedType );
		},
	},
} );
</script>
