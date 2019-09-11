<template>
	<div class="update-address-confirmation">
		<h1 class="title">{{ $t( 'address_change_form_success_title' ) }}</h1>
		<span v-if="donationReceipt === '1'">{{ $t( 'address_change_form_success_label' ) }}</span>
		<span v-else>{{ $t( 'address_change_form_success_optout_label' ) }}</span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'UpdateAddressSuccess',
	props: [
		'donationReceipt',
	],
} );
</script>
