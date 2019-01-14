<template>
<div class="row">
    <div v-if="isCompany" class="field-grp field-company col-xs-12 col-md-6">
        <label for="company-name">{{ messages.companyname_label }}</label>
        <input type="text" id="company-name" name="companyName" :placeholder="messages.companyname_label" data-pattern="^.+$">
        <span class="error-text">{{ messages.form_companyname_error }}</span>
    </div>
    <div v-else>
        <div class="field-grp field-firstname col-xs-12 col-md-6">
            <label for="first-name">{{ messages.firstname_label }}</label>
            <input type="text" id="first-name" name="firstName" :placeholder="messages.firstname_label" data-pattern="^.+$">
            <span class="error-text">{{ messages.form_firstname_error }}</span>
        </div>
        <div class="field-grp field-lastname col-xs-12 col-md-6">
            <label for="last-name">{{ messages.lastname_label }}</label>
            <input type="text" id="last-name" name="lastName" :placeholder="messages.lastname_label" data-pattern="^.+$">
            <span class="error-text">{{ messages.form_lastname_error }}</span>
        </div>
    </div>
</div>
</template>

<script lang="ts">
    import Vue from 'vue';
	export default Vue.extend( {
        name: "name",
        props: [ 'isCompany', 'messages' ]
	} );
</script>