<template>
	<div class="column is-full">
		<div class="donation-summary-wrapper has-background-bright columns has-padding-18">

			<div class="column is-half">
				<membership-summary :address="confirmationData.address"
									:membershipApplication="confirmationData.membershipApplication"
									:addressType="addressType">
					<div class="title is-size-5">{{ $t( 'donation_confirmation_topbox_intro' ) }}</div>
				</membership-summary>
			</div>

			<div class="column is-half">
				<summary-links :confirmation-data="confirmationData"/>
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import MembershipSummary from '@/components/pages/membership_confirmation/MembershipSummary.vue';
import SummaryLinks from '@/components/pages/membership_confirmation/SummaryLinks.vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'MembershipConfirmation',
	components: {
		MembershipSummary,
		SummaryLinks,
	},
	props: [
		'confirmationData',
	],
	computed: {
		addressType: {
			get(): number {
				return this.confirmationData.address.salutation === 'Firma' ? AddressTypeModel.COMPANY : AddressTypeModel.PERSON;
			},
		},
	},
} );
</script>

<style lang="scss"></style>
