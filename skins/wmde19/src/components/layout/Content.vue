<template>
	<div class="container has-margin-top-112 has-margin-bottom-48">
		<div class="columns">
			<main class="column is-two-thirds has-background-white">
				<slot></slot>
			</main>
			<aside class="column is-one-third">
				<p>Verschlüsselte Datenübertragung</p>
				<br>
				<p><strong>Frage zum Spenden?</strong></p>
				<p>Rufen Sie uns an oder nutzen Sie das Kontakformular</p>
				<p><a class="phone-number" href="tel:0049030219158260">030 / 21 91 58 26 - 0</a></p>
			</aside>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Content',
};
</script>

<style lang="scss">
	@import "../../scss/variables";
	.phone-number {
		color: $fun-color-basic-black;
		cursor: default;
		&:hover {
			color: $fun-color-basic-black;
		}
	}
</style>
