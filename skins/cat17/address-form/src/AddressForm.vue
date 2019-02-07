<template>
<div id="addressForm" class="container">
	<h2>{{ messages.address_form_title }}</h2>
	<h5>{{ messages.address_form_subtitle }}</h5>
	<div class="row">
        <form ref="form" :action="updateAddressURL + addressToken" method="post">
            <div class="col-xs-12 col-md-9">
                <div class="form-shadow-wrap">
                    <name :show-error="showError" :form-data="formData" :validate-input="validateInput" :is-company="isCompany" :messages="messages"></name>
                    <postal :show-error="showError" :form-data="formData" :validate-input="validateInput" :messages="messages" :countries="countries"></postal>
                </div>
            </div>
            <div class="col-xs-12 col-md-3 submit">
                <input type="submit" value="Kontaktdaten ändern" class="btn btn-address-change" @click.prevent="validateForm()">
            </div>
            <input type="hidden" name="addressType" v-model="formData.addressType.value">
        </form>
	</div>
</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	import Name from './components/Name.vue';
	import Postal from './components/Postal.vue';
	import {InputField, ValidationResult, FormData, Transport} from './types';

	export default Vue.extend ( {
		name: 'addressForm',
		components: {
			Name,
			Postal
		},
		data: function() {
			return {
				formData: {
					salutation: {
						name: 'salutation',
						value: '',
						pattern: '^(Herr|Frau)$',
						optionalField: this.$props.isCompany
					},
                    title: {
                        name: 'title',
                        value: '',
                        pattern: '',
                        optionalField: true
                    },
                    companyName: {
                            name: 'companyName',
                            value: '',
                            pattern: '^.+$',
                            optionalField: !this.$props.isCompany
                        },
					firstName: {
						name: 'firstName',
						value: '',
						pattern: '^.+$',
						optionalField: this.$props.isCompany
					},
					lastName: {
						name: 'lastName',
						value: '',
						pattern: '^.+$',
						optionalField: this.$props.isCompany
					},
					street: {
						name: 'street',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					city: {
						name: 'city',
						value: '',
						pattern: '^.+$',
						optionalField: false
					},
					postcode: {
						name: 'postcode',
						value: '',
						pattern: '[0-9]{4,5}$',
						optionalField: false
					},
					country: {
						name: 'country',
						value: 'DE',
						pattern: '',
						optionalField: false
					},
					addressType: {
						name: 'addressType',
						value: this.$props.isCompany ? 'firma' : 'person',
						pattern: '',
						optionalField: false
					}
                },
				showError: {
                    salutation: false,
					companyName: false,
					firstName: false,
					lastName: false,
					street: false,
					city: false,
					postcode: false
				}
			}
		},
		props: {
            transport: {
                type: Object as () => Transport
            },
			addressToken: String,
			isCompany: Boolean,
			messages: Object,
            validateAddressURL: String,
            updateAddressURL: String,
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
                    transport: this.$props.transport,
					validateAddressURL: this.$props.validateAddressURL,
					formData: this.formData
				}).then( resp => {
					if (!this.$store.getters.allFieldsAreValid) {
						this.$store.getters.invalidFields.forEach(invalidField => {
							this.error(invalidField);
						});
                    }
                    else {
                        this.$refs.form.submit();
                    }
				});
			},
			validateInput(formData: FormData, fieldName: string) {
				this.$store.dispatch( 'validateInput', formData[fieldName] );
				this.error(fieldName);
			},
			error(fieldName: string) {
				this.showError[fieldName] = !this.$store.getters.validity(fieldName);
			}
		}
	} );
</script>
