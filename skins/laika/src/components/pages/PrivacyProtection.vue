<template>
    <div class="column is-full">
        <h2>Datenerhebung</h2>
        <p class="legend">
            {{ $t('privacy_optout_description') }}
        </p>
        <div class="privacy_selection">
            <div class="privacy_selection_box">
                <div class="wrap-field choice-in">
                    <div class="wrap-input">
                        <input name="matomo_choice" id="tracking-opt-in" value="0" type="radio">
                        <label for="tracking-opt-in"><span>{{ $t('privacy_optout_tracking_permit') }}</span></label>
                    </div>
                </div>
                <div class="wrap-field choice-out">
                    <div class="wrap-input">
                        <input name="matomo_choice" id="tracking-opt-out" value="1" type="radio">
                        <label for="tracking-opt-out"><span>{{ $t('privacy_optout_tracking_deny') }}</span></label>
                    </div>
                </div>
            </div>
            <div class="privacy_explanation">
                <p>
                    {{ $t('privacy_optout_tracking_state') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'PrivacyProtection',
} );
</script>
