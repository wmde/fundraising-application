<template>
  <div id="answer">
  <p>
	{{ content }}
	<span v-show="!collapsed">The rest of the text appears here! Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
	<span v-if="isPreview" @click="collapsed = !collapsed;" class="link"
		data-content-target="/page/Häufige Fragen" 
		data-track-content 
		data-content-name="Toggle expand/collapse" 
		data-content-piece="Toggle expand/collapse">
	{{ toggleMessage }}
	</span>
	</p>
  </div>
</template>

<script>
export default {
	name: 'answer',
	data() {
		return {
			collapsed: true
		};
	},
	computed: {
		toggleMessage() {
			return this.collapsed ? 'Expand' : 'Collapse';
		}
	},
	props: [ 'content', 'isPreview' ]
};
</script>