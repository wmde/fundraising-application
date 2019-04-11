<template>
	<div class="footer has-background-footer">
		<div class="container">
			<div class="columns">
				<div class="column is-two-thirds">
					<div class="level">
						<div class="level-left">
							<div class="logo-footer">
								<a href="https://www.wikimedia.de/">
									<img :src="imagePath + '/images/logo-vertical-wikimedia.svg'" alt="Wikimedia Deutschland">
								</a>
							</div>
						</div>
						<div class="level-item">
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
					</div>
				</div>
				<div class="column is-one-third">
					<ul class="list-menu">
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
	.bank-data-content {
		padding-top: 10px;
		p {
			line-height: 2.5em;
		}
	}
	.spacer {
		margin-left: 3px;
	}
	.logo-footer {
		text-align: center;
		img {
			width: 124px;
			height: 106px;
		}
	}
</style>
