<template>
	<div>
		<header>
			<Header :page-identifier="pageIdentifier" :assets-path="assetsPath"></Header>
		</header>
		<main id="app" class="main-wrapper">
			<div class="container">
				<Headline :is-full-width="isFullWidth"></Headline>
			</div>
			<div class="container">
				<Content :is-full-width="isFullWidth">
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
import Headline from '@/components/layout/Headline.vue';
import Content from '@/components/layout/Content.vue';
import Footer from '@/components/layout/Footer.vue';

Vue.use( Buefy );
export default Vue.extend( {
	name: 'app',
	components: {
		Header,
		Headline,
		Content,
		Footer,
	},
	props: {
		assetsPath: {
			type: String,
		},
		pageIdentifier: {
			type: String,
		},
		isFullWidth: {
			type: Boolean,
			default: false,
		},
	},
} );
</script>

<style lang="scss">
	@import "../scss/custom";
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
