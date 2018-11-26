<template>
<div id="question" class="space-above">
	<div v-bind:class="[ isOpen && !collapse ? 'border-secondary' : 'border-primary' ]">
		<div class="inline-icon clickable" @click="toggle()">
			<h5 v-bind:class="[ isOpen && !collapse ? 'secondary-color' : 'title-primary-color' ]">
				{{ content.question }}
			</h5>
			<i v-bind:class="[ isOpen && !collapse ? 'icon-keyboard_arrow_up secondary-color' : 'icon-keyboard_arrow_down primary-color' ]"
			   data-content-target="/page/Häufige Fragen"
			   data-track-content
			   data-content-name="Toggle expand/collapse"
			   data-content-piece="Toggle expand/collapse">
			</i>
		</div>
		<div v-show="isOpen && !collapse"  v-html="content.visible_text"></div>
	</div>
</div>
</template>

<script>
export default {
	name: 'question',
	props: [ 'content', 'idx', 'visible' ],
	data() {
		return {
			collapse: true
		};
	},
	computed: {
		isOpen: function () {
			return this.idx === this.visible;
		}
	},
	methods: {
		toggle: function () {
			if ( !this.isOpen ) {
				this.$emit( 'question-opened', this.idx );
				this.collapse = false;
			} else {
				this.collapse = true;
			}

		}
	}
};
</script>