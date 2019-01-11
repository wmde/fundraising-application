<template>
<div>
	<div class="field-grp field-firstname">
		<input type="text" id="first-name" name="firstName" placeholder="{$ 'firstname_label' | trans $}" data-pattern="^.+$">
		<label for="first-name" class="sr-only">{$ 'firstname_label' | trans $}</label>
		<span class="error-text">{$ 'form_firstname_error' | trans $}</span>
	</div>
	<div class="field-grp field-lastname">
		<input type="text" id="last-name" name="lastName" placeholder="{$ 'lastname_label' | trans $}" data-pattern="^.+$">
		<label for="last-name" class="sr-only">{$ 'lastname_label' | trans $}</label>
		<span class="error-text">{$ 'form_lastname_error' | trans $}</span>
	</div>
</div>
</template>

<script lang="ts">
    import Vue from "vue";
	export default Vue.extend({
		name: "name"
	});
</script>