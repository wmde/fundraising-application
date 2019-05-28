<template>
	<div class="column is-full">
		<donation-summary></donation-summary>
		<membership-info></membership-info>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import DonationSummary from "@/components/pages/donation_confirmation/DonationSummary.vue";
import MembershipInfo from "@/components/pages/donation_confirmation/MembershipInfo.vue";


export default Vue.extend( {
	name: 'DonationConfirmation',
	components: {
		DonationSummary,
		MembershipInfo,
	},
	props: {

	},
} );
</script>
