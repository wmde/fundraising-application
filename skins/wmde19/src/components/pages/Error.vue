<template>
	<div class="column is-full">
		<!-- TODO create small i18n vue function to use instead of messages variable -->
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

		<p class="throw-me-away-later-test">
			<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
			Yes this is indeed a test that will be removed later!!!<br><br>
		</p>
	</div>
</template>

<script>
export default {
	name: 'Error',
	props: [ 'errorMessage' ],
};
</script>
