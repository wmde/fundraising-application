<template>
	<div class="footer columns has-background-mid-blue-gray">
		<div class="container">
			<div class="columns">
				<div class="column">
					<div class="logo-footer">
						<img :src="imagePath + '/images/logo-vertical-wikimedia.svg'" alt="Wikimedia Deutschland" width="124" height="106">
					</div>
				</div>
				<div class="column">
					<div class="bank-data-content">
						<p><strong>Kontoinhaber:</strong> Wikimedia Foerdergesellschaft</p>
						<p>
							<strong>IBAN:</strong>
							<span class="spacer"></span>DE33
							<span class="spacer"></span>1002
							<span class="spacer"></span>0500
							<span class="spacer"></span>0001
							<span class="spacer"></span>1947
							<span class="spacer"></span>00
						</p>
						<p><strong>BIC:</strong> BFSWDE33BER</p>
						<p>Bank für Sozialwirtschaft</p>
						<p><strong>Kontonummer:</strong> 1194700</p>
						<p><strong>BLZ:</strong> 10020500</p>
					</div>
				</div>
				<div class="column">
					<ul>
						<li>
							<a href="/contact/get-in-touch">
								<span>Kontakt</span>
							</a>
						</li>
						<li>
							<a href="/page/Impressum">
								<span>Impressum</span>
							</a>
						</li>
						<li>
							<a href="/page/Datenschutz">
								<span>Datenschutz</span>
							</a>
						</li>
						<li>
							<a href="/page/Unterst%C3%BCtzerliste">
								<span>Unterstützerliste</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Footer',
	props: [ 'imagePath' ],
};
</script>

<style lang="scss">
	.spacer {
		margin-left: 3px;
	}
</style>
