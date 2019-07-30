<template>
    <div class="column is-full privacy-selection has-padding-36">
        <h2 class="title is-size-2">{{ $t( 'privacy_protection_title' ) }}</h2>
        <p class="legend">
            {{ $t('privacy_optout_description') }}
        </p>
            <b-radio id="tracking-opt-in"
                    name="matomo_choice"
                    native-value="0"
                    v-model="optOut"
                    @input="changeTracking">
                {{ $t('privacy_optout_tracking_permit') }}
            </b-radio>
            <b-radio id="tracking-opt-out"
                    name="matomo_choice"
                    native-value="1"
                    v-model="optOut"
                    @input="changeTracking">
                {{ $t('privacy_optout_tracking_deny') }}
            </b-radio>
        <div class="privacy_explanation">
            <p>{{ $t('privacy_optout_tracking_state') }}</p>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import jsonp from 'jsonp';
import { TRACKING_URL } from '@/trackingUrl';

export default Vue.extend( {
	name: 'PrivacyProtection',
	data: function () {
		return {
			optOut: 0,
		};
	},
	methods: {
		changeTracking: function (): void {
			if ( this.$data.optOut === 0 ) {
				jsonp( TRACKING_URL + 'index.php?module=API&method=AjaxOptOut.doTrack&format=json',
					undefined,
					function () {}
				);
			} else {
				jsonp( TRACKING_URL + 'index.php?module=API&method=AjaxOptOut.doIgnore&format=json',
					undefined,
					function () {}
				);
			}

		},
	},
} );
</script>
<style lang="scss" scoped>
@import "../../scss/custom.scss";
	.b-radio.radio {
		height: 6.5em;
		&+.radio{
			margin-left: 0;
		}
    }
    .privacy-selection {
        border: 1px solid $fun-color-primary-light;
		border-radius: 2px;
    }
</style>
