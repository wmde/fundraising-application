<template>
	<div id="laika-donation" class="column is-full">
		<keep-alive>
			<component
				ref="currentPage"
				:is="currentFormComponent"
				v-on:next-page="changePageIndex( 1 )"
				v-on:previous-page="changePageIndex( -1 )"
				v-bind="currentProperties">
			</component>
		</keep-alive>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { TrackingData } from '@/view_models/SubmitValues';
import PaymentPage from '@/components/pages/donation_form/subpages/PaymentPage.vue';
import AddressPage from '@/components/pages/donation_form/subpages/AddressPage.vue';

export default Vue.extend( {
	name: 'DonationForm',
	components: {
		PaymentPage,
		AddressPage,
	},
	props: {
		validateAddressUrl: String,
		validateAmountUrl: String,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<Number>,
		paymentTypes: Array as () => Array<String>,
		addressCountries: Array as () => Array<String>,
		trackingData: Object as () => TrackingData,
		startPage: {
			type: String,
			default: () => 'PaymentPage',
		},
	},
	data: function () {
		const pages = [ 'PaymentPage', 'AddressPage' ];
		const currentPageIndex = pages.indexOf( this.$props.startPage );
		if ( currentPageIndex < 0 ) {
			throw new Error( `Unknown initial page name '${this.$props.startPage}'. Valid page names are: ${pages.join( ', ' )}` );
		}
		return {
			pages,
			currentPageIndex,
		};
	},
	computed: {
		currentFormComponent: {
			get(): string {
				return this.$data.pages[ this.$data.currentPageIndex ];
			},
		},
		currentProperties: {
			get(): object {
				if ( this.currentFormComponent === 'AddressPage' ) {
					return {
						validateAddressUrl: this.$props.validateAddressUrl,
						validateBankDataUrl: this.$props.validateBankDataUrl,
						validateLegacyBankDataUrl: this.$props.validateLegacyBankDataUrl,
						countries: this.$props.addressCountries,
						trackingData: this.$props.trackingData,
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
	},
	methods: {
		changePageIndex( indexChange: number ): void {
			const newIndex = this.$data.currentPageIndex + indexChange;
			if ( newIndex >= 0 && newIndex < this.$data.pages.length ) {
				this.$data.currentPageIndex = newIndex;
				this.scrollToTop();
			}
		},
		scrollToTop(): void {
			window.scrollTo( 0, 0 );
		},
	},
} );
</script>
