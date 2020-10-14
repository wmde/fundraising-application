<template>
	<div
		class="cookie-notice"
		:class="{ 'options-visible': showOptions, 'is-visible': isVisible }"
	>
		<form
			class="cookie-notice-content"
			ref="cookieNotice"
			action="/set-cookie-preferences"
		>
			<div class="cookie-notice-info">
				<div class="cookie-notice-text">
					<h3>{{ $t( 'cookie_heading' ) }}</h3>
					<p class="cookie-notice-text-copy" :class="{ open: textOpen }">
						{{ $t( 'cookie_content' ) }}
						<a href="" v-on:click="toggleTextOpen" class="cookie-notice-more">{{
								$t( 'cookie_option_more' )
							}}</a>
					</p>
				</div>
				<div class="cookie-notice-options">
					<h3>
						<a href="" v-on:click="onBackButtonClick">
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
								></CookieCheckbox>
							</li>
							<li>
								<CookieCheckbox
									:heading="$t('cookie_option_optional_heading')"
									:content="$t('cookie_option_optional_content')"
									:checked="optionalChecked"
									:disabled="false"
									v-on:toggle="onOptionalToggle"
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
		<div :style="{ height: height + 'px' }"></div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { ref, onMounted, onUnmounted } from '@vue/composition-api';
import axios from 'axios';
import CookieCheckbox from './CookieCheckbox.vue';

const CONSENT_TRUE: string = 'yes';
const CONSENT_FALSE: string = 'no';

export default Vue.extend( {
	name: 'CookieNotice',
	components: {
		CookieCheckbox,
	},
	props: {
		showCookieNotice: Boolean,
	},
	setup( props: any ) {
		const isVisible = ref( props.showCookieNotice );
		const cookieNotice = ref<any>( null );
		const height = ref( 0 );
		const optionalChecked = ref( false );
		const showOptions = ref( false );
		const textOpen = ref( false );

		const toggleTextOpen = ( e: Event ) => {
			e.preventDefault();
			textOpen.value = !textOpen.value;
		};

		const onContentResize = () => {
			height.value = cookieNotice.value.offsetHeight;
		};

		const removeNotice = () => {
			height.value = 0;
			isVisible.value = false;
			cookieNotice.value.removeEventListener( 'click', onContentResize );
			window.removeEventListener( 'resize', onContentResize );
		};

		const submitPreferences = () => {
			const form = new FormData();
			form.append( 'cookie_consent', optionalChecked.value ? CONSENT_TRUE : CONSENT_FALSE );
			axios.post(
				cookieNotice.value.action + window.location.search,
				form,
				{ headers: { 'Content-Type': 'multipart/form-data' } }
			).then( () => {
				removeNotice();
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

		onMounted( () => {
			if ( !isVisible.value ) {
				return;
			}
			height.value = cookieNotice.value.offsetHeight;
			cookieNotice.value.addEventListener( 'click', onContentResize );
			window.addEventListener( 'resize', onContentResize );
		} );

		onUnmounted( () => {
			removeNotice();
		} );

		return {
			isVisible,
			cookieNotice,
			height,
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
