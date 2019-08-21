<template>
<div class="column is-full">
	<h2 class="title is-size-2">{{ pageTitle }}</h2>
	<div v-html="pageContent" class="has-margin-top-18 is-full static-content"></div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'StaticPage',
	props: {
		pageId: String,
		pageTitle: String,
		pageContent: String,
	},
} );
</script>

<style lang="scss">
	@import "../../scss/custom.scss";
	.static-content {
		ol {
			margin-left: 1.5em;
		}
		& > ol {
			padding-left: 10px;
			list-style-type: upper-roman;
		}
		p {
			margin: 0 0 11px;
		}
	}
</style>
