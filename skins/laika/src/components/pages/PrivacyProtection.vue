<template>
	<div class="column is-full privacy-selection has-padding-36">
		<h2 class="title is-size-2">{{ $t( 'privacy_protection_title' ) }}</h2>
		<fieldset>
			<legend class="legend">
				{{ $t('privacy_optout_description') }}
			</legend>
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
			<p v-if="showOptOutExplanation === 0" class="has-text-dark-lighter has-margin-top-18">{{ $t( 'privacy_optout_tracking_state' ) }}</p>
			<p v-else class="has-text-dark-lighter has-margin-top-18" v-html="$t( 'privacy_optout_tracking_state_no' )"></p>
		</fieldset>
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
			showOptOutExplanation: 0,
		};
	},
	beforeMount: function () {
		this.getInitialTrackingState();
	},
	methods: {
		changeTracking: function (): void {
			if ( this.$data.optOut === 0 ) {
				jsonp( TRACKING_URL + 'index.php?module=API&method=AjaxOptOut.doTrack&format=json',
					undefined,
					( error, data ) => {
						if ( data.result === 'success' ) {
							this.$data.showOptOutExplanation = 0;
						}
					}
				);
			} else {
				jsonp( TRACKING_URL + 'index.php?module=API&method=AjaxOptOut.doIgnore&format=json',
					undefined,
					( error, data ) => {
						if ( data.result === 'success' ) {
							this.$data.showOptOutExplanation = 1;
						}
					}
				);
			}
		},
		getInitialTrackingState: function (): void {
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

<style lang="scss">
	@import "../../scss/custom.scss";
		.privacy-selection {
			border: 1px solid $fun-color-gray-mid;
			border-radius: 2px;
			.b-radio.radio {
				& + .radio {
					margin-left: 0;
				}
			}
		}
</style>
