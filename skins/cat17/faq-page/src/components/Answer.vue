<template>
  <div id="answer" class="space-around">
  	<p>
	{{ content }}
	<!-- data-content-piece attribute should be adjusted based on how we compute the message for Expand/Collapse-->
	<span v-if="isPreview" class="link"
		data-content-target="/page/Häufige Fragen" 
		data-track-content 
		data-content-name="Toggle expand/collapse" 
		data-content-piece="Toggle expand/collapse">
	some long german word here...
	</span>
	</p>
	
  </div>
</template>

<script>

export default {
	name: 'answer',
	props: ['content', 'isPreview']
}
</script>