<template>
	<div class="donation-links">
		<a href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a>
		<a :href="confirmationData.urls.cancelMembership">{{ $t( 'membership_confirmation_cancel_button' ) }}</a>
	</div>
</template>
<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'SummaryLinks',
	props: [
		'confirmationData',
	],
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
