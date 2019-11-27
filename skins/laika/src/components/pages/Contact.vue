<template>
	<div class="contact-form">
		<h1 class="title">{{ $t( 'contact_form_title' ) }}</h1>
		<span class="help is-danger has-padding-bottom-18" v-if="contactData.errors">{{ $t('contact_form_error') }}</span>
		<span class="help is-danger has-padding-bottom-18" v-for="error in contactData.errors">{{ $t( error ) }}</span>
		<form method="post" action="/contact/get-in-touch" v-on:submit.prevent="submit" id="laika-contact" ref="form">
			<fieldset>
				<div>
					<label for="firstname" class="subtitle">
						{{ $t( 'contact_form_firstname_label' ) }}
						<span class="has-text-gray-dark">{{ $t('contact_form_optional') }}</span>
					</label>
					<b-field>
						<b-input type="text"
								id="firstname"
								name="firstname"
								:placeholder="$t( 'form_for_example', { example: $t( 'contact_form_firstname_placeholder' ) } )"
								v-model="formData.firstname.value">
						</b-input>
					</b-field>
					<span v-if="formData.firstname.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_firstname_error' ) }}</span>
				</div>
				<div class="has-margin-top-18">
					<label for="lastname" class="subtitle">
						{{ $t( 'contact_form_lastname_label' ) }}
						<span class="has-text-gray-dark">{{ $t('contact_form_optional') }}</span>
					</label>
					<b-field>
						<b-input type="text"
								id="lastname"
								name="lastname"
								:placeholder="$t( 'form_for_example', { example: $t( 'contact_form_lastname_placeholder' ) } )"
								v-model="formData.lastname.value">
						</b-input>
					</b-field>
					<span v-if="formData.lastname.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_lastname_error' ) }}</span>
				</div>
				<div class="has-margin-top-18">
					<label for="donationNumber" class="subtitle">
						{{ $t( 'contact_form_donation_number_label' ) }}
						<span class="has-text-gray-dark">{{ $t('contact_form_optional') }}</span>
					</label>
					<b-field>
						<b-input type="text"
								id="donationNumber"
								name="donationNumber"
								:placeholder="$t( 'form_for_example', { example: $t( 'contact_form_donation_number_placeholder' ) } )"
								v-model="formData.donationNumber.value">
						</b-input>
					</b-field>
					<span v-if="formData.donationNumber.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_donation_number_error' ) }}</span>
				</div>
			</fieldset>
			<fieldset class="has-margin-top-36">
				<div class="has-margin-top-18">
				<label for="email" class="subtitle">{{ $t( 'contact_form_email_label' ) }}</label>
				<b-field>
					<b-input type="text"
							id="email"
							name="email"
							:placeholder="$t( 'form_for_example', { example: $t( 'contact_form_email_placeholder' ) } )"
							v-model="formData.email.value">
					</b-input>
				</b-field>
				<span v-if="formData.email.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_email_error' ) }}</span>
				</div>
				<div class="has-margin-top-18">
					<label for="category" class="subtitle">{{ $t( 'contact_form_topic_placeholder' ) }}</label>
					<b-select
						class="is-form-input"
						v-model="formData.topic.value"
						id="category"
						name="category">
						<option hidden="hidden" disabled="disabled" value="">{{ $t( 'contact_form_topic_placeholder' ) }}</option>
						<option v-for="option in contactData.contact_categories">{{ option }}</option>
					</b-select>
					<span v-if="formData.topic.validity === Validity.INVALID" class="help is-danger has-padding-top-18">{{ $t( 'contact_form_topic_error' ) }}</span>
				</div>
				<div class="has-margin-top-18">
					<label for="subject" class="subtitle">{{ $t( 'contact_form_subject_label' ) }}</label>
					<b-field>
						<b-input type="text"
								id="subject"
								name="subject"
								:placeholder="$t( 'form_for_example', { example: $t( 'contact_form_subject_placeholder' ) } )"
								v-model="formData.subject.value">
						</b-input>
					</b-field>
					<span v-if="formData.subject.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_subject_error' ) }}</span>
				</div>
				<div class="has-margin-top-18">
					<label for="messageBody" class="subtitle">{{ $t( 'contact_form_body_label' ) }}</label>
					<b-field>
						<b-input type="textarea"
								id="messageBody"
								name="messageBody"
								v-model="formData.comment.value">
						</b-input>
					</b-field>
					<span v-if="formData.comment.validity === Validity.INVALID" class="help is-danger">{{ $t( 'contact_form_body_error' ) }}</span>
				</div>
			</fieldset>
			<div class="has-margin-top-18">
				<b-button id="submit-btn" type="is-primary is-main" native-type="submit">
					{{ $t('contact_form_submit_button') }}
				</b-button>
			</div>
		</form>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { FormData } from '@/view_models/Contact';
import { Helper } from '@/store/util';
import { Validity } from '@/view_models/Validity';
import { trackFormSubmission } from '@/tracking';

export default Vue.extend( {
	name: 'Contact',
	data: function (): { formData: FormData } {
		return {
			formData: {
				firstname: {
					name: 'name',
					value: this.contactData.firstname ? this.contactData.firstname : '',
					pattern: '^.+$',
					optionalField: true,
					validity: Validity.VALID,
				},
				lastname: {
					name: 'lastname',
					value: this.contactData.lastname ? this.contactData.lastname : '',
					pattern: '^.+$',
					optionalField: true,
					validity: Validity.VALID,
				},
				donationNumber: {
					name: 'donationNumber',
					value: this.contactData.donationNumber ? this.contactData.donationNumber : '',
					pattern: '^[0-9]*$',
					optionalField: true,
					validity: Validity.VALID,
				},
				email: {
					name: 'email',
					value: this.contactData.email ? this.contactData.email : '',
					pattern: '^(.+)@(.+)\\.(.+)$',
					optionalField: false,
					validity: Validity.INCOMPLETE,
				},
				topic: {
					name: 'topic',
					value: this.contactData.category ? this.$i18n.t( this.contactData.category ) as string : '',
					pattern: '^.+$',
					optionalField: false,
					validity: Validity.INCOMPLETE,
				},
				subject: {
					name: 'subject',
					value: this.contactData.subject ? this.contactData.subject : '',
					pattern: '^.+$',
					optionalField: false,
					validity: Validity.INCOMPLETE,
				},
				comment: {
					name: 'comment',
					value: this.contactData.messageBody ? this.contactData.messageBody : '',
					pattern: '(\n|.)+',
					optionalField: false,
					validity: Validity.INCOMPLETE,
				},
			},
		};
	},
	props: [
		'contactData',
	],
	computed: {
		Validity: {
			get() {
				return Validity;
			},
		},
	},
	methods: {
		submit() {
			let isValid = true;
			Object.keys( this.$data.formData ).forEach( ( fieldName: string ) => {
				let field = this.$data.formData[ fieldName ];
				field.validity = Helper.inputIsValid( field.value, field.pattern, field.optionalField );
				if ( field.validity !== Validity.VALID ) {
					isValid = false;
				}
			} );
			if ( isValid ) {
				const form = this.$refs.form as HTMLFormElement;
				trackFormSubmission( form );
				form.submit();
			}
		},
	},
} );
</script>
