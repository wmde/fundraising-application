<template>
	<div>
		<header>
			<Header :page-identifier="pageIdentifier" :image-path="imagePath"></Header>
		</header>
		<main id="app" class="section">
			<div class="container">
				<Content>
					<slot></slot>
					<template v-slot:sidebar>
						<slot name="sidebar"></slot>
					</template>
				</Content>
			</div>
		</main>
		<footer>
			<Footer :image-path=imagePath></Footer>
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
	props: [ 'imagePath', 'pageIdentifier' ],
} );
</script>

<style lang="scss">
	@import "../scss/variables";
	@import "../scss/classes";

	// Import Bulma's core
	@import "~bulma/sass/utilities/_all";

	// Overriding Bulma variables
	@import "../scss/overrides";

	// Import Bulma and Buefy styles
	@import "~bulma";
	@import "~buefy/src/scss/buefy";
	@import "~bulma-helpers/sass/helpers/spacing/margin-padding";
</style>
