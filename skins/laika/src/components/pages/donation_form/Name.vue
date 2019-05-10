<template>
<div class="row">
	<div v-if="isCompany" v-bind:class="[{ invalid: showError.companyName }]">
		<label for="company-name">{{ $t( 'companyname_label' ) }}</label>
		<input type="text"
				id="company-name"
				name="company"
				:placeholder="$t( 'companyname_label' )"
				v-model="formData.companyName.value"
				@blur="validateInput(formData, 'companyName')">
		<span v-if="showError.companyName" class="error-text">{{ $t( 'form_companyname_error' )  }}</span>
	</div>
	<div v-else>
		<div class="align-block">
			<label for="salutation">{{ $t( 'salutation_label' ) }}</label>
			<select class=""
					id="salutation"
					v-model="formData.salutation.value"
					name="salutation"
					@blur="validateInput(formData, 'salutation')">
				<option hidden class="hideme" value="">{{ $t( 'salutation_label' ) }}</option>
				<option value="Herr">{{ $t( 'salutation_option_mr' ) }}</option>
				<option value="Frau">{{ $t( 'salutation_option_mrs' ) }}</option>
			</select>
		</div>
		<div class="align-block">
			<label for="title">{{ $t( 'academic_title_label' ) }}</label>
			<select class=""
					id="title"
					v-model="formData.title.value"
					name="title"
					@blur="validateInput(formData, 'title')">
				<option value="">{{ $t( 'title_option_none' ) }}</option>
				<option value="Dr.">Dr.</option>
				<option value="Prof.">Prof.</option>
				<option value="Prof. Dr.">Prof. Dr.</option>
			</select>
		</div>
		<span v-if="showError.salutation" class="error-text"> {{ $t( 'form_salutation_error' ) }}</span>
		<div v-bind:class="[{ invalid: showError.firstName }]">
			<label for="first-name">{{ $t( 'firstname_label' ) }}</label>
			<input type="text"
					id="first-name"
					v-model="formData.firstName.value"
					name="firstName"
					:placeholder="$t( 'firstname_label' )"
					@blur="validateInput(formData, 'firstName')">
			<span v-if="showError.firstName" class="error-text">{{ $t( 'form_firstname_error' ) }}</span>
		</div>
		<div v-bind:class="[{ invalid: showError.lastName }]">
			<label for="last-name">{{ $t( 'lastname_label' ) }}</label>
			<input type="text"
					id="last-name"
					v-model="formData.lastName.value"
					name="lastName"
					:placeholder="$t( 'lastname_label' )"
					@blur="validateInput(formData, 'lastName')">
			<span v-if="showError.lastName" class="error-text">{{ $t( 'form_lastname_error' ) }}</span>
		</div>
	</div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
export default Vue.extend( {
	name: 'name',
	props: [ 'showError', 'formData', 'validateInput', 'isCompany' ],
} );
</script>
