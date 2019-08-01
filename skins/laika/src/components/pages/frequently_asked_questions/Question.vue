<template>
	<div class="question" v-bind:class="[ isOpen ? 'accordion' : '' ]">
		<div @click="toggle()"
			:data-content-target="!isOpen ? '/page/Häufige Fragen' : ''"
			:data-track-content="!isOpen"
			:data-content-name="!isOpen ? 'Expand' : ''"
			:data-content-piece="!isOpen ? content.question : ''">
			<div v-bind:class="[ isOpen ? 'has-text-primary has-text-weight-bold' : 'accordion-heading', 'icon-inline', 'question-title' ] ">
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

<style lang="scss">
@import "../../../scss/custom";

.accordion {
	border: 1px solid $fun-color-gray-light-transparency;
	padding: 1.4rem;
	box-sizing: content-box;
	&-heading {
		padding: 1.4rem;
		border-bottom: 2px solid $fun-color-gray-light-transparency;
		cursor: pointer;
	}
	&-content {
		padding-top: 2.8rem;
		padding-right: 0.6rem;
	}
	.question-title {
		cursor: pointer;
	}
}
.icon-inline {
	display: flex;
	justify-content: space-between;
	flex-wrap: nowrap;
	align-items: center;
}
</style>
