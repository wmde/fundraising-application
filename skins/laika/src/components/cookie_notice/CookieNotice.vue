<template>
	<div
		class="cookie-notice"
		:class="{ 'is-visible': isVisible }"
	>
		<form
			class="cookie-notice-content"
			ref="cookieNotice"
			action="/set-cookie-preferences"
		>
			<div class="cookie-notice-info">
				<div class="cookie-notice-text" v-if="!showOptions">
					<h3>{{ $t( 'cookie_heading' ) }}</h3>
					<p class="cookie-notice-text-copy" :class="{ open: textOpen }">
						<text-visibility-toggle :height-to-show="38" v-on:toggle-text="toggleTextOpen">
							<span v-html="$t( 'cookie_content' )"></span>
						</text-visibility-toggle>
					</p>
				</div>
				<div class="cookie-notice-options" v-if="showOptions">
					<h3>
						<a href="" class="cookie-notice-back-button" v-on:click="onBackButtonClick">
							<svg width="11" height="18" viewBox="0 0 11 18" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M0 9L9 18L10.4 16.5L3 9L10.4 1.5L9 0L0 9Z" fill="#0065A4"/>
							</svg>
						</a> {{ $t( 'cookie_option_heading' ) }}
					</h3>
					<div class="cookie-notice-accordion">
						<ul>
							<li>
								<CookieCheckbox
									:heading="$t('cookie_option_required_heading')"
									:content="$t('cookie_option_required_content')"
									:checked="true"
									:disabled="true"
									:name="'required'"
								></CookieCheckbox>
							</li>
							<li>
								<CookieCheckbox
									:heading="$t('cookie_option_optional_heading')"
									:content="$t('cookie_option_optional_content')"
									:checked="optionalChecked"
									:disabled="false"
									v-on:toggle="onOptionalToggle"
									:name="'optional'"
								></CookieCheckbox>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="cookie-notice-buttons">
				<div class="cookie-notice-button check" v-if="!showOptions">
					<button class="button is-primary is-main is-outlined" v-on:click="onCheckButtonClick">
						<span>{{ $t( 'cookie_button_check' ) }}</span>
					</button>
				</div>
				<div class="cookie-notice-button save" v-if="showOptions">
					<button class="button is-primary is-main is-outlined" v-on:click="onSaveButtonClick">
						<span>{{ $t( 'cookie_button_save' ) }}</span>
					</button>
				</div>
				<div class="cookie-notice-button accept">
					<button class="button is-primary is-main is-outlined" v-on:click="onAcceptButtonClick">
						<span>{{ $t( 'cookie_button_accept' ) }}</span>
					</button>
				</div>
			</div>
		</form>
		<HeightAdjuster :element="cookieNotice" :element-visibility="isVisible"></HeightAdjuster>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { ref } from '@vue/composition-api';
import { setConsentGiven } from '@/tracking';
import axios from 'axios';
import CookieCheckbox from './CookieCheckbox.vue';
import HeightAdjuster from './HeightAdjuster.vue';
import TextVisibilityToggle from './TextVisibilityToggle.vue';

const CONSENT_TRUE: string = 'yes';
const CONSENT_FALSE: string = 'no';

export default Vue.extend( {
	name: 'CookieNotice',
	components: {
		CookieCheckbox,
		HeightAdjuster,
		TextVisibilityToggle,
	},
	setup() {
		const isVisible = ref( true );
		const cookieNotice = ref<any>( null );
		const optionalChecked = ref( false );
		const showOptions = ref( false );
		const textOpen = ref( false );

		const toggleTextOpen = () => {
			textOpen.value = !textOpen.value;
		};

		const submitPreferences = () => {
			const form = new FormData();
			const consent = optionalChecked.value ? CONSENT_TRUE : CONSENT_FALSE;
			form.append( 'cookie_consent', consent );
			axios.post(
				cookieNotice.value.action + window.location.search,
				form,
				{ headers: { 'Content-Type': 'multipart/form-data' } }
			).then( () => {
				isVisible.value = false;
				if ( consent === CONSENT_TRUE ) {
					setConsentGiven();
				}
			} );
		};

		const onOptionalToggle = () => {
			optionalChecked.value = !optionalChecked.value;
		};

		const onCheckButtonClick = ( e: Event ) => {
			e.preventDefault();
			showOptions.value = true;
		};

		const onBackButtonClick = ( e: Event ) => {
			e.preventDefault();
			showOptions.value = false;
		};

		const onSaveButtonClick = ( e: Event ) => {
			e.preventDefault();
			submitPreferences();
		};

		const onAcceptButtonClick = ( e: Event ) => {
			e.preventDefault();
			optionalChecked.value = true;
			submitPreferences();
		};

		return {
			isVisible,
			cookieNotice,
			optionalChecked,
			showOptions,
			textOpen,
			toggleTextOpen,
			onOptionalToggle,
			onCheckButtonClick,
			onSaveButtonClick,
			onAcceptButtonClick,
			onBackButtonClick,
		};
	},
} );
</script>
