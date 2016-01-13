/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke <gabriel.birke@wikimedia.de>
 */

/**
 * Load and render current donation comments
 *
 * @constructor
 */
var DonationCommentsLightbox = function ( container ) {
	this.container = container;
};

$.extend( DonationCommentsLightbox.prototype, {
	init: function () {
		var self = this,
				paginationContainer = this.container.parent().find( '.pagination' );
		this.container.html( 'Spendenkommentare werden geladen ...' );
		paginationContainer.find( '.first' ).bind( 'click', $.proxy( this.goToFirstPage, this ) );
		paginationContainer.find( '.last' ).bind( 'click', $.proxy( this.goToLastPage, this ) );
		paginationContainer.find( '.prev' ).bind( 'click', $.proxy( this.goToPrevPage, this ) );
		paginationContainer.find( '.next' ).bind( 'click', $.proxy( this.goToNextPage, this ) );
		this.currentPage = 0;
		this.itemsPerPage = 10;
		this.numPages = 1;
		this.update();
	},

	update: function () {
		var self = this;
		$.ajax( 'json.php?n=100&anon=1', {
			dataType: 'json',
			success: function ( data ) {
				self.numPages = Math.ceil( data.length / self.itemsPerPage );
				self.container.html( self.renderHtml( data ) );
				self.updatePagination();
			}
		} );
	},

	renderHtml: function ( data ) {
		var html = '',
				currentPageHtml = '',
				currentPage = 0,
				self = this;
		if ( !data.length ) {
			return '<div class="noDonationComments">Zur Zeit gibt es keine Spendenkommentare</div>';
		}
		$( data ).each( function ( index, item ) {
			if ( index && index % self.itemsPerPage === 0 ) {
				html += '<div class="donationPage" id="dnp' + currentPage + '">' + currentPageHtml + '</div>';
				currentPageHtml = '';
				currentPage++;
			}
			currentPageHtml += '<div class="donationComment"><div class="donationCommentMeta">' + item.betrag +
					' &euro; von ' + item.spender + ' am ' + self._renderDate( item.datum ) + '</div>' +
					item.kommentar + '</div>';
		} );
		if ( data.length % this.itemsPerPage ) {
			html += '<div class="donationPage" style="display: none" id="dnp' + currentPage + '">' + currentPageHtml + '</div>';
		}
		return html;
	},

	goToFirstPage: function () {
		this.currentPage = 0;
		this.updatePagination();
	},

	goToLastPage: function () {
		this.currentPage = this.numPages - 1;
		this.updatePagination();
	},

	goToPrevPage: function () {
		if ( this.currentPage > 0 ) {
			this.currentPage--;
		}
		this.updatePagination();
	},

	goToNextPage: function () {
		if ( this.currentPage < this.numPages - 1 ) {
			this.currentPage++;
		}
		this.updatePagination();
	},

	updatePagination: function () {
		var paginationElement = $( '.pagination', this.container.parent() );
		// Show current page
		$( '.donationPage', this.container ).hide();
		$( '#dnp' + this.currentPage ).show();

		// Show page numbers
		paginationElement.find( '.currentPage' ).text( this.currentPage + 1 );
		paginationElement.find( '.numPages' ).text( this.numPages );

		// set visibility of back and forward arrows depending on this.numPages and this.currentPage
		paginationElement.find( '> span' ).hide();
		if ( this.numPages > 1 ) {
			paginationElement.find( '.pages' ).show();
		}
		if ( this.currentPage > 0 ) {
			paginationElement.find( '.prev' ).show();
			paginationElement.find( '.first' ).show();
		}
		if ( this.currentPage < this.numPages - 1 ) {
			paginationElement.find( '.next' ).show();
			paginationElement.find( '.last' ).show();
		}
	},

	_formatTwoDigitNumber: function ( n ) {
		return n < 10 ? '0' + n : n;
	},

	_renderDate: function ( dateString ) {
		var donationDate = new Date( dateString ),
				donationDateParts = [
					this._formatTwoDigitNumber( donationDate.getDate() ),
					'.',
					this._formatTwoDigitNumber( donationDate.getMonth() + 1 ),
					'.',
					donationDate.getFullYear(),
					' um ',
					donationDate.getHours(),
					':',
					this._formatTwoDigitNumber( donationDate.getMinutes() ),
					' Uhr'
				];
		return donationDateParts.join( '' );
	}

}	);

$( function () {

	$( '#donationCommentLink' ).click( function() {
		var commentContainer = $( '#wlightbox-spendenkommentare .commentContainer' ),
				lightbox;
		if ( commentContainer.length < 1 ) {
			return;
		}
		lightbox = new DonationCommentsLightbox( commentContainer );
		lightbox.init();
	} );

} );
