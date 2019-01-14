<template>
<div id="addressForm" class="container">
    <!--will remove the hardcoded text when composer update is fixed-->
 	<h2>Anschrift ändern {{ messages.address_form_title }}</h2>
 	<h5>Die bisherigen Daten sind schon eingetragen, bitte passen Sie diese an. {{ messages.address_form_subtitle }}</h5>
 	<div class="row">
        <div class="col-xs-12 col-md-9">
            <div class="form-shadow-wrap">
                <div v-if="!isCompany">
                    <div class="align-block">
                        <label for="salutation">{{ messages.salutation_label }}</label>
                        <select class="salutation col-xs-12 col-md-6" id="salutation" name="salutation" data-jcf='{"wrapNative": false,  "wrapNativeOnMobile": true  }'>
                            <option hidden class="hideme" value="">{{ messages.salutation_label }}</option>
                            <option value="Herr">{{ messages.salutation_option_mr }}</option>
                            <option value="Frau">{{ messages.salutation_option_mrs }}</option>
                        </select>
                    </div>
                    <div class="align-block">
                        <!--will remove the hardcoded text when composer update is fixed-->
                        <label for="title">Akademischer Titel {{ messages.academic_title_label }}</label>
                        <select class="personal-title col-xs-12 col-md-6" id="title" name="title" data-jcf='{"wrapNative": false, "wrapNativeOnMobile": true}'>
                            <option value="">{{ messages.title_option_none }}</option>
                            <option value="Dr.">Dr.</option>
                            <option value="Prof.">Prof.</option>
                            <option value="Prof. Dr.">Prof. Dr.</option>
                        </select>
                    </div>
                    <span class="error-text"> {{ messages.form_salutation_error }}</span>
                </div>
                <name :is-company="isCompany" :messages="messages"></name>
                <postal :messages="messages" :countries="countries"></postal>
            </div>
        </div>
        <div class="col-xs-12 col-md-3 submit">
            <input type="submit" value="Kontaktdaten ändern" class="btn btn-address-change">
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
		props: {
            addressToken: String,
            isCompany: Boolean,
            messages: Object,
            countries: {
                type: Array,
                default: function() {
                    return [ 'DE', 'AT', 'CH', 'BE', 'IT', 'LI', 'LU' ];
                }
            }
        }
	} );
</script>
