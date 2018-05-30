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
	},
	classes: {
		NO_COMMENTS: 'noDonationComments',
		COMMENT_ITEM: 'comment-item',
		COMMENT_AMOUNT_NAME: 'field-amount-name',
		COMMENT_DATE: 'date-time'
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
				self.commentContainer.html( self.getHtml( data ) );
				self.updatePagination();
			}
		} );
	},

	getHtml: function ( data ) {
		var html = $('<div></div>'),
				currentPage, pageContainer,
				dataPages = this.paginateData( data ),
				self = this;
		if ( !data.length ) {
			return html
				.addClass( DOM_SELECTORS.classes.NO_COMMENTS )
				.text( this.commentContainer.data( DOM_SELECTORS.data.NO_COMMENTS ) );
		}
		for ( currentPage = 0; currentPage < this.numPages; currentPage++ ) {
			pageContainer = $( '<div></div>' )
				.addClass( 'wrap-items comment-page comment-page-' + currentPage );
			$.each( dataPages[currentPage], function( index, item ) {
				pageContainer.append(
					$( '<article></article>' ).addClass( DOM_SELECTORS.classes.COMMENT_ITEM ).append(
						$( '<span></span>' )
							.addClass( DOM_SELECTORS.classes.COMMENT_AMOUNT_NAME )
							.text( item.betrag + ' € von ' + item.spender ),
						$( '<span></span>' )
							.addClass( DOM_SELECTORS.classes.COMMENT_DATE )
							.text( self._renderDate( item.datum ) ),
						$( '<p></p>' )
							.text( item.kommentar )
					)
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
		this.paginationContainer.find( '.controls' ).addClass( 'inactive' );
		if ( this.currentPage > 0 ) {
			this.paginationContainer.find( '.prev' ).removeClass( 'inactive' );
			this.paginationContainer.find( '.first' ).removeClass( 'inactive' );
		}
		if ( this.currentPage < this.numPages - 1 ) {
			this.paginationContainer.find( '.next' ).removeClass( 'inactive' );
			this.paginationContainer.find( '.last' ).removeClass( 'inactive' );
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
		$( '.comment-paginationContainer' )
	);
	comments.init();
} );
