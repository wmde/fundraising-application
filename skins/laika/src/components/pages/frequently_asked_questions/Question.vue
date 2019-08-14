<template>
	<div class="accordion-item" v-bind:class="[ isOpen ? 'accordion' : '' ]">
		<div @click="toggle()"
			:data-content-target="isOpen ? '/page/Häufige Fragen' : ''"
			:data-track-content="isOpen"
			:data-content-name="isOpen ? 'Expand' : ''"
			:data-content-piece="isOpen ? content.question : ''">
			<div v-bind:class="[ isOpen ? 'has-text-primary has-text-weight-bold' : 'accordion-heading', 'icon-inline', 'accordion-title' ] ">
				{{ content.question }}
				<b-icon v-if="isOpen" icon="arrow-up" class="icon-size"></b-icon>
				<b-icon v-else icon="arrow-down" class="icon-size"></b-icon>
			</div>
		</div>
		<div v-show="isOpen" v-html="content.visibleText" class="accordion-content"></div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { QuestionModel } from '@/view_models/faq';

export default Vue.extend( {
	name: 'question',
	props: {
		content: {
			type: Object as () => QuestionModel,
		},
		visibleQuestionId: String,
		questionId: String,
	},
	computed: {
		isOpen: {
			get: function (): boolean {
				return this.questionId === this.visibleQuestionId;
			},
		},
	},
	methods: {
		toggle: function () {
			if ( !this.isOpen ) {
				this.$emit( 'question-opened', this.questionId );
			} else {
				this.$emit( 'question-opened' ); // close the current question when the arrow up icon is clicked
			}
		},
	},
} );
</script>
