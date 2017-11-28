'use strict';

var test = require( 'tape-catch' ),
	sinon = require( 'sinon' ),
	SuboptionDisplayHandler = require( '../../lib/view_handler/display_field_suboptions' )
;

test( 'Current field is selected and infotext opened, others unselected and not opened', function ( t ) {
	var allWrappers = {
			removeClass: sinon.spy()
		},
		allInfoTexts = {
			removeClass: sinon.spy()
		},
		activeInfoText = {
			addClass: sinon.spy(),
			prop: sinon.stub().withArgs( 'scrollHeight' ).returns( 472 )
		},
		activeWrapper = {
			find: sinon.stub().withArgs( '.info-text' ).returns( activeInfoText ),
			addClass: sinon.spy()
		},
		activeField = {
			parents: sinon.stub().withArgs( '.wrap-field' ).returns( activeWrapper )
		},
		fieldset = {
			find: sinon.stub(),
			css: sinon.spy()
		}
	;

	fieldset.find.withArgs( '.wrap-field' ).returns( allWrappers );
	fieldset.find.withArgs( '.info-text' ).returns( allInfoTexts );
	fieldset.find.withArgs( '.wrap-input [value="PPL"]:not(.hidden)' ).returns( activeField );

	var handler = SuboptionDisplayHandler.createSuboptionDisplayHandler( fieldset );
	handler.update( 'PPL' );

	t.ok( allWrappers.removeClass.withArgs( 'selected notselected' ).calledOnce, 'all wappers unstyled' );
	t.ok( allInfoTexts.removeClass.withArgs( 'opened' ).calledOnce, 'all infotexts closed' );

	t.ok( activeWrapper.addClass.withArgs( 'selected' ).calledOnce, 'active wrapper styled' );
	t.ok( activeInfoText.addClass.withArgs( 'opened' ).calledOnce, 'active infotext opened' );
	t.ok( fieldset.css.withArgs( 'min-height', '472px' ).calledOnce, 'height of fieldset adjusted per active infotext' );

	t.end();
} );
