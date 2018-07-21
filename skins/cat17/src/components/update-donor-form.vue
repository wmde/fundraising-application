<template>
    <form id="form-donation" class="main-form" action="{$ basepath $}/donation/update" method="post" novalidate>
        <div class="container">
            <div class="row">
                <div class="form-shadow-wrap col-xs-12 col-sm-12">
                    <section id="donation-type" class="donation-data clearfix">
                        <h2>{$ 'donation_section_donor_title' | trans $}</h2>

                        <p class="legend">{$ 'donation_section_donor_legend' | trans $}</p>

                        <fieldset id="type-donor"
                                  class="show-info padding-right-4 col-xs-12 col-sm-8 col-sm-offset-right-4 col-md-6 col-md-offset-right-7 no-gutter-left">
                            <legend class="sr-only">{$ 'donation_section_donor_legend' | trans $}</legend>

                            <div class="wrap-field personal">

                                <div class="wrap-input">
                                    <input type="radio" name="addressType" id="personal" value="person" v-model="addressType">
                                    <label for="personal">
                                        <span>{$ 'donation_addresstype_option_private' | trans $}</span>
                                        <i class="icon-account_circle"></i>
                                    </label>
                                </div>
                                <div class="wrap-info">
                                    <div :class="[ 'info-text', addressType == 'person' ? 'opened' : '' ]">
                                        <div class="field-grp field-salutation">
                                            <div class="wrap-select-50 clearfix ">
                                                <select class="no-outline salutation" id="salutation" name="salutation"
                                                        data-jcf='{"wrapNative": false,  "wrapNativeOnMobile": true  }'>
                                                    <option hidden class="hideme" value="">{$ 'salutation_label' | trans $}</option>
                                                    <option value="Herr">{$ 'salutation_option_mr' | trans $}</option>
                                                    <option value="Frau">{$ 'salutation_option_mrs' | trans $}</option>
                                                </select>
                                                <select class="no-outline personal-title" id="title" name="title"
                                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true}'>
                                                    <option value="">{$ 'title_option_none' | trans $}</option>
                                                    <option value="Dr.">Dr.</option>
                                                    <option value="Prof.">Prof.</option>
                                                    <option value="Prof. Dr.">Prof. Dr.</option>
                                                </select>
                                            </div>
                                            <span class="error-text">{$ 'form_salutation_error' | trans $}</span>
                                        </div>

                                        <field-wrapper name="first-name" label="firstname_label" error-message="form_firstname_error">
                                            <input type="text" id="first-name" name="firstName" placeholder="{$ 'firstname_label' | trans $}" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="last-name" label="lastname_label" error-message="form_lastname_error">
                                            <input type="text" id="last-name" name="lastName" placeholder="{$ 'lastname_label' | trans $}" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="email" label="email_label" error-message="form_email_error">
                                            <input type="email" id="email" placeholder="{$ 'email_label' | trans $}" data-pattern="^[^@]+@.+$">
                                        </field-wrapper>

                                        <field-wrapper name="street" label="street_label" error-message="form_street_error">
                                            <input type="text" id="street" placeholder="{$ 'street_label' | trans $}" data-pattern="^.+$">
                                            <span class="warning-text">{$ "form_street_number_warning" | trans $}</span>
                                        </field-wrapper>

                                        <field-wrapper name="post-code" label="zip_label" error-message="form_zip_error">
                                            <input type="text" id="post-code" placeholder="{$ 'zip_label' | trans $}" data-pattern="^[0-9]{4,5}$">
                                        </field-wrapper>

                                        <field-wrapper name="city" label="city_label" error-message="form_city_error">
                                            <input type="text" id="city" placeholder="{$ 'city_label' | trans $}" data-pattern="^.+$">
                                        </field-wrapper>

                                        <!-- TODO use vue-native select field -->
                                        <select id="country" title="{$ 'country_label' | trans | e( 'html_attr' ) $}"
                                                data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true, "flipDropToFit": true,  "maxVisibleItems": 6}'
                                                class="no-outline country-select">
                                            {% for countryCode in COUNTRIES %}
                                            <option value="{$ countryCode $}">{$ ( 'country_option_' ~ countryCode ) | trans $}</option>
                                            {% endfor %}
                                        </select>

                                    </div>
                                    <div class="wrap-check">
                                        <div>
                                            <input type="checkbox" id="newsletter" data-jcf='{"wrapNative": true}' class="jcf">
                                            <label class="news" for="newsletter">
                                                {$ 'donation_sendinfo_label' | trans | raw $}
                                            </label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="donation-receipt" data-jcf='{"wrapNative": true}' class="jcf">
                                            <label for="donation-receipt">
                                                {$ 'donation-no-donation-receipt' | trans $}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wrap-field firma">
                                <div class="wrap-input">
                                    <input type="radio" name="addressType" id="company" value="firma" v-model="addressType">
                                    <label for="company">
                                        <span>{$ 'donation_addresstype_option_company' | trans $}</span>
                                        <i class="icon-work"></i>
                                    </label>
                                </div>
                                <div class="wrap-info">
                                    <div :class="[ 'info-text', addressType == 'firma' ? 'opened' : '' ]">

                                        <field-wrapper name="company-name" label="companyname_label" error-message="form_companyname_error">
                                            <input type="text" id="company-name" name="companyName" placeholder="{$ 'companyname_label' | trans $}" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="email-company" label="email_label" error-message="form_email_error">
                                            <input type="email" id="email-company" placeholder="{$ 'email_label' | trans $}" data-pattern="^[^@]+@.+$">
                                        </field-wrapper>

                                        <field-wrapper name="street-company" label="street_label" error-message="form_street_error">
                                            <input type="text" id="street-company" placeholder="{$ 'street_label' | trans $}" data-pattern="^.+$">
                                            <span class="warning-text">{$ "form_street_number_warning" | trans $}</span>
                                        </field-wrapper>

                                        <field-wrapper name="post-code-company" label="zip_label" error-message="form_zip_error">
                                            <input type="text" id="post-code-company" placeholder="{$ 'zip_label' | trans $}" data-pattern="^[0-9]{4,5}$">
                                        </field-wrapper>

                                        <field-wrapper name="city-company" label="city_label" error-message="form_city_error">
                                            <input type="text" id="city-company" placeholder="{$ 'city_label' | trans $}" data-pattern="^.+$">
                                        </field-wrapper>

                                        <!-- TODO use vue-native select field -->
                                        <select id="country-company" title="{$ 'country_label' | trans | e( 'html_attr' ) $}"
                                                data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true, "flipDropToFit": true,  "maxVisibleItems": 6}'
                                                class="no-outline country-select">
                                            {% for countryCode in COUNTRIES %}
                                            <option value="{$ countryCode $}">{$ ( 'country_option_' ~ countryCode ) | trans $}</option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                    <div class="wrap-check">
                                        <div>
                                            <input type="checkbox" id="newsletter-company" name="info" value="1">
                                            <label class="news" for="newsletter-company">
                                                {$ 'donation_sendinfo_label' | trans | raw $}
                                            </label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="donation-receipt-company" name="donationReceipt" value="0" data-jcf='{"wrapNative": true}' class="jcf">
                                            <label for="donation-receipt-company">
                                                {$ 'donation-no-donation-receipt' | trans $}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </section>

                </div>

                <div class="action-block visible-md visible-lg col-md-offset-0 col-md-4 hidden-print">
                    <!-- TODO create "shy" submit button -->
                    <input type="submit" value="Spende abschließen" class="btn btn-donation btn-unactive">
                </div>
            </div>
        </div>
    </form>
</template>

<script>
	import FieldWrapper from './field-wrapper';

	export default {
		name: "update-donor-form",
        components: {
			FieldWrapper
        },
        data: () => {
			return {
				addressType: null
            }
        }
        // TODO: create method for changing address type change, set CSS min-height of fieldset to scrollheight of info-text
	}
</script>

<style scoped>

</style>