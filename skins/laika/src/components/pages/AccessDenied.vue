<template>
	<div class="column is-full">
		<h1 class="title">{{ $t( 'access_denied_header' ) }}</h1>
		<span>{{ $t( errorData.message ? errorData.message : 'access_denied' ) }}</span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'AccessDenied',
	props: [
		'errorData',
	],
} );
</script>
