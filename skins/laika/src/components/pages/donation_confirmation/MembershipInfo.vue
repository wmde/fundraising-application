<template>
    <div class="membership-benefits-wrapper has-background-bright columns has-margin-top-36 has-padding-18">
        <div class="column is-half">
            <h2 class="title is-size-5">{{ $t( 'membership_call_to_action_title' ) }}</h2>
            <p>{{ $t( 'membership_call_to_action_line_1' ) }}</p>
            <p><b-button type="is-primary is-main">{{ $t('donation_confirmation_apply_for_membership') }}</b-button></p>
        </div>
        <div class="column is-half">TODO</div>

    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'DonationSummary',
} );
</script>

<style lang="scss">
    @import "../../../scss/custom";
    .membership-benefits-wrapper {
        border: 1px solid $fun-color-primary-light;
        border-radius: 2px;
    }
</style>
