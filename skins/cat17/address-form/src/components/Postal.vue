<template>
<div>
    <div class="row">
        <div class="field-grp field-street col-xs-12 col-md-6">
            <label for="street">{{ messages.street_label }}</label>
            <input type="text" id="street" :placeholder="messages.street_label" data-pattern="^.+$">
            <span class="error-text">{{ messages.form_street_error }}</span>
            <span class="warning-text">{{ messages.form_street_number_warning }}</span>
        </div>
    </div>
    <div class="row">
        <div class="field-grp field-city col-xs-12 col-md-6">
            <label for="city">{{ messages.city_label }}</label>
            <input type="text" id="city" :placeholder="messages.city_label" data-pattern="^.+$">
            <span class="error-text">{{ form_city_error }}</span>
        </div>
        <div class="field-grp field-postcode col-xs-12 col-md-6">
            <label for="post-code">{{ messages.zip_label }}</label>
            <input type="text" id="post-code" :placeholder="messages.zip_label" data-pattern="^[0-9]{4,5}$">
            <span class="error-text">{{ messages.form_zip_error }}</span>
        </div>
    </div>
    <div class="align-block">
        <label for="country">{{ messages.country_label }}</label>
        <select id="country" title="{$ 'country_label' | trans | e( 'html_attr' ) $}"
                class="country-select col-xs-12 col-md-6" 
                data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true, "flipDropToFit": true,  "maxVisibleItems": 6}'>
            <option v-for="(countryCode, index) in countries" :value="countryCode" :key="index">{{ messages[ 'country_option_' + countryCode ] }}</option>
            {% endfor %}
        </select>
    </div>
</div>
</template>

<script lang="ts">
    import Vue from 'vue';
	export default Vue.extend( {
		name: "postal",
        props: [ 'messages', 'countries' ]
	} );
</script>