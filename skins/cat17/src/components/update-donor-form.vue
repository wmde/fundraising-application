<template>
    <form id="form-donation" class="main-form" action="{$ basepath $}/donation/update" method="post" novalidate>
        <div class="container">
            <div class="row">
                <div class="form-shadow-wrap col-xs-12 col-sm-12">
                    <section id="donation-type" class="donation-data clearfix">
                        <h2>{{ $t( 'donation_section_donor_title' ) }}</h2>

                        <p class="legend"> {{ $t('donation_section_donor_legend') }}</p>

                        <fieldset id="type-donor"
                                  class="show-info padding-right-4 col-xs-12 col-sm-8 col-sm-offset-right-4 col-md-6 col-md-offset-right-7 no-gutter-left" ref="donorFieldset">
                            <legend class="sr-only">{{ $t('donation_section_donor_legend') }}</legend>

                            <div :class="[ 'wrap-field', addressType == 'person' ? 'selected' : '' ]">

                                <div class="wrap-input">
                                    <input type="radio" name="addressType" id="personal" value="person" v-model="addressType">
                                    <label for="personal">
                                        <span>{{ $t('donation_addresstype_option_private') }}</span>
                                        <i class="icon-account_circle"></i>
                                    </label>
                                </div>
                                <div class="wrap-info">
                                    <div :class="[ 'info-text', addressType == 'person' ? 'opened' : '' ]" ref="person">
                                        <div class="field-grp field-salutation">
                                            <div class="wrap-select-50 clearfix ">
                                                <select class="no-outline salutation" id="salutation" name="salutation"
                                                        data-jcf='{"wrapNative": false,  "wrapNativeOnMobile": true  }'>
                                                    <option hidden class="hideme" value="">{{ $t('salutation_label') }}</option>
                                                    <option value="Herr">{{ $t('salutation_option_mr') }}</option>
                                                    <option value="Frau">{{ $t('salutation_option_mrs') }}</option>
                                                </select>
                                                <select class="no-outline personal-title" id="title" name="title"
                                                        data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true}'>
                                                    <option value="">{{ $t('title_option_none') }}</option>
                                                    <option value="Dr.">Dr.</option>
                                                    <option value="Prof.">Prof.</option>
                                                    <option value="Prof. Dr.">Prof. Dr.</option>
                                                </select>
                                            </div>
                                            <span class="error-text">{{ $t('form_salutation_error') }}</span>
                                        </div>

                                        <field-wrapper name="first-name" label="firstname_label" error-message="form_firstname_error">
                                            <input type="text" id="first-name" name="firstName" :placeholder="$t('firstname_label')" v-model="firstName" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="last-name" label="lastname_label" error-message="form_lastname_error">
                                            <input type="text" id="last-name" name="lastName" :placeholder="$t('lastname_label')" v-model="lastName" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="email" label="email_label" error-message="form_email_error">
                                            <input type="email" id="email" :placeholder="$t('email_label')" v-model="email" data-pattern="^[^@]+@.+$">
                                        </field-wrapper>

                                        <field-wrapper name="street" label="street_label" error-message="form_street_error">
                                            <input type="text" id="street" :placeholder="$t('street_label')" v-model="street" data-pattern="^.+$">
                                            <span class="warning-text">{{ $t( 'form_street_number_warning' ) }}</span>
                                        </field-wrapper>

                                        <field-wrapper name="post-code" label="zip_label" error-message="form_zip_error">
                                            <input type="text" id="post-code" :placeholder="$t('zip_label')" v-model="postcode" data-pattern="^[0-9]{4,5}$">
                                        </field-wrapper>

                                        <field-wrapper name="city" label="city_label" error-message="form_city_error">
                                            <input type="text" id="city" :placeholder="$t('city_label')"  v-model="city" data-pattern="^.+$">
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
                                            <label class="news" for="newsletter" v-html="$t( 'donation_sendinfo_label' )"></label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="donation-receipt" data-jcf='{"wrapNative": true}' class="jcf">
                                            <label for="donation-receipt">
                                                {{ $t('donation-no-donation-receipt') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div :class="[ 'wrap-field', addressType == 'firma' ? 'selected' : '' ]">
                                <div class="wrap-input">
                                    <input type="radio" name="addressType" id="company" value="firma" v-model="addressType">
                                    <label for="company">
                                        <span>{{ $t('donation_addresstype_option_company') }}</span>
                                        <i class="icon-work"></i>
                                    </label>
                                </div>
                                <div class="wrap-info">
                                    <div :class="[ 'info-text', addressType == 'firma' ? 'opened' : '' ]" ref="firma">

                                        <field-wrapper name="company-name" label="companyname_label" error-message="form_companyname_error">
                                            <input type="text" id="company-name" name="companyName" :placeholder="$t('companyname_label')"  v-model="companyName" data-pattern="^.+$">
                                        </field-wrapper>

                                        <field-wrapper name="email-company" label="email_label" error-message="form_email_error">
                                            <input type="email" id="email-company" :placeholder="$t('email_label')" v-model="email" data-pattern="^[^@]+@.+$">
                                        </field-wrapper>

                                        <field-wrapper name="street-company" label="street_label" error-message="form_street_error">
                                            <input type="text" id="street-company" :placeholder="$t('street_label')" v-model="street" data-pattern="^.+$">
                                            <span class="warning-text">{{ $t( 'form_street_number_warning' ) }}</span>
                                        </field-wrapper>

                                        <field-wrapper name="post-code-company" label="zip_label" error-message="form_zip_error">
                                            <input type="text" id="post-code-company" :placeholder="$t('zip_label')" v-model="postcode" data-pattern="^[0-9]{4,5}$">
                                        </field-wrapper>

                                        <field-wrapper name="city-company" label="city_label" error-message="form_city_error">
                                            <input type="text" id="city-company" :placeholder="$t('city_label')" v-model="city" data-pattern="^.+$">
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
                                            <label class="news" for="newsletter-company" v-html="$t( 'donation_sendinfo_label' )">
                                            </label>
                                        </div>
                                        <div>
                                            <input type="checkbox" id="donation-receipt-company" name="donationReceipt" value="0" data-jcf='{"wrapNative": true}' class="jcf">
                                            <label for="donation-receipt-company">
                                                {{ $t('donation-no-donation-receipt') }}
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
	import { mapFields } from 'vuex-map-fields';

	export default {
		name: "update-donor-form",
        components: {
			FieldWrapper
        },
        computed: {
            ...mapFields([
            	'formData.addressType',
                'formData.firstName',
				'formData.lastName',
                'formData.salutation',
                'formData.title',
                'formData.companyName',
                'formData.street',
                'formData.postcode',
                'formData.city',
                'formData.countryCode',
                'formData.email'
            ])
        },
        watch:{
			'addressType': function (val ) {
				this.$nextTick( function () {
					this.$refs.donorFieldset.style.minHeight = this.$refs[val].scrollHeight + 'px';
				});
            }
        }
	}
</script>

<style scoped>

</style>