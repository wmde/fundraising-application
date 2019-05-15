<template>
<div>
	<div v-if="addressType === AddressTypeModel.PERSON">
		<fieldset>
			<legend class="subtitle has-margin-top-36">{{ $t( 'salutation_label' ) }}</legend>
			<div>
				<b-radio type="radio"
						id="salutation-mr"
						name="salutation"
						:native-value="$t( 'salutation_option_mr' ) "
						v-model="formData.salutation.value"
						@blur="validateInput(formData, 'salutation')">
					{{ $t( 'salutation_option_mr' ) }}
				</b-radio>
			</div>
			<div>
				<b-radio type="radio"
						id="salutation-mrs"
						name="salutation"
						:native-value="$t( 'salutation_option_mrs' ) "
						v-model="formData.salutation.value"
						@blur="validateInput(formData, 'salutation')">
					{{ $t( 'salutation_option_mrs' ) }}
				</b-radio>
			</div>
		</fieldset>
		<div>
			<label for="title" class="subtitle has-margin-top-36">{{ $t( 'academic_title_label' ) }}</label>
			<b-select
					class="is-form-input"
					v-model="formData.title.value"
					id="title"
					name="title"
					@blur="validateInput(formData, 'title')">
				<option value="">{{ $t( 'title_option_none' ) }}</option>
				<option value="Dr.">Dr.</option>
				<option value="Prof.">Prof.</option>
				<option value="Prof. Dr.">Prof. Dr.</option>
			</b-select>
		</div>
		<span v-if="showError.salutation" class="help is-danger"> {{ $t( 'form_salutation_error' ) }}</span>
		<div v-bind:class="[{ 'is-invalid': showError.firstName }]">
			<label for="first-name" class="subtitle has-margin-top-36">{{ $t( 'firstname_label' ) }}</label>
			<b-input class="is-medium"
					type="text"
					id="first-name"
					v-model="formData.firstName.value"
					name="firstName"
					:placeholder="$t( 'firstname_label' )"
					@blur="validateInput(formData, 'firstName')">
			</b-input>
			<span v-if="showError.firstName" class="help is-danger">{{ $t( 'form_firstname_error' ) }}</span>
		</div>
		<div v-bind:class="[{ 'is-invalid': showError.lastName }]">
			<label for="last-name" class="subtitle has-margin-top-36">{{ $t( 'lastname_label' ) }}</label>
			<b-input type="text"
					id="last-name"
					v-model="formData.lastName.value"
					name="lastName"
					:placeholder="$t( 'lastname_label' )"
					@blur="validateInput(formData, 'lastName')">
			</b-input>
			<span v-if="showError.lastName" class="help is-danger">{{ $t( 'form_lastname_error' ) }}</span>
		</div>
	</div>
	<div v-else-if="addressType === AddressTypeModel.COMPANY" v-bind:class="[{ 'is-invalid': showError.companyName }]">
		<label for="company-name" class="subtitle has-margin-top-36">{{ $t( 'companyname_label' ) }}</label>
		<b-input type="text"
				id="company-name"
				name="company"
				:placeholder="$t( 'companyname_label' )"
				v-model="formData.companyName.value"
				@blur="validateInput(formData, 'companyName')">
		</b-input>
		<span v-if="showError.companyName" class="help is-danger">{{ $t( 'form_companyname_error' )  }}</span>
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
