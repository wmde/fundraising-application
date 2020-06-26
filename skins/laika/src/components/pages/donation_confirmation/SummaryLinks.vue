<template>
	<div class="donation-links is-hidden-print">
		<a id="print-link" href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a>
		<a id="comment-link" @click="openPopUp()" v-if="donationCanBeCommented" :disabled="commentLinkIsDisabled">
			{{ commentLinkIsDisabled ? $t( 'donation_comment_popup_thanks' ) : $t( 'donation_confirmation_comment_button' ) }}
		</a>
		<b-modal :active.sync="openCommentPopUp" scroll="keep" has-modal-card>
			<donation-comment-pop-up
				v-on:disable-comment-link="commentLinkIsDisabled = true"
				v-if="openCommentPopUp"
				:donation="donation"
				:address-type="addressType"
				:post-comment-url="postCommentUrl"
			/>
		</b-modal>
		<div id="cancel-link" v-if="donationCanBeCanceled">
			<form class="has-margin-top-18" :action="cancelDonationUrl" method="post">
				<a href="javascript:" onclick="parentNode.submit();">{{ $t( 'donation_confirmation_cancel_button' ) }}</a>
				<input type="hidden" name="sid" :value="donation.id"/>
				<input type="hidden" name="utoken" :value="donation.updateToken">
			</form>
		</div>
	</div>
</template>
<script lang="ts">
import Vue from 'vue';
import DonationCommentPopUp from '@/components/DonationCommentPopUp.vue';
import { Donation } from '@/view_models/Donation';

export default Vue.extend( {
	name: 'SummaryLinks',
	components: {
		DonationCommentPopUp,
	},
	data: function () {
		return {
			openCommentPopUp: false,
			commentLinkIsDisabled: false,
		};
	},
	props: {
		donation: Object as () => Donation,
		addressType: String,
		cancelDonationUrl: String,
		postCommentUrl: String,
	},
	computed: {
		donationCanBeCanceled(): boolean {
			return this.$props.donation.paymentType === 'BEZ';
		},
		donationCanBeCommented(): boolean {
			return this.$props.donation.paymentType !== 'UEB';
		},
	},
	methods: {
		openPopUp(): void {
			if ( !this.$data.commentLinkIsDisabled ) {
				this.$data.openCommentPopUp = true;
			}
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
