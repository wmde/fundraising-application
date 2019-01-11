<template>
<div>
	<select class="no-outline salutation" id="salutation" name="salutation"
			data-jcf='{"wrapNative": false,  "wrapNativeOnMobile": true  }'>
		<option hidden class="hideme" value="">{$ 'salutation_label' | trans $}</option>
		<option value="Herr">{$ 'salutation_option_mr' | trans $}</option>
		<option value="Frau">{$ 'salutation_option_mrs' | trans $}</option>
	</select>
	<select class="no-outline personal-title" id="title" name="title"
			data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true}'>
		<option value="">{$ 'title_option_none' | trans $}</option>
		<option value="Dr.">Dr.</option>
		<option value="Prof.">Prof.</option>
		<option value="Prof. Dr.">Prof. Dr.</option>
	</select>
	<span class="error-text">{$ 'form_salutation_error' | trans $}</span>
	<name></name>
	<postal></postal>
	<email></email>
</div>
</template>

<script lang="ts">
  	import Vue from 'vue';
	import Name from './components/Name.vue';
	import Email from './components/Email.vue';
	import Postal from './components/Postal.vue';

    export default Vue.extend ({
		name: 'addressForm',
		components: {
			Name,
			Email,
			Postal
		},
		props: ['addressType']
	});
</script>
