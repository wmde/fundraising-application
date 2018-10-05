<template>
	<div id="bank-data" @click="justAnExample">
		<span>Hey</span>
		<!-- TODO: Use IBAN and stuff -->
		<span>{{ formContent.firstName }}</span>
	</div>
</template>

<script>
	export default {
		name: 'bank-data',
		props: ['formContent', 'justAnExample'] // TODO use different props and use defaults for the right store cross-section
		// add methods if needed (e.g. for passing values from input fields), see https://www.npmjs.com/package/redux-vue
	}

</script>
