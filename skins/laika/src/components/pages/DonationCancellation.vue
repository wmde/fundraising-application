<template>
	<div class="column is-full">
		<h1 class="title">{{ $t('donation_cancellation_title') }}</h1>
		<span v-html="$t( 'donation_cancellation_text', { donation_id: cancellationData.donationId, } )"></span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'DonationCancellation',
	props: [
		'cancellationData',
	],
} );
</script>
