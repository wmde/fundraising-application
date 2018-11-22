<template>
<div id="question" class="space-above">
	<div v-bind:class="[ isOpen ? 'border-secondary' : 'border-primary' ]">
		<div class="inline-icon clickable" @click="toggle()">
			<h5 v-bind:class="[ isOpen ? 'secondary-color' : 'title-primary-color' ]">
				{{ content.question }}
			</h5>
			<i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up secondary-color' : 'icon-keyboard_arrow_down primary-color' ]"
			   data-content-target="/page/Häufige Fragen"
			   data-track-content
			   data-content-name="Toggle expand/collapse"
			   data-content-piece="Toggle expand/collapse">
			</i>
		</div>
		<div v-show="isOpen" v-html="content.visible_text"></div>
	</div>
</div>
</template>

<script>
export default {
	name: 'question',
	props: [ 'content' ],
	data() {
		return {
			isOpen: false
		};
	},
	methods: {
		toggle: function () {
			this.isOpen = !this.isOpen;
		}
	}
};
</script>