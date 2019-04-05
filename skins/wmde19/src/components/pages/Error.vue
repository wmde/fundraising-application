<template>
	<main>
		<!-- TODO create small i18n vue function to use instead of messages variable -->
		<h1>{{ messages.error_page_header }}</h1>

		<p>
			<span v-html="messages.error_page"></span>
		</p>

		<pre v-if="errorMessage">
			{{ errorMessage }}
		</pre>

		<p>
			<a href="/"><span v-html="messages.error_pages_return_to_donation"></span></a>
		</p>
	</main>

</template>

<script>
export default {
	name: 'Error',
	props: [ 'messages', 'errorMessage' ],
};
</script>

