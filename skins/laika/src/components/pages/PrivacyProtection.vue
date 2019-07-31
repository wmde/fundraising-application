<template>
    <div class="column is-full privacy-selection has-padding-36">
        <h2 class="title is-size-2">{{ $t( 'privacy_protection_title' ) }}</h2>
        <p class="legend">
            {{ $t('privacy_optout_description') }}
        </p>
            <b-radio id="tracking-opt-in"
                    name="matomo_choice"
                    :native-value="0"
                    v-model="optOut"
                    @input="changeTracking">
                {{ $t('privacy_optout_tracking_permit') }}
            </b-radio>
            <b-radio id="tracking-opt-out"
                    name="matomo_choice"
                    :native-value="1"
                    v-model="optOut"
                    @input="changeTracking">
                {{ $t('privacy_optout_tracking_deny') }}
            </b-radio>
            <p v-if="optOut === 0" class="has-text-dark-lighter has-margin-top-18">{{ $t( 'privacy_optout_tracking_state' ) }}</p>
            <p v-else class="has-text-dark-lighter has-margin-top-18" v-html="$t( 'privacy_optout_tracking_state_no' )"></p>
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
    beforeMount: function() {
       this.getInitialTrackingState();
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
        getInitialTrackingState: function(): void {
            jsonp( TRACKING_URL + 'index.php?module=API&method=AjaxOptOut.isTracked&format=json',
					undefined,
					( error, data ) => {
                        this.$data.optOut = data.value ? 0 : 1;
                    }
				);
        },
	},
} );
</script>
<style lang="scss" scoped>
@import "../../scss/custom.scss";
	.b-radio.radio {
		&+.radio{
            margin-left: 0;
		}
    }
    .privacy-selection {
        border: 1px solid $fun-color-gray-mid;
		border-radius: 2px;
    }
</style>
