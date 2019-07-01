<template>
    <form class="column is-full" ref="form" action="/apply-for-membership" method="post">
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
import { TrackingData } from '@/view_models/SubmitValues';
import PaymentPage from '@/components/pages/membership_form/subpages/PaymentPage.vue';
import AddressPage from '@/components/pages/membership_form/subpages/AddressPage.vue';

export default Vue.extend( {
	name: 'MembershipForm',
	components: {
		PaymentPage,
		AddressPage,
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
		submitMembershipForm(): void {
			( this.$refs.form as HTMLFormElement ).submit();
		},
	},
} );
</script>
