<template>
	<div class="column is-full">
		<h1 class="title">{{ $t( 'error_page_header' ) }}</h1>
		<p>
			<span v-html="$t( 'error_page' )"></span>
		</p>

		<pre v-if="errorMessage">
			{{ errorMessage }}
		</pre>

		<p>
			<a href="/"><span v-html="$t( 'error_pages_return_to_donation' )"></span></a>
		</p>
	</div>
</template>

<script>
export default {
	name: 'Error',
	props: [ 'errorMessage' ],
};
</script>
