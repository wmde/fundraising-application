<template>
	<name></name>
	<postal></postal>
	<email></email>
</template>

<script>
	import Name from '../app/components/Name.vue';
	import Email from '../app/components/Email.vue';
	import Postal from '../app/components/Postal.vue';

	export default {
		name: 'addressForm',
		components: {
			Name,
			Email,
			Postal
		},
		props: [ 'addressType' ],
	};
</script>
