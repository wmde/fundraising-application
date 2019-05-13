<template>
	<aside class="column is-one-third is-half-desktop">
		<p>{{ $t('sidebar_data_encryption_notice') }}</p>
		<br>
		<p><strong>{{ $t('sidebar_getintouch_headline') }}</strong></p>
		<p>{{ $t('sidebar_getintouch_text') }}</p>
		<p>{{ $t('sidebar_getintouch_text_phone') }}</p>
	</aside>
</template>

<script>
export default {
	name: 'Sidebar',
};
</script>

<style lang="scss">
	@import "../../scss/variables";
	.phone-number {
		color: $fun-color-dark;
		cursor: default;
		&:hover {
			color: $fun-color-dark;
		}
	}
</style>
