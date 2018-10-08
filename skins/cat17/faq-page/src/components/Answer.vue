<template>
  <div id="answer">
  <p>
	{{ visibleContent }}
	<span v-show="!collapsed || !isPreview">{{ hiddenContent }}</span>
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
		toggleMessage: function () {
			return this.collapsed ? this.messages.expand : this.messages.collapse;
		}
	},
	props: ['hiddenContent', 'visibleContent', 'messages', 'isPreview']
};
</script>