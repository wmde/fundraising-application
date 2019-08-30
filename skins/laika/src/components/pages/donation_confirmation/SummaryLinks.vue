<template>
	<div class="donation-links is-hidden-print">
		<a id="print-link" href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a>
		<a id="comment-link" @click="addComment = true" v-if="donationCanBeCommented">
			{{ $t( 'donation_confirmation_comment_button' )}}
		</a>
		 <b-modal :active.sync="addComment" has-modal-card>
            <donation-comment-pop-up v-if="addComment"></donation-comment-pop-up>
        </b-modal>
		<div id="cancel-link" v-if="donationCanBeCanceled">
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
import DonationCommentPopUp from '@/components/DonationCommentPopUp.vue';


export default Vue.extend( {
	name: 'SummaryLinks',
	components: {
		DonationCommentPopUp,
	},
	data: function() {
		return {
			addComment: false,
		}
	},
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
