<template>
	<section id="app" class="section">
		<div class="container">
			<Header></Header>
			<Content>
				<slot></slot>
			</Content>
			<Footer></Footer>
		</div>
	</section>
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
} );
</script>
