<template>
	<div class="membership-cancellation-confirmation">
		<h1 class="title">{{ $t('membership_cancellation_title') }}</h1>
		<span v-html="$t( 'membership_cancellation_text', { membership_id: cancellationData.membershipId, } )"></span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'MembershipCancellation',
	props: [
		'cancellationData',
	],
} );
</script>
