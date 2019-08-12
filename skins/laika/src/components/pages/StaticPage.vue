<template>
<div class="column is-full">
	<h2 class="title is-size-2">{{ pageTitle }}</h2>
	<div v-html="partialContentFirstHalf" class="has-margin-top-18 is-full static-content"></div>
	<privacy-protection v-if="pageId === 'privacy_protection'"></privacy-protection>
	<div v-html="partialContentSecondHalf" class="has-margin-top-18 is-full static-content"></div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
import PrivacyProtection from '@/components/pages/PrivacyProtection.vue';

export default Vue.extend( {
	name: 'StaticPage',
	components: {
		PrivacyProtection,
	},
	data: function () {
		const splitContent = this.$props.pageContent.split( '<!-- placeholder_matomo -->' );
		return {
			partialContentFirstHalf: splitContent[ 0 ],
			partialContentSecondHalf: splitContent[ 1 ],
		};
	},
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
