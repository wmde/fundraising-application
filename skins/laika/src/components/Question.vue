<template>
    <div class="question space-above">
        <div v-bind:class="[ isOpen ? 'border-secondary' : 'border-primary' ]">
            <div class="inline-icon" @click="toggle()"
                 :data-content-target="!isOpen ? '/page/Häufige Fragen' : ''"
                 :data-track-content="!isOpen"
                 :data-content-name="!isOpen ? 'Expand' : ''"
                 :data-content-piece="!isOpen ? content.question : ''">
                <h5 v-bind:class="[ isOpen ? 'secondary-color' : 'title-primary-color' ]">
                    {{ content.question }}
                </h5>
                <i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up secondary-color' : 'icon-keyboard_arrow_down primary-color' ]">
                </i>
            </div>
            <div v-show="isOpen" v-html="content.visibleText" class="space-below"></div>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Question } from '@/view_models/faq';

export default Vue.extend( {
	name: 'question',
	props: {
		content: {
			type: Object as () => Question,
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
