/**
 * @licence GNU GPL v2+
 * @author Gabriel Birke <gabriel.birke@wikimedia.de>
 */

/**
 * Load and render current donation comments
 *
 * @constructor
 */
var DonationComments = function ( commentContainer, paginationContainer ) {
	this.commentContainer = commentContainer;
	this.paginationContainer = paginationContainer;
};

var DOM_SELECTORS = {
	data: {
		NO_COMMENTS: 'no-comments'
	}
};

$.extend( DonationComments.prototype, {
	init: function () {
		this.paginationContainer.find( '.first' ).bind( 'click', $.proxy( this.goToFirstPage, this ) );
		this.paginationContainer.find( '.last' ).bind( 'click', $.proxy( this.goToLastPage, this ) );
		this.paginationContainer.find( '.prev' ).bind( 'click', $.proxy( this.goToPrevPage, this ) );
		this.paginationContainer.find( '.next' ).bind( 'click', $.proxy( this.goToNextPage, this ) );
		this.currentPage = 0;
		this.itemsPerPage = 10;
		this.numPages = 1;
		this.update();
	},

	update: function () {
		var self = this;
		$.ajax( '../list-comments.json?n=100&anon=1', {
			dataType: 'json',
			success: function ( data ) {
				self.numPages = Math.ceil( data.length / self.itemsPerPage );
				self.commentContainer.html( self.renderHtml( data ) );
				self.updatePagination();
			}
		} );
	},

	renderHtml: function ( data ) {
		var html = $('<div></div>'),
				currentPage, pageContainer,
				dataPages = this.paginateData( data ),
				self = this;
		if ( !data.length ) {
			return '<div class="noDonationComments">' + this.commentContainer.data( DOM_SELECTORS.data.NO_COMMENTS) + '</div>';
		}
		for ( currentPage = 0; currentPage < this.numPages; currentPage++ ) {
			pageContainer = $( '<div class="wrap-items comment-page comment-page-' + currentPage + '"></div>' );
			$.each( dataPages[currentPage], function( index, item ) {
				pageContainer.append(
					'<article class="comment-item">' +
					'<span class="field-amount-name">' + item.betrag + ' &euro; von ' + item.spender + '</span>' +
					'<span class="date-time">' + self._renderDate( item.datum ) + '</span>' +
					'<p>' + item.kommentar + '</p></article>'
				);
			} );
			html.append( pageContainer );
		}
		return html;
	},

	paginateData: function ( data ) {
		if ( !data.length ) {
			return [];
		}
		var pages = [],
			i = 0,
			n = data.length;

		while (i < n) {
			pages.push( data.slice( i, i += this.itemsPerPage ) );
		}

		return pages;
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
		// Show current page
		$( '.comment-page', this.commentContainer ).hide();
		$( '.comment-page-' + this.currentPage ).show();

		// Show page numbers
		this.paginationContainer.find( '.current-page' ).text( this.currentPage + 1 );
		this.paginationContainer.find( '.num-pages' ).text( this.numPages );

		// set visibility of back and forward arrows depending on this.numPages and this.currentPage
		this.paginationContainer.find( '> span' ).hide();
		if ( this.numPages > 1 ) {
			this.paginationContainer.find( '.pages' ).show();
		}
		if ( this.currentPage > 0 ) {
			this.paginationContainer.find( '.prev' ).show();
			this.paginationContainer.find( '.first' ).show();
		}
		if ( this.currentPage < this.numPages - 1 ) {
			this.paginationContainer.find( '.next' ).show();
			this.paginationContainer.find( '.last' ).show();
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
	var comments = new DonationComments(
		$( '.comment-commentContainer' ),
		$( '.comment-paginationContainer')
	);
	comments.init();
} );
