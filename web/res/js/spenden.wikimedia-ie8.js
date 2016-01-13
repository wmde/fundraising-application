$(function() {

	/* Input label pseudo class fix  */
	$('label').hover(
		function(){
			$('#' + $(this).attr('for')).addClass('__hover');
		},
		function(){
			$('#' + $(this).attr('for')).removeClass('__hover');
		}
	).mousedown(
		function(){
			$('#' + $(this).attr('for')).addClass('__active');
		}
	).mouseup(
		function(){
			$('#' + $(this).attr('for')).removeClass('__active');
		}
	);

	$(':checkbox').change(
		function(){

			if ($(this).is(':checked')) {
				$(this).addClass('__checked');
			} else {
				$(this).removeClass('__checked');
			}

			$(this).blur();
		}
	);

	$(':radio').change(
		function(evt){
			$('input[name="' + $(this).attr('name') + '"]').removeClass('__checked');

			if ($(this).is(':checked')) {
				$(this).addClass('__checked');
			} else {
				$(this).removeClass('__checked');
			}

			$(this).blur();
		}
	)

	$(':radio, :checkbox, select, button, input:button').hover(
		function(){
			$(this).addClass('__hover');
		},
		function(){
			$(this).removeClass('__hover');
		}
	).mousedown(
		function(){
			$(this).addClass('__active');
		}
	).mouseup(
		function(){
			$(this).removeClass('__active');
		}
	);

	/* onReleaseOutside */
	$(document).mouseup(function() {
		$('.__active').removeClass('__active');
	});

	$(document).ready( function () {
		
		/* ensure that checked status is shown correctly on load */
		$(':checked').addClass('__checked');
		$(':disabled').addClass('__disabled');
		$(':radio, :checkbox').hide();

		window.setTimeout(function(){
			$(':radio, :checkbox').show();
		}, 1000);
	});


    /* amount-list */
    $('.amount-list').each(function(){
      var $container = $(this);

      /* uncheck all list items when user changes custom amount text field */
      $container.find('.amount-custom :text').bind('focus', function(){
        $container.find(':radio').removeClass('__checked');
        $container.find(':radio').blur();
      });
    });


    /* personal data */
    $('#personal-data').each(function(){
      var $container = $(this);

      $container.find( ':radio' ).change( function( e ){
        // check #address-type-3 enable or disable #send-information
        if ($('#address-type-3').is(':checked')){
        	$('#send-information').addClass('__disabled');
        	$('label[for="send-information"]').blur().click();
        } else {
        	$('#send-information').removeClass('__disabled');
        	$('label[for="send-information"]').blur();
        }
      });
    });

});