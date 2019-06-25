<template>
	<form class="column is-full">
		<keep-alive>
			<component
				:is="currentFormComponent"
				v-bind="currentProperties">
			</component>
		</keep-alive>
		<donation-summary v-if="showSummary" :payment="paymentSummary" :address-type="addressType" :address="addressSummary"></donation-summary>
		<submit-values :trackingData="trackingData"></submit-values>
		<div class="level has-margin-top-36">
			<div class="level-left">
				<b-button id="next" class="level-item"
					v-if="buttonsVisibility.next"
					@click="next()"
					type="is-primary is-main">
					{{ $t('donation_form_section_continue') }}
				</b-button>
				<b-button id="previous" class="level-item"
					v-if="buttonsVisibility.previous"
					@click="previous()"
					type="is-primary is-main">
					{{ $t('donation_form_section_back') }}
				</b-button>
			</div>
			<div class="level-right">
				<b-button id="submit" class="level-item"
					v-if="buttonsVisibility.submit"
					@click="submit()"
					type="is-primary is-main">
					{{ $t('donation_form_finalize') }}
				</b-button>
			</div>
		</div>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import DonationSummary from '@/components/DonationSummary.vue';
import Payment from '@/components/pages/donation_form/Payment.vue';
import AddressForm from '@/components/pages/donation_form/Address.vue';
import SubmitValues from '@/components/pages/donation_form/SubmitValues.vue';
import { action } from '@/store/util';
import { TrackingData } from '@/view_models/SubmitValues';
import { markEmptyValuesAsInvalid } from '@/store/payment/actionTypes';
import {
	NS_ADDRESS,
	NS_PAYMENT,
} from '@/store/namespaces';
import { addressTypeName } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'DonationForm',
	components: {
		Payment,
		AddressForm,
		DonationSummary,
		SubmitValues,
	},
	props: {
		validateAddressUrl: String,
		validateAmountUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		paymentTypes: Array as () => Array<String>,
		addressCountries: Array as () => Array<String>,
		trackingData: Object as () => TrackingData,
	},
	data: function () {
		return {
			currentFormComponent: 'Payment',
			buttonsVisibility: {
				previous: false,
				next: true,
				submit: false,
			},
		};
	},
	computed: {
		showSummary: {
			get(): boolean {
				return this.$data.currentFormComponent === 'AddressForm';
			},
		},
		currentProperties: {
			get(): object {
				if ( this.$data.currentFormComponent === 'AddressForm' ) {
					return {
						validateAddressUrl: this.$props.validateAddressUrl,
						countries: this.$props.addressCountries,
					};
				}
				return {
					validateAmountUrl: this.$props.validateAmountUrl,
					paymentAmounts: this.$props.paymentAmounts,
					paymentIntervals: this.$props.paymentIntervals,
					paymentTypes: this.$props.paymentTypes,
				};
			},
		},
		paymentSummary: {
			get(): object {
				const payment = this.$store.state[ NS_PAYMENT ].values;
				return {
					interval: payment.interval,
					amount: Number( payment.amount ),
					paymentType: payment.type,
				};
			},
		},
		addressType: {
			get(): string {
				return addressTypeName( this.$store.state[ NS_ADDRESS ].addressType );
			},
		},
		addressSummary: {
			get(): object {
				return {
					...this.$store.state[ NS_ADDRESS ].values,
					fullName: this.$store.getters[ NS_ADDRESS + '/fullName' ],
					streetAddress: this.$store.state[ NS_ADDRESS ].values.street,
					postalCode: this.$store.state[ NS_ADDRESS ].values.postcode,
				};
			},
		},
	},
	methods: {
		changeCurrentFormComponent( newComponent: string ): void {
			this.$data.currentFormComponent = newComponent;
			this.scrollToTop();
		},
		scrollToTop(): void {
			window.scrollTo( 0, 0 );
		},
		next(): void {
			this.$store.dispatch( action( NS_PAYMENT, markEmptyValuesAsInvalid ) );
			if ( this.$store.getters[ NS_PAYMENT + '/paymentDataIsValid' ] ) {
				this.changeCurrentFormComponent( 'AddressForm' );
				this.$data.buttonsVisibility.next = false;
				this.$data.buttonsVisibility.previous = true;
				this.$data.buttonsVisibility.submit = true;
			}
		},
		previous(): void {
			this.changeCurrentFormComponent( 'Payment' );
			this.$data.buttonsVisibility.next = true;
			this.$data.buttonsVisibility.previous = false;
			this.$data.buttonsVisibility.submit = false;
		},
		submit() {
			// TODO
		},
	},
} );
</script>
