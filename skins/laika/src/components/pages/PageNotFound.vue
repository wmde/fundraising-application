<template>
	<div class="column is-full">
		<h1 class="title">{{ $t( 'page_not_found_header' ) }}</h1>
		<span v-html="$t( 'page_not_found' )"></span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'PageNotFound',
} );
</script>
