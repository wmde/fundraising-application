<template>
	<div class="column is-full">
		<h1 class="title">{{ $t( 'system_message_page_header' ) }}</h1>
		<span>{{ $t( errorData.message ) }}</span>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'SystemMessage',
	props: [
		'errorData',
	],
} );
</script>
