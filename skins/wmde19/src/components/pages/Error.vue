<template>
	<div class="column is-full">
		<!-- TODO create small i18n vue function to use instead of messages variable -->
		<h1 class="title">{{ $t( 'error_page_header' ) }}</h1>
		<p>
			<span v-html="$t( 'error_page' )"></span>
		</p>

		<pre v-if="errorMessage">
			{{ errorMessage }}
		</pre>

		<p>
			<a href="/"><span v-html="$t( 'error_pages_return_to_donation' )"></span></a>
		</p>

		<p class="throw-me-away-later-test">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Velit scelerisque in dictum non consectetur a erat nam. Blandit volutpat maecenas volutpat blandit aliquam. Libero nunc consequat interdum varius sit amet mattis vulputate enim. Mauris pellentesque pulvinar pellentesque habitant morbi tristique senectus. Consequat id porta nibh venenatis cras sed felis eget. Eget duis at tellus at urna condimentum mattis pellentesque id. Nulla at volutpat diam ut venenatis tellus in. Purus viverra accumsan in nisl. Tellus rutrum tellus pellentesque eu tincidunt tortor aliquam nulla facilisi. Diam donec adipiscing tristique risus nec. Risus feugiat in ante metus. Morbi tempus iaculis urna id volutpat lacus laoreet non curabitur. Fermentum dui faucibus in ornare quam viverra. Nunc non blandit massa enim nec dui nunc. Tincidunt dui ut ornare lectus sit amet est placerat. Sed euismod nisi porta lorem mollis aliquam ut porttitor.
			<br><br>
			Aliquet eget sit amet tellus. Risus commodo viverra maecenas accumsan lacus. Scelerisque eu ultrices vitae auctor eu augue. Sit amet commodo nulla facilisi nullam vehicula. Leo duis ut diam quam nulla porttitor. Tellus id interdum velit laoreet id donec ultrices. Pharetra sit amet aliquam id diam maecenas ultricies mi. Sapien nec sagittis aliquam malesuada bibendum. Aliquet enim tortor at auctor urna nunc. Tortor condimentum lacinia quis vel eros. Eu tincidunt tortor aliquam nulla facilisi. Turpis nunc eget lorem dolor sed. Est ultricies integer quis auctor elit sed vulputate mi sit. Et egestas quis ipsum suspendisse ultrices gravida dictum fusce ut.
			<br><br>
			Aliquam purus sit amet luctus. Adipiscing at in tellus integer. Sollicitudin nibh sit amet commodo nulla facilisi nullam vehicula. Semper feugiat nibh sed pulvinar proin. Suspendisse faucibus interdum posuere lorem. Urna condimentum mattis pellentesque id nibh tortor. Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci. Ac tortor vitae purus faucibus ornare suspendisse sed nisi lacus. Laoreet suspendisse interdum consectetur libero id faucibus nisl. Nullam vehicula ipsum a arcu cursus vitae. Sit amet porttitor eget dolor morbi non. Aliquam eleifend mi in nulla posuere sollicitudin aliquam ultrices. Tristique magna sit amet purus gravida. Ut tristique et egestas quis ipsum suspendisse ultrices gravida.
			<br><br>
			Rhoncus dolor purus non enim praesent elementum facilisis leo. Leo integer malesuada nunc vel risus commodo viverra maecenas accumsan. Massa vitae tortor condimentum lacinia quis vel eros donec. Augue ut lectus arcu bibendum at varius. Amet massa vitae tortor condimentum lacinia quis vel eros. Nulla aliquet porttitor lacus luctus accumsan tortor posuere. Accumsan sit amet nulla facilisi. Facilisis leo vel fringilla est ullamcorper eget nulla facilisi. Placerat duis ultricies lacus sed turpis tincidunt id aliquet. Egestas integer eget aliquet nibh praesent. Pretium quam vulputate dignissim suspendisse.
			<br><br>
			Imperdiet massa tincidunt nunc pulvinar sapien. Enim lobortis scelerisque fermentum dui faucibus. Pharetra massa massa ultricies mi. Dignissim enim sit amet venenatis urna cursus eget. Porttitor rhoncus dolor purus non enim praesent. Vitae purus faucibus ornare suspendisse sed nisi. Lorem mollis aliquam ut porttitor leo a diam. In tellus integer feugiat scelerisque varius morbi. Leo in vitae turpis massa sed elementum tempus. Auctor urna nunc id cursus metus. Tristique et egestas quis ipsum suspendisse ultrices gravida dictum. Vel pharetra vel turpis nunc eget. Id cursus metus aliquam eleifend mi in nulla posuere sollicitudin. Sed faucibus turpis in eu mi. Urna nunc id cursus metus aliquam eleifend. Felis bibendum ut tristique et egestas quis ipsum.
			<br><br>
			Leo a diam sollicitudin tempor. Faucibus purus in massa tempor nec feugiat nisl pretium fusce. Massa placerat duis ultricies lacus sed turpis tincidunt id. Convallis convallis tellus id interdum velit. Sagittis orci a scelerisque purus semper. Quis viverra nibh cras pulvinar mattis nunc sed blandit libero. Gravida quis blandit turpis cursus in hac. Ipsum faucibus vitae aliquet nec. A diam maecenas sed enim ut sem viverra aliquet. Sit amet est placerat in egestas erat imperdiet sed. Id aliquet lectus proin nibh nisl condimentum id. Et netus et malesuada fames ac turpis egestas. Leo a diam sollicitudin tempor id eu nisl nunc mi.
			<br><br>
			Lacus suspendisse faucibus interdum posuere lorem. Condimentum mattis pellentesque id nibh tortor. In aliquam sem fringilla ut morbi tincidunt. Velit ut tortor pretium viverra suspendisse. Sapien faucibus et molestie ac feugiat sed lectus. Tempus urna et pharetra pharetra massa. Id consectetur purus ut faucibus pulvinar. Est ultricies integer quis auctor elit sed. Potenti nullam ac tortor vitae purus faucibus ornare. Mi sit amet mauris commodo quis imperdiet massa. In pellentesque massa placerat duis ultricies lacus sed turpis.
			<br><br>
			In pellentesque massa placerat duis. Pellentesque eu tincidunt tortor aliquam nulla facilisi. Vulputate eu scelerisque felis imperdiet proin fermentum leo vel orci. Magna ac placerat vestibulum lectus mauris. Ipsum nunc aliquet bibendum enim facilisis gravida neque convallis a. Aenean euismod elementum nisi quis eleifend quam adipiscing vitae proin. Sagittis nisl rhoncus mattis rhoncus urna neque. Arcu odio ut sem nulla pharetra diam sit amet. Sit amet mattis vulputate enim nulla aliquet. A condimentum vitae sapien pellentesque habitant morbi tristique. In tellus integer feugiat scelerisque varius morbi enim nunc faucibus. Faucibus pulvinar elementum integer enim neque volutpat ac tincidunt vitae. Scelerisque varius morbi enim nunc.
		</p>
	</div>
</template>

<script>
export default {
	name: 'Error',
	props: [ 'errorMessage' ],
};
</script>
