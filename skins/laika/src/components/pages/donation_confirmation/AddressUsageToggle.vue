<template>
    <div class="address-usage-toggle" :class="{ 'open': contentOpen }">
		<a href="#" v-on:click="toggleContent" class="address-usage-toggle-link">{{ $t('donation_confirmation_address_usage_link') }}</a>
		<div class="address-usage-toggle-content">{{ $t('donation_confirmation_address_usage_content') }}</div>
    </div>
</template>

<script>
import Vue from 'vue';

export default Vue.extend( {
	name: 'AddressUsageToggle',
	data: function () {
		return {
			contentOpen: false,
		};
	},
	methods: {
		toggleContent: function () {
			this.$data.contentOpen = !this.$data.contentOpen;
		},
	},
} );
</script>
