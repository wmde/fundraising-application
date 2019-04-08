<template>
	<div>
		<header>
			<Header :imagePath=imagePath></Header>
		</header>
		<main id="app" class="section">
			<div class="container">
				<Content>
					<slot></slot>
				</Content>
			</div>
		</main>
		<footer>
			<Footer :imagePath=imagePath></Footer>
		</footer>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import Header from './layout/Header.vue';
import Content from './layout/Content.vue';
import Footer from './layout/Footer.vue';

export default Vue.extend( {
	name: 'app',
	components: {
		Header,
		Content,
		Footer,
	},
	props: [ 'imagePath' ],
} );
</script>

<style lang="scss">
	@import "../scss/variables";

	// Import Bulma's core
	@import "~bulma/sass/utilities/_all";

	// Overriding Bulma variables
	@import "../scss/overrides";

	// Import Bulma and Buefy styles
	@import "~bulma";
	@import "~buefy/src/scss/buefy";
	@import "../scss/modifiers";
</style>
