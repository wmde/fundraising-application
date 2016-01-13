/**
 * @licence GNU GPL v2+
 * @author Kai Nissen <kai.nissen@wikimedia.de>
 */

/**
 * Live donation ticker
 *
 * @constructor
 */

var DonationTicker = function() {};

$.extend( DonationTicker.prototype, {

	populate: function() {
		var self = this;
		var visibleListEntries = 4;

		$.getJSON( '../ajax.php?module=action&action=fetchDonationList', { min: visibleListEntries, max: 50 }, function( result ) {
			if( result.donations ) {
				$.each( result.donations, function( index, item ) {
					self._pushRow( item );
				} );

				$( '#donationListContainer' ).als( {
					orientation: "vertical",
					visible_items: visibleListEntries,
					start_from: $( '#donationList li' ).length - 4
				} );
			}
		} );
	},

	updateItems: function() {
		self = this;
		if( $( '#donationList li' ).length === 0 ) {
			updateInterval = null;
		} else {
			$( '#donationList li' ).each( function( index, element ) {
				if( ( $( element ).attr( 'data-ts' ) < new Date().getTime() / 1000 ) && element.offsetTop < 0 ) {
					$( '#listScrollP' ).click();
					$( '#sum-diff' ).text( self._formatNumber(
						$( '#sum-diff' ).text().replace( /\./g, '' ) - $( element ).attr( 'data-amount' )
					) );
					$( '#sum-collected' ).text( self._formatNumber(
						$( '#sum-collected' ).text().replace( /\./g, '' ) + $( element ).attr( 'data-amount' )
					) );
				}
			} );
		}
	},

	predictDonationSum: function( rand ) {
		return this._formatNumber( this._getSumCollected( rand ) );
	},

	predictDonationDifference: function( rand ) {
		var donationTarget = parseInt( $( 'input[name=donations-target]' ).val() ) || 0;
		return this._formatNumber( donationTarget - this._getSumCollected( rand ) );
	},

	_getSumCollected: function( rand ) {
		var startDonations = parseInt( $( 'input[name=donations-collected-base]' ).val() ) || 0;
		var secsPast = this._getSecondsPassed() || 0;

		return startDonations + this._getApprDonationsPrediction( secsPast, rand );
	},

	_formatNumber: function( num ) {
		num = parseInt( num ) + "";
		num = num.replace( /\./g, ',' );
		return num.replace( /(\d)(?=(\d\d\d)+(?!\d))/g, "$1." );
	},

	_getApprDonationsPrediction: function( secsPast, rand ) {
		var apprDonationsMinute = parseInt( $( 'input[name=appr-donations-per-minute]' ).val() ) || 0;
		var randFactor = 0;

		if ( rand == true ) {
			randFactor = Math.floor( (Math.random()) + 0.5 - 0.2 );
		}

		return (secsPast / 60 * (apprDonationsMinute * (100 + randFactor)) / 100);
	},

	_getSecondsPassed: function() {
		var startDate = $( 'input[name=donations-date-base]' ).val() || '';

		if( startDate !== '' ) {
			var parts = startDate.split( '-' );
			var startDateObj = new Date( parts[0], parts[1] - 1, parts[2] );
			var secsPassed = Math.floor( (new Date() - startDateObj) / 1000 );
			if ( secsPassed < 0 ) secsPassed = 0;
		}

		return secsPassed;
	},

	_pushRow: function( data ) {
		$( '#donationList' ).prepend(
			$('<li class="als-item" />')
				.append( $( '<div>' + data.date + ' Uhr</div>' ) )
				.append( $( '<div>' + data.amount + ' €</div>' ) )
				.attr( 'data-ts', data.timestamp )
				.attr( 'data-amount', data.amount )
		);
	}

} );

var updateInterval;
$( document ).ready( function() {
	if ( $( '#donationListContainer' ).length === 0 ) {
		return;
	}
	var ticker = new DonationTicker();
	ticker.populate();

	$( '#sum-diff' ).text( ticker.predictDonationDifference( false ) );
	$( '#sum-collected' ).text( ticker.predictDonationSum( true ) );

	updateInterval = setInterval( function() {
		ticker.updateItems();
	}, 1000 );
});
