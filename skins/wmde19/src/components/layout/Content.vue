<template>
	<div class="container has-margin-top-108 has-margin-bottom-54">
		<div class="columns">
			<main class="column is-two-thirds has-background-bright">
				<slot></slot>
			</main>
			<slot name="sidebar"></slot>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Content',
};
</script>
