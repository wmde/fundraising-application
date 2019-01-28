<template>
<div class="row">
	<div v-if="isCompany" class="field-grp field-company col-xs-12 col-md-6">
		<label for="company-name">{{ messages.companyname_label }}</label>
		<input type="text"
				id="company-name"
				name="companyName"
				:placeholder="messages.companyname_label"
				@blur="validateInput(formData, 'companyName')">
		<span v-if="error('companyName')" class="error-text">{{ messages.form_companyname_error }}</span>
	</div>
	<div v-else>
		<div class="align-block">
			<label for="salutation">{{ messages.salutation_label }}</label>
			<select class="salutation col-xs-12 col-md-6"
					id="salutation"
					name="salutation"
					data-jcf='{"wrapNative": false,  "wrapNativeOnMobile": true  }'
					@blur="validateInput(formData, 'salutation')">
				<option hidden class="hideme" value="">{{ messages.salutation_label }}</option>
				<option value="Herr">{{ messages.salutation_option_mr }}</option>
				<option value="Frau">{{ messages.salutation_option_mrs }}</option>
			</select>
		</div>
		<div class="align-block">
			<label for="title">{{ messages.academic_title_label }}</label>
			<select class="personal-title col-xs-12 col-md-6"
					id="title"
					name="title"
					data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true}'
					@blur="validateInput(formData, 'title')">
				<option value="">{{ messages.title_option_none }}</option>
				<option value="Dr.">Dr.</option>
				<option value="Prof.">Prof.</option>
				<option value="Prof. Dr.">Prof. Dr.</option>
			</select>
		</div>
		<span v-if="error('salutation')" class="error-text"> {{ messages.form_salutation_error }}</span>
		<div class="field-grp field-firstname col-xs-12 col-md-6">
			<label for="first-name">{{ messages.firstname_label }}</label>
			<input type="text"
					id="first-name"
					name="firstName"
					:placeholder="messages.firstname_label"
					@blur="validateInput(formData, 'firstName')">
			<span v-if="error('firstName')" class="error-text">{{ messages.form_firstname_error }}</span>
		</div>
		<div class="field-grp field-lastname col-xs-12 col-md-6">
			<label for="last-name">{{ messages.lastname_label }}</label>
			<input type="text"
					id="last-name"
					name="lastName"
					:placeholder="messages.lastname_label"
					@blur="validateInput(formData, 'lastName')">
			<span v-if="error('lastName')" class="error-text">{{ messages.form_lastname_error }}</span>

		</div>
	</div>
</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	export default Vue.extend( {
		name: 'name',
		props: [ 'formData', 'error', 'validateInput', 'isCompany', 'messages' ]
	} );
</script>
