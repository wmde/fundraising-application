<template>
	<div class="accordion-item" v-bind:class="[ isOpen ? 'accordion-open' : '' ]">
		<div @click="isOpen = !isOpen"
			:data-content-target="isOpen ? '/page/Häufige Fragen' : ''"
			:data-track-content="isOpen"
			:data-content-name="isOpen ? 'Expand' : ''"
			:data-content-piece="isOpen ? content.question : ''">
			<div v-bind:class="[ isOpen ? 'has-text-primary has-text-weight-bold has-margin-left-18' : 'accordion-heading', 'icon-inline', 'accordion-title' ] ">
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
	},
	data() {
		return {
			isOpen: false,
		};
	},
} );
</script>
