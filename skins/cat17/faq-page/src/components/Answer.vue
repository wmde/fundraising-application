<template>
  <div id="answer" class="space-around">
  	<p>
	{{ content }}
	<span v-if="isPreview" class="link">some long german word here...</span>
	</p>
	
  </div>
</template>

<script>

export default {
	name: 'answer',
	props: ['content', 'isPreview']
}
</script>