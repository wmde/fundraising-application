<template>
	<div class="container">
		<div class="columns has-margin-top-0">
			<div v-bind:class="[isFullWidth ? 'is-full' : 'is-two-thirds is-half-desktop column has-background-bright']">
				<slot></slot>
			</div>
			<slot name="sidebar"></slot>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Content',
	props: [ 'isFullWidth' ],
};
</script>
