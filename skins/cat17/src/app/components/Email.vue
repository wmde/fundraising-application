<template>
	<div class="field-grp field-email">
		<input type="email" id="email" placeholder="{$ 'email_label' | trans $}" data-pattern="^[^@]+@.+$">
		<label for="email" class="sr-only">{$ 'email_label' | trans $}</label>
		<span class="error-text">{$ 'form_email_error' | trans $}</span>
	</div>
</template>

<script lang="ts">
    import Vue from "vue";
	export default Vue.extend({
		name: "email"
	});
</script>