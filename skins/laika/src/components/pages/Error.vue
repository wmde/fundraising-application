<template>
	<div class="error-page">
		<h1 class="title">{{ $t( 'error_page_header' ) }}</h1>
		<p>
			<span v-html="$t( 'error_page' )"></span>
		</p>

		<pre v-if="errorMessage">
			{{ errorMessage }}
		</pre>
		<div v-if="errorMessage" style="background-color: whitesmoke;font-family: monospace;white-space: pre; overflow: scroll; padding: 1.25rem 1.5rem;">
			<div v-for="(trace, idx) in errorTrace" :key="idx">
				<span>{{idx}} - </span>
				<span v-if="trace.class">{{ trace.class }}{{ trace.type }}{{ trace.function }}</span>
				<span v-else>{{ trace.function }}</span>
				-- <a :href="trace.file.replace(/^.*src\//, 'https://github.com/wmde/FundraisingFrontend/tree/master/src/')">
					{{ trace.file.replace(/^.*(src\/)/, '$1' ) }}:{{trace.line}}
				</a>
			</div>
		</div>

		<p :class="{ 'has-margin-top-18' : !errorMessage }">
			<a href="/"><span v-html="$t( 'error_pages_return_to_donation' )"></span></a>
		</p>
	</div>
</template>

<script>
export default {
	name: 'Error',
	props: [ 'errorMessage', 'errorTrace' ],
};
</script>
