<template>
    <div class="membership-benefits-wrapper has-background-bright columns has-margin-top-36 has-padding-18">
        <div class="column is-half">
            <h2 class="title is-size-5">{{ $t( 'donation_confirmation_membership_call_to_action_title' ) }}</h2>
            <p>{{ $t( 'donation_confirmation_membership_call_to_action_text' ) }}</p>
            <p>
                <a href="#">
                    <b-button type="is-primary is-main has-margin-top-36">{{ $t('donation_confirmation_membership_button') }}</b-button>
                </a>
            </p>
        </div>
        <div class="column is-half">
            <p>{{ $t( 'donation_confirmation_bottombox_membership_benefit_1' ) }}</p>
            <p class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_2' ) }}</p>
            <p class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_3' ) }}</p>
            <p class="has-margin-top-18">{{ $t( 'donation_confirmation_bottombox_membership_benefit_4' ) }}</p>
            <p class="has-margin-top-18"><a href="https://wikimedia.de/de/mitglied-werden">{{ $t( 'donation_confirmation_bottombox_membership_link' ) }}</a></p>
        </div>

    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'MembershipInfo',
} );
</script>

<style lang="scss">
    @import "../../../scss/custom";
    .membership-benefits-wrapper {
        border: 1px solid $fun-color-primary-light;
        border-radius: 2px;
    }
</style>
