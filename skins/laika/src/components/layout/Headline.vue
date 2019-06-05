<template>
	<div class="columns has-margin-top-36 has-margin-bottom-36 intro-content">
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
