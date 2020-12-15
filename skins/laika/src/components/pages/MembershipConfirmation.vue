<template>
	<div class="membership-confirmation">
		<div class="donation-summary-wrapper has-background-bright columns has-padding-18">
			<div class="column is-half">
				<membership-summary :address="confirmationData.address"
									:membershipApplication="confirmationData.membershipApplication">
					<div class="has-margin-top-18" v-if="hasIncentives()">{{ $t( 'membership_confirmation_success_text_incentive' ) }}</div>
					<div class="has-margin-top-18" v-if="!hasIncentives()">{{ $t( 'membership_confirmation_success_text' ) }}</div>
				</membership-summary>
			</div>

			<div class="column is-half">
				<summary-links :confirmation-data="confirmationData"/>
			</div>
			<img src="https://de.wikipedia.org/wiki/Special:HideBanners?category=fr-thankyou&duration=15552000&reason=membership"
				alt=""
				width="0"
				height="0"
			/>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import MembershipSummary from '@/components/MembershipSummary.vue';
import SummaryLinks from '@/components/pages/membership_confirmation/SummaryLinks.vue';

export default Vue.extend( {
	name: 'MembershipConfirmation',
	components: {
		MembershipSummary,
		SummaryLinks,
	},
	props: [
		'confirmationData',
	],
	methods: {
		hasIncentives(): boolean {
			return this.confirmationData.membershipApplication.incentives !== undefined
				&& this.confirmationData.membershipApplication.incentives.length > 0;
		},
	},
} );
</script>
