<template>
	<form name="laika-membership" class="column is-full" ref="form" action="/apply-for-membership" method="post">
		<keep-alive>
			<component
				ref="currentPage"
				:is="currentFormComponent"
				v-on:next-page="changePageIndex( 1 )"
				v-on:previous-page="changePageIndex( -1 )"
				v-on:submit-membership="submitMembershipForm"
				v-bind="currentProperties">
			</component>
		</keep-alive>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import PaymentPage from '@/components/pages/membership_form/subpages/PaymentPage.vue';
import AddressPage from '@/components/pages/membership_form/subpages/AddressPage.vue';
import { trackFormSubmission } from '@/tracking';

export default Vue.extend( {
	name: 'MembershipForm',
	components: {
		PaymentPage,
		AddressPage,
	},
	props: {
		validateAddressUrl: String,
		validateBankDataUrl: String,
		validateLegacyBankDataUrl: String,
		validateFeeUrl: String,
		paymentAmounts: Array as () => Array<String>,
		paymentIntervals: Array as () => Array<String>,
		addressCountries: Array as () => Array<String>,
		showMembershipTypeOption: Boolean,
	},
	data: function () {
		return {
			pages: [ 'AddressPage', 'PaymentPage' ],
			currentPageIndex: 0,
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
						countries: this.$props.addressCountries,
						showMembershipTypeOption: this.$props.showMembershipTypeOption,
					};
				}
				return {
					validateFeeUrl: this.$props.validateFeeUrl,
					paymentAmounts: this.$props.paymentAmounts,
					paymentIntervals: this.$props.paymentIntervals,
					validateBankDataUrl: this.$props.validateBankDataUrl,
					validateLegacyBankDataUrl: this.$props.validateLegacyBankDataUrl,
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
		submitMembershipForm(): void {
			const form = this.$refs.form as HTMLFormElement;
			trackFormSubmission( form );
			form.submit();
		},
	},
} );
</script>
