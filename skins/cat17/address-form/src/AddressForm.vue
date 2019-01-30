<template>
<div id="addressForm" class="container">
	<h2>{{ messages.address_form_title }}</h2>
	<h5>Die bisherigen Daten sind schon eingetragen, bitte passen Sie diese an. {{ messages.address_form_subtitle }}</h5>
	<div class="row">
		<div class="col-xs-12 col-md-9">
			<div class="form-shadow-wrap">
				<name :show-error="showError" :form-data="formData" :validate-input="validateInput" :is-company="isCompany" :messages="messages"></name>
				<postal :show-error="showError" :form-data="formData" :validate-input="validateInput" :messages="messages" :countries="countries"></postal>
			</div>
		</div>
		<div class="col-xs-12 col-md-3 submit">
			<input type="submit" value="Kontaktdaten ändern" class="btn btn-address-change" @click="validateForm()">
			<span class="info-text">Im folgenden Schritt können Sie die Daten noch einmal prüfen.</span>
		</div>
        <input type="hidden" name="addressType" v-model="formData[9].value">
	</div>
</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import Name from './components/Name.vue';
	import Postal from './components/Postal.vue';
	import {inputField, ValidationResult} from './types';

	export default Vue.extend ( {
		name: 'addressForm',
		components: {
			Name,
			Postal
		},
		data: function() {
			return {
				formData: [
					{
						name: 'salutation',
						value: '',
						pattern: '',
						optionalField: false
					},
					{
						name: 'title',
						value: '',
						pattern: '',
						optionalField: true
					},
					{
						name: 'companyName',
						value: '',
						pattern: '^.+$',
						optionalField: !this.$props.isCompany
					},
					{
						name: 'firstName',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					{
						name: 'lastName',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					{
						name: 'street',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					{
						name: 'city',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					{
						name: 'postCode',
						value: '',
						pattern: '[0-9]{4,5}$',
						optionalField: false
					},
					{
						name: 'country',
						value: 'DE',
						pattern: '',
						optionalField: false
					},
					 {
						name: 'addressType',
						value: this.$props.isCompany ? 'firma' : 'person',
						pattern: '',
						optionalField: false
					}
				],
				showError: {
					companyName: false,
					firstName: false,
					lastName: false,
					street: false,
					city: false,
					postCode: false
				}
			}
		},
		props: {
			addressToken: String,
			isCompany: Boolean,
			messages: Object,
			validateAddressURL: String,
			countries: {
				type: Array,
				default: function() {
					return [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];
				}
			}
		},
		methods: {
			validateForm() {
				this.$store.dispatch('storeAddressFields', {
					validateAddressURL: this.validateAddressURL,
					formData: this.formData
				}).then( resp => {
					if (!this.$store.getters.allFieldsAreValid) {
						this.$store.getters.invalidFields.forEach(invalidField => {
							this.error(invalidField);
						});
					}
				});
			},
			validateInput(formData: Array<inputField>, fieldName: string) {
				let field = formData.filter( data => data.name === fieldName )[0];
				this.$store.dispatch( 'validateInput', field );
				this.error(fieldName);
			},
			error(fieldName: string) {
				this.showError[fieldName] = !this.$store.getters.validity(fieldName);
			}
		}
	} );
</script>
