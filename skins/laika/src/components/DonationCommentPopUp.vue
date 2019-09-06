<template>
	<form id="laika-comment" name="laika-comment" v-on:submit.prevent="postComment" method="post" ref="form" class="modal-card">
		<input type="hidden" name="donationId" :value="confirmationData.donation.id"/>
		<input type="hidden" name="updateToken" :value="confirmationData.donation.updateToken">
		<p class="modal-card-title has-margin-bottom-18">{{ $t( 'donation_comment_popup_title' ) }}</p><br>
		<p class="has-margin-bottom-18">{{ $t( 'donation_comment_popup_explanation' ) }}</p>
		<div class="has-margin-bottom-18">
			<label for="comment">{{ $t( 'donation_comment_popup_label' ) }}</label>
			<b-input id="comment" name="comment" type="textarea"></b-input>
			<p v-if="commentErrored" class="help is-danger"> {{ $t( 'donation_comment_popup_error' ) }}</p>
		</div>
		<div class="field has-margin-bottom-18">
			<b-checkbox type="checkbox" id="isAnonymous" name="isAnonymous" v-model="commentIsAnon">{{ $t( 'donation_comment_popup_is_anon' ) }}</b-checkbox>
		</div>
		<div class="field has-margin-bottom-18">
			<b-checkbox type="checkbox" id="public" name="public" v-model="commentIsPublic">{{ $t( 'donation_comment_popup_is_public' ) }}</b-checkbox>
		</div>
		<div class="columns">
			<div class="column">
			<b-button type="is-primary is-main has-margin-top-18 level-item" @click="$parent.close()" outlined>
				{{ $t( 'donation_comment_popup_cancel' ) }}
			</b-button>
			</div>
			<div class="column">
			<b-button type="is-primary is-main has-margin-top-18 level-item" native-type="submit">
				{{ $t( 'donation_comment_popup_submit' ) }}
			</b-button>
			</div>
		</div>
	</form>
</template>

<script lang="ts">
import Vue from 'vue';
import axios, { AxiosResponse } from 'axios';
import { trackFormSubmission } from '@/tracking';

export default Vue.extend( {
	name: 'DonationCommentPopUp',
	data: function () {
		return {
			commentIsPublic: true,
			commentIsAnon: false,
			commentErrored: false,
		};
	},
	props: [
		'confirmationData',
	],
	methods: {
		postComment() {
			let form = this.$refs.form as HTMLFormElement;
			trackFormSubmission( form );
			const jsonForm = new FormData( form );
			axios.post( this.$props.confirmationData.urls.postComment, jsonForm )
				.then( ( validationResult: AxiosResponse<any> ) => {
					if ( validationResult.data.status === 'OK' ) {
						this.$data.commentErrored = false;
						( this.$parent as any ).close();
						this.$emit( 'disable-comment-link' );
					} else {
						this.$data.commentErrored = true;
					}

				} );
		},
	},
} );
</script>
