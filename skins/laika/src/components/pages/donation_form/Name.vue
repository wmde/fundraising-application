<template>
<div>
	<div v-if="addressType === AddressTypeModel.PERSON">
		<fieldset class="has-margin-top-36">
			<legend class="subtitle">{{ $t( 'donation_form_salutation_label' ) }}</legend>
			<div>
				<b-radio type="radio"
						id="salutation-mr"
						name="salutationInternal"
						:native-value="$t( 'donation_form_salutation_option_mr' ) "
						v-model="formData.salutation.value"
						@input="$emit('field-changed', 'salutation')">
					{{ $t( 'donation_form_salutation_option_mr' ) }}
				</b-radio>
			</div>
			<div>
				<b-radio type="radio"
						id="salutation-mrs"
						name="salutationInternal"
						:native-value="$t( 'donation_form_salutation_option_mrs' ) "
						v-model="formData.salutation.value"
						@input="$emit('field-changed', 'salutation')">
					{{ $t( 'donation_form_salutation_option_mrs' ) }}
				</b-radio>
			</div>
		</fieldset>
		<div class="has-margin-top-36">
			<label for="title" class="subtitle">{{ $t( 'donation_form_academic_title_label' ) }}</label>
			<b-select
					class="is-form-input"
					v-model="formData.title.value"
					id="title"
					name="title"
					@input="$emit('field-changed', 'title')">
				<option value="">{{ $t( 'donation_form_academic_title_option_none' ) }}</option>
				<option value="Dr.">Dr.</option>
				<option value="Prof.">Prof.</option>
				<option value="Prof. Dr.">Prof. Dr.</option>
			</b-select>
		</div>
		<span v-if="showError.salutation" class="help is-danger"> {{ $t( 'donation_form_salutation_error' ) }}</span>
		<div v-bind:class="[{ 'is-invalid': showError.firstName }]">
			<label for="first-name" class="subtitle has-margin-top-36">{{ $t( 'donation_form_firstname_label' ) }}</label>
			<b-input class="is-medium"
					type="text"
					id="first-name"
					v-model="formData.firstName.value"
					:placeholder="$t( 'donation_form_firstname_placeholder' )"
					@blur="$emit('field-changed', 'firstName')">
			</b-input>
			<span v-if="showError.firstName" class="help is-danger">{{ $t( 'donation_form_firstname_error' ) }}</span>
		</div>
		<div v-bind:class="[{ 'is-invalid': showError.lastName }]">
			<label for="last-name" class="subtitle has-margin-top-36">{{ $t( 'donation_form_lastname_label' ) }}</label>
			<b-input type="text"
					id="last-name"
					v-model="formData.lastName.value"
					:placeholder="$t( 'donation_form_lastname_placeholder' )"
					@blur="$emit('field-changed', 'lastName')">
			</b-input>
			<span v-if="showError.lastName" class="help is-danger">{{ $t( 'donation_form_lastname_error' ) }}</span>
		</div>
	</div>
	<div v-else-if="addressType === AddressTypeModel.COMPANY" v-bind:class="[{ 'is-invalid': showError.companyName }]">
		<label for="company-name" class="subtitle has-margin-top-36">{{ $t( 'donation_form_companyname_label' ) }}</label>
		<b-input type="text"
				id="company-name"
				:placeholder="$t( 'donation_form_companyname_placeholder' )"
				v-model="formData.companyName.value"
				@blur="$emit('field-changed', 'companyName')">
		</b-input>
		<span v-if="showError.companyName" class="help is-danger">{{ $t( 'donation_form_companyname_error' )  }}</span>
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
