<template>
    <div class="has-background-bright columns">
        <div class="column is-two-thirds is-half-desktop">
            <span v-html="$t( 'donation_confirmation_topbox_summary' )"></span>
        </div>
        <div class="column is-one-third is-half-desktop">Abc</div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';

    export default Vue.extend( {
        name: 'DonationSummary'
    } );
</script>
