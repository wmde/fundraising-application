<template>
	<div class="navbar is-fixed-top has-background-white">
		<div class="container">
			<div class="navbar-brand">
				<a class="navbar-item">
					<img :src="imagePath + '/images/logo-horizontal-wikimedia.svg'" alt="Wikimedia Deutschland" width="144" height="29">
				</a>
				<a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false">
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
				</a>
			</div>

			<div class="navbar-menu" id="navMenu">
				<div class="navbar-start">
					<a class="navbar-item">
						Spendenkommentare
					</a>
					<a class="navbar-item">
						Häufige Fragen
					</a>
					<a class="navbar-item">
						Mittelverwendung
					</a>
					<a class="navbar-item">
						Spenden
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: 'Header',
	props: [ 'imagePath' ],
};

// Navbar burger animation
document.addEventListener( 'DOMContentLoaded', () => {
	const $navbarBurgers = Array.prototype.slice.call( document.querySelectorAll( '.navbar-burger' ), 0);
	if ( $navbarBurgers.length > 0 ) {
		$navbarBurgers.forEach( el => {
			el.addEventListener( 'click', () => {
				const target = el.dataset.target;
				const $target = document.getElementById( target );
				el.classList.toggle( 'is-active' );
				$target.classList.toggle( 'is-active' );
			} );
		} );
	}
} );

</script>
