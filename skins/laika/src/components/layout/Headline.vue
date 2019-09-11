<template>
	<div class="columns intro-content">
		<div v-bind:class="[isFullWidth ? 'is-full' : 'is-two-thirds', 'column' ]">
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
<style lang="scss">
	@import "../../scss/custom";
	.intro-content {
		.is-two-thirds.column {
			margin: 18px 0;
			@include from($tablet) {
				margin: 0px -28px;
			}
		}
		.is-full.column {
			margin: 18px 0;
			@include from($tablet) {
				margin: 18px 0;
			}
		}
	}
</style>
