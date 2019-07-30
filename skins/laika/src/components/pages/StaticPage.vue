<template>
<!--set matomo_opt_out-->
<div class="column is-full">
	<h2 class="title is-size-2">{{ pageTitle }}</h2>
	<div v-html="pageContent" class="has-margin-top-18 column is-full"></div>
</div>
<!--{$ web_content("pages/#{page_id}") | replace({: matomo_opt_out}) | raw $}-->

</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'StaticPage',
	props: {
		pageTitle: String,
		pageContent: String,
	},
} );
</script>
