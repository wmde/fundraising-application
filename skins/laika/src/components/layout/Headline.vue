<template>
	<div class="columns has-margin-top-18 has-margin-bottom-18 intro-content">
		<div v-bind:class="[isFullWidth ? 'is-full' : 'is-two-thirds', 'column', 'has-padding-0' ]">
			<span>{{ $t('header_headline_text') }}</span>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Headline',
	props: [ 'isFullWidth' ],
};
</script>
