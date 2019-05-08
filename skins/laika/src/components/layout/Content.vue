<template>
	<div class="container">
		<div class="columns has-margin-top-0">
			<main class="column is-two-thirds is-half-desktop has-background-bright">
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
