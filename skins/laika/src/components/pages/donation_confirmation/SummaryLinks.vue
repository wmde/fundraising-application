<template>
	<div class="donation-links">
		<a href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a>
		<a :href="donationCommentUrl" v-if="donationCanBeCommented">{{ $t( 'donation_confirmation_comment_button' )
			}}</a>
		<div v-if="donationCanBeCanceled">
			<form class="has-margin-top-18" :action="confirmationData.urls.cancelDonation" method="post">
				<a href="javascript:" onclick="parentNode.submit();">{{ $t( 'donation_confirmation_cancel_button' )
					}}</a>
				<input type="hidden" name="sid" :value="confirmationData.donation.id"/>
				<input type="hidden" name="utoken" :value="confirmationData.donation.updateToken">
			</form>
		</div>
	</div>
</template>
<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'SummaryLinks',
	props: [
		'confirmationData',
	],
	computed: {
		donationCanBeCanceled(): boolean {
			return this.confirmationData.donation.paymentType === 'BEZ';
		},
		donationCanBeCommented(): boolean {
			return this.confirmationData.donation.paymentType !== 'UEB';
		},
		donationCommentUrl(): string {
			const donation = this.confirmationData.donation;
			return `/add-comment?donationId=${donation.id}&accessToken=${donation.accessToken}&updateToken=${donation.updateToken}`;
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";

	.donation-links {
		border-left: 1px solid $fun-color-gray-light-solid;
		padding: 0 0 18px 18px;
		& > a {
			display: block;
		}
	}
</style>
