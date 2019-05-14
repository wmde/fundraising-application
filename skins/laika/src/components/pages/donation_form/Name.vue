<template>
<div>
	<div v-if="addressType === AddressTypeModel.COMPANY" v-bind:class="[{ invalid: showError.companyName }]">
		<label for="company-name">{{ $t( 'companyname_label' ) }}</label>
		<input type="text"
				id="company-name"
				name="company"
				:placeholder="$t( 'companyname_label' )"
				v-model="formData.companyName.value"
				@blur="validateInput(formData, 'companyName')">
		<span v-if="showError.companyName" class="error-text">{{ $t( 'form_companyname_error' )  }}</span>
	</div>
	<div v-else-if="addressType === AddressTypeModel.PERSON">
		<label for="salutation">{{ $t( 'salutation_label' ) }}</label>
		<div>
			<input type="radio"
					id="salutation-mr"
					name="salutation"
					v-model="formData.salutation.value"
					@blur="validateInput(formData, 'salutation')">
			<label for="salutation-mr">
				<span>{{ $t( 'salutation_option_mr' ) }}</span>
			</label>
		</div>
		<div>
			<input type="radio"
					id="salutation-mrs"
					name="salutation"
					v-model="formData.salutation.value"
					@blur="validateInput(formData, 'salutation')">
			<label for="salutation-mr">
				<span>{{ $t( 'salutation_option_mrs' ) }}</span>
			</label>
		</div>
		<div>
			<input type="radio"
					id="salutation-family"
					name="salutation"
					v-model="formData.salutation.value"
					@blur="validateInput(formData, 'salutation')">
			<label for="salutation-family">
				<span>{{ $t( 'salutation_option_family' ) }}</span>
			</label>
		</div>
		<div>
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
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { AddressValidity, FormData } from '@/view_models/Address';

export default Vue.extend( {
	name: 'name',
	props: {
		showError: Object as () => AddressValidity,
		formData: Object as () => FormData,
		validateInput: Function,
		addressType: Number as () => AddressTypeModel,
	},
	computed: {
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
	},
} );
</script>
