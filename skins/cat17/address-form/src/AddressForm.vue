<template>
<div id="addressForm" class="container">
	<h2>{{ messages.address_form_title }}</h2>
	<h5>Die bisherigen Daten sind schon eingetragen, bitte passen Sie diese an. {{ messages.address_form_subtitle }}</h5>
	<div class="row">
		<div class="col-xs-12 col-md-9">
			<div class="form-shadow-wrap">
				<name :form-data="formData" :validate-input="validateInput" :error="error" :is-company="isCompany" :messages="messages"></name>
				<postal :form-data="formData" :validate-input="validateInput" :error="error" :messages="messages" :countries="countries"></postal>
			</div>
		</div>
		<div class="col-xs-12 col-md-3 submit">
			<input type="submit" value="Kontaktdaten ändern" class="btn btn-address-change" @click="validateForm()">
			<span class="info-text">Im folgenden Schritt können Sie die Daten noch einmal prüfen.</span>
		</div>
	</div>
</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import Name from './components/Name.vue';
	import Postal from './components/Postal.vue';

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
					value: 'Kein Titel',
					pattern: '',
					optionalField: true
				},
				{
					name: 'companyName',
					value: '',
					pattern: '^.+$',
					optionalField: false
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
					value: '',
					pattern: '',
					optionalField: false
				}
			]
			}
		},
		props: {
			addressToken: String,
			isCompany: Boolean,
			messages: Object,
			initAddressForm: Object,
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
					validateAddressURL: this.initAddressForm.validateAddressURL,
					formData: this.formData
				});
			},
			validateInput(formData, fieldName: string) {
				let field = formData.filter( data => data.name === fieldName )[0];
				this.$store.dispatch( 'validateInput', field );
			},
			error(fieldName: string) {
				return this.$store.getters.validity( fieldName );
			}
		}
	} );
</script>
