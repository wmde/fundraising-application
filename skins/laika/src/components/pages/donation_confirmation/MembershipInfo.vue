<template>
	<div class="is-hidden-print membership-benefits-wrapper has-background-bright columns has-margin-top-36 has-padding-18">
		<div class="column is-half">
			<h2 class="title is-size-5">{{ $t( 'donation_confirmation_membership_call_to_action_title' ) }}</h2>
			<p>{{ $t( 'donation_confirmation_membership_call_to_action_text' ) }}</p>
			<p>
				<a id="membership-application-url" :href="membershipApplicationUrl">
					<b-button type="is-primary is-main has-margin-top-36">{{ $t('donation_confirmation_membership_button') }}</b-button>
				</a>
			</p>
		</div>
		<div class="column is-half">
			<ul class="membership-benefits">
				<li>{{ $t( 'donation_confirmation_bottombox_membership_benefit_1' ) }}</li>
				<li class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_2' ) }}</li>
				<li class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_3' ) }}</li>
				<li class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_4' ) }}</li>
				<li class="has-margin-top-18"><a href="https://wikimedia.de/de/mitglied-werden">{{ $t( 'donation_confirmation_bottombox_membership_link' ) }}</a></li>
			</ul>
		</div>

	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Donation } from '@/view_models/Donation';

export default Vue.extend( {
	name: 'MembershipInfo',
	props: {
		donation: Object as () => Donation,
	},
	computed: {
		membershipApplicationUrl(): string {
			const donation = this.$props.donation;
			return `apply-for-membership?donationId=${donation.id}&donationAccessToken=${donation.accessToken}&type=sustaining`;
		},
	},
} );
</script>

<style lang="scss">
	@import "../../../scss/custom";
	.membership-benefits {
		list-style: disc;
		&-wrapper {
			border: 1px solid $fun-color-primary-light;
			border-radius: 2px;
		}
	}
</style>
