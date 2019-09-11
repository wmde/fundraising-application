<template>
	<div class="columns content-wrapper">
		<div v-bind:class="[isFullWidth ? 'column is-full' : 'is-two-thirds column has-background-bright']">
			<slot></slot>
		</div>
		<slot name="sidebar"></slot>
	</div>
</template>

<script>
export default {
	name: 'Content',
	props: [ 'isFullWidth' ],
};
</script>

<style lang="scss">
	@import "../../scss/custom";
	.columns.content-wrapper {
		margin: 0 -12px;
	}
</style>
