<template>
	<div class="column is-full">
		<h1 class="title">{{ $t( 'donation_comments_title' ) }}</h1>
		<span>{{ $t( 'donation_comments_text' )}}</span>
		<span v-if="isLoading" class="has-margin-top-36 columns is-centered">
			<span class="pseudo button is-loading"></span>
		</span>
		<div class="has-margin-top-18">
			<div class="has-margin-top-18" v-for="comment in pageContent">
				<div class="has-text-weight-bold">{{ $t( 'donation_comments_donor_headline', { amount: comment.amount, donor: comment.donor } )}}</div>
				<div class="has-text-gray-dark">{{ comment.date }}</div>
				<div>{{ comment.comment }}</div>
			</div>
		</div>
		<div v-if="!isLoading" class="page-selector has-margin-top-36 has-margin-bottom-18">
			<a class="button mdi mdi-arrow-left" :disabled="currentPage === 1" v-on:click="previousPage"></a>
			<b-select
					class="is-form-input"
					v-model="currentPage"
					id="page"
					name="page"
					@change.native="switchPage">
				<option v-for="page in pageCount"
						:value="page"> {{ page }}
				</option>
			</b-select>
			<a class="button mdi mdi-arrow-right" :disabled="currentPage === pageCount" v-on:click="nextPage"></a>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import axios from 'axios';
import { commentModelsFromObject } from '@/view_models/Comment';

const PAGE_SIZE = 10;

export default Vue.extend( {
	name: 'CommentList',
	data() {
		return {
			comments: [] as any,
			pageContent: [],
			pageCount: 0,
			currentPage: 1,
			isLoading: true,
		};
	},
	mounted() {
		axios.get( '/list-comments.json?n=100&anon=1' ).then( ( response ) => {
			this.comments = commentModelsFromObject( response.data );
			this.pageCount = Math.ceil( this.comments.length / PAGE_SIZE );
			this.switchPage();
			this.isLoading = false;
		}, () => {
			this.isLoading = false;
		} );
	},
	methods: {
		switchPage() {
			this.pageContent = this.comments.slice( ( ( this.currentPage - 1 ) * PAGE_SIZE ), ( this.currentPage * PAGE_SIZE ) - 1 );
		},
		nextPage() {
			if ( this.currentPage < this.pageCount ) {
				this.currentPage += 1;
				this.switchPage();
			}
		},
		previousPage() {
			if ( this.currentPage > 1 ) {
				this.currentPage -= 1;
				this.switchPage();
			}
		},
	},
} );
</script>

<style lang="scss">
	@import "../../scss/variables";
	.pseudo.button {
		display: block;
		width: 100px;
	}
	.page-selector {
		position: relative;
		height: 50px;
		> a {
			position: absolute;
			width: 50px;
			height: 48px;
			&.mdi-arrow-left {
				left: 0;
			}
			&.mdi-arrow-right {
				left: 160px;
			}
		}
		> .control {
			position: absolute;
			width: 70px;
			left: 70px;
		}
	}
</style>
