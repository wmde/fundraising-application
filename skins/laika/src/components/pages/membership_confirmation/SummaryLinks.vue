<template>
	<div class="donation-links is-hidden-print">
		<ul>
			<li><a href="javascript:window.print()">{{ $t( 'donation_confirmation_print_confirmation' ) }}</a></li>
			<li><a :href="confirmationData.urls.cancelMembership">{{ $t( 'membership_confirmation_cancel_button' ) }}</a></li>
		</ul>
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
</style>
