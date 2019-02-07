<template>
<div>
	<div class="row">
		<div v-bind:class="[{ invalid: showError.street }, 'field-grp', 'field-street', 'col-xs-12', 'col-md-6']">
			<label for="street">{{ messages.street_label }}</label>
			<input type="text"
					id="street"
                    name="street"
					:placeholder="messages.street_label"
					v-model="formData.street.value"
					@blur="validateInput(formData, 'street'); displayStreetWarning()">
			<span v-if="showError.street" class="error-text">{{ messages.form_street_error }}</span>
			<span v-if="showWarning" class="warning-text">{{ messages.form_street_number_warning }}</span>
		</div>
	</div>
	<div class="row">
		<div v-bind:class="[{ invalid: showError.postcode }, 'field-grp', 'field-postcode', 'col-xs-12', 'col-md-6']">
			<label for="post-code">{{ messages.zip_label }}</label>
			<input type="text"
					id="post-code"
                    name="postcode"
					v-model="formData.postcode.value"
					:placeholder="messages.zip_label"
					@blur="validateInput(formData, 'postcode')">
			<span v-if="showError.postcode" class="error-text">{{ messages.form_zip_error }}</span>
		</div>
        <div v-bind:class="[{ invalid: showError.city }, 'field-grp', 'field-city', 'col-xs-12', 'col-md-6']">
			<label for="city">{{ messages.city_label }}</label>
			<input type="text"
					id="city"
                    name="city"
					:placeholder="messages.city_label"
					v-model="formData.city.value"
					@blur="validateInput(formData, 'city')">
			<span v-if="showError.city" class="error-text">{{ messages.form_city_error }}</span>
		</div>
	</div>
	<div class="row">
	<div class="align-block">
		<label for="country">{{ messages.country_label }}</label>
		<select id="country"
				name="country"
				class="country-select col-xs-12 col-md-6"
				data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true, "flipDropToFit": true,  "maxVisibleItems": 6}'
				v-model="formData.country.value"
				@blur="validateInput(formData, 'country')">
			<option v-for="(countryCode, index) in countries" 
					:value="countryCode"
					:key="index">{{ messages[ 'country_option_' + countryCode ] }}
			</option>
		</select>
	</div>
	</div>
</div>
</template>

<script lang="ts">
	import Vue from 'vue';
	export default Vue.extend( {
		name: 'postal',
        props: [ 'showError', 'formData', 'validateInput', 'messages', 'countries' ],
        data() {
            return {
                showWarning: false
            }
        },
        methods: {
            displayStreetWarning() {
                this.showWarning = /^\D+$/.test(this.formData.street.value);
            }
        }
	} );
</script>
