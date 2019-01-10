<template>
	<div class="field-grp field-street">
		<input type="text" id="street" placeholder="{$ 'street_label' | trans $}" data-pattern="^.+$">
		<label for="street" class="sr-only">{$ 'street_label' | trans $}</label>
		<span class="error-text">{$ 'form_street_error' | trans $}</span>
		<span class="warning-text">{$ "form_street_number_warning" | trans $}</span>
	</div>
	<div class="field-grp field-postcode">
		<input type="text" id="post-code" placeholder="{$ 'zip_label' | trans $}" data-pattern="^[0-9]{4,5}$">
		<label for="post-code" class="sr-only">{$ 'zip_label' | trans $}</label>
		<span class="error-text">{$ 'form_zip_error' | trans $}</span>
	</div>
	<div class="field-grp field-city">
		<input type="text" id="city" placeholder="{$ 'city_label' | trans $}" data-pattern="^.+$">
		<label for="city" class="sr-only">{$ 'city_label' | trans $}</label>
		<span class="error-text">{$ 'form_city_error' | trans $}</span>
	</div>
	<select id="country" title="{$ 'country_label' | trans | e( 'html_attr' ) $}"
			data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true, "flipDropToFit": true,  "maxVisibleItems": 6}'
			class="no-outline country-select">
		{% for countryCode in COUNTRIES %}
		<option value="{$ countryCode $}">{$ ( 'country_option_' ~ countryCode ) | trans $}</option>
		{% endfor %}
	</select>
</template>

<script lang="ts">
    import Vue from "vue";
	export default Vue.extend({
		name: "postal"
	});
</script>