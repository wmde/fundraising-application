<template>
<div class="question space-above">
	<div v-bind:class="[ isOpen ? 'border-secondary' : 'border-primary' ]">
		<div class="inline-icon" @click="toggle()"
			 data-content-target="/page/Häufige Fragen"
			 data-track-content
			 data-content-name="Toggle expand/collapse"
			 data-content-piece="Toggle expand/collapse">
			<h5 v-bind:class="[ isOpen ? 'secondary-color' : 'title-primary-color' ]">
				{{ content.question }}
			</h5>
			<i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up secondary-color' : 'icon-keyboard_arrow_down primary-color' ]">
			</i>
		</div>
		<div v-show="isOpen"  v-html="content.visible_text" class="space-below"></div>
	</div>
</div>
</template>

<script>
export default {
	name: 'question',
	props: [ 'content', 'visibleQuestionId' ],
	computed: {
		isOpen: function () {
			return this.$vnode.key === this.visibleQuestionId;
		}
	},
	methods: {
		toggle: function () {
			if ( !this.isOpen ) {
				this.$emit( 'question-opened', this.$vnode.key );
			} else {
				this.$emit( 'question-opened' ); // close the current question when the arrow up icon is clicked
			}
		}
	}
};
</script>