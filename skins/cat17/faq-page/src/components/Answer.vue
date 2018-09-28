<template>
  <div id="answer" class="space-around">
  	<p>
	{{ content }}
		<a class="space-left" href="">some long german word here...</a>
	</p>
	
  </div>
</template>

<script>

export default {
	name: 'answer',
	props: ['content']
}
</script>