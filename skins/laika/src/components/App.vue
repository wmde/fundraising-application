<template>
	<div>
		<header>
			<Header :page-identifier="pageIdentifier" :assets-path="assetsPath"></Header>
		</header>
		<main id="app" class="main-wrapper">
			<div class="container">
				<div class="columns has-margin-top-36 has-margin-bottom-36 intro-content">
					<div class="column is-two-thirds is-half-desktop has-padding-0">
						<span>{{ $t('wikimedia_tagline') }}</span>
					</div>
				</div>
			</div>
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
			<Footer :assets-path="assetsPath"></Footer>
		</footer>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import Buefy from 'buefy';
import Header from '@/components/layout/Header.vue';
import Content from '@/components/layout/Content.vue';
import Footer from '@/components/layout/Footer.vue';

Vue.use( Buefy );
export default Vue.extend( {
	name: 'app',
	components: {
		Header,
		Content,
		Footer,
	},
	props: [ 'assetsPath', 'pageIdentifier' ],
} );
</script>

<style lang="scss">
	@import "../scss/custom";

	// Import Bulma's core
	@import "~bulma/sass/utilities/_all";

	// Import Bulma and Buefy styles
	@import "~bulma";
	@import "~buefy/src/scss/buefy";
	@import "~bulma-helpers/sass/helpers/spacing/margin-padding";

	.main-wrapper {
		padding: $navbar-height 18px;
	}

	@include until($desktop) {
		.intro-content {
			margin: 18px !important;
			padding: 18px 5%;
		}
	}
</style>
