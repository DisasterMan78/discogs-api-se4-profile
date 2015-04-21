// semi-colon prevents concatenation issues
;(function(jQ){
		'use strict';
		jQ.DiscogsAjaxian = function( el, options )
		{
				// To avoid scope issues, use '$plugin' instead of 'this'
				// to reference this class from internal events and functions.
				var $plugin = this;

				// Access to jQuery and DOM versions of element
				$plugin.$el = jQ(el);
				$plugin.el  = el;

				// Add a reverse reference to the DOM object
				$plugin.$el.data( 'DiscogsAjaxian', $plugin );

				// Starting page number
				$plugin.page = 1;

				$plugin.init = function()
				{

						$plugin.settings = jQ.extend( {}, jQ.DiscogsAjaxian.defaultOptions, options );

						// Check ID is set
						if( $plugin.settings.discogsId === false )
						{
							$el.html( 'Discogs Artist ID not defined' );
						}

						$plugin.$el.addClass( $plugin.settings.containerClass );

					$plugin.fetchResults();

				};

				$plugin.fetchResults = function()
				{

					jQ.ajax(
					{
						dataType   : 'html',
						beforeSend : $plugin.showLoading,
						data       : {
							artist : $plugin.settings.discogsId,
							page   : $plugin.page
						},
						url        : 'discogs.php',
						success    : $plugin.showResults,
						complete   : $plugin.ajaxComplete
					});

				};

				$plugin.showLoading = function()
				{
					$plugin.$el.html( 'loading' );
				}

				$plugin.ajaxError = function( jqXHR, textStatus, errorThrown, errorOptions )
				{
					var settings     = $plugin.settings,
							errorMessage = settings.errorMessage;

					if(textStatus)
					{
						errorMessage += $plugin.templater( settings.errorStatus, textStatus );
					}

					if(errorThrown)
					{
						errorMessage += $plugin.templater( settings.errorThrown, errorThrown );
					}

					$plugin.$el.html = '<div class="' + settings.errorClass + '"">' + errorMessage + '</div>';
				}

				$plugin.showResults = function( html )
				{
					$plugin.page ++;
					$plugin.$el.html(html);
				}

				$plugin.ajaxComplete = function()
				{
					$plugin.$el.find( '.discogs-pagination' ).on( 'click', function(event)
					{
						event.preventDefault();

						$plugin.page = jQ( event.target ).data('page');

						$plugin.fetchResults();
					});
				}

				$plugin.templater = function( template, replacementsAssoc )
				{
					// template: We need a {{placeholder}}
					// replacementsAssoc = { 'placeholder' : 'replacement' }
					// result: 'We need a replacement'
					var output = template;

					jQ.each(replacements, function( index, value )
					{
						output.replace( '{{' + index + '}}', value);
					});

					return output;
				}

				$plugin.init();
		};

		jQ.DiscogsAjaxian.defaultOptions =
		{
				discogsId      : false,
				containerClass : 'discogs-artist',
				errorClass     : 'discogs-error',
				errorMessage   : 'Oh dear. Something went wrong.<br>',
				errorStatus    : 'The server said:<br>' +
													'{{textStatus}}<br>',
				errorThrown    : '{{errorThrown}}<br>'
		};

		jQ.fn.DiscogsAjaxian = function(options)
		{
				return this.each(function()
				{
						( new jQ.DiscogsAjaxian( this, options ) );
				});
		};

})(jQuery);


var userName  = jQuery( 'li[data-field-id="78"] span:nth-of-type(2)' ).html(),
		discogsId = jQuery( '.profile_fields.integration li:first-of-type span:nth-of-type(2)' ).html(),
		error;

if(typeof userName == 'undefined' ){
	userName = jQuery( 'li[data-field-id="19"] span:nth-of-type(2)' ).html() + ' ' + jQuery( 'li[data-field-id="20"] span:nth-of-type(2)' ).html();
}

console.log('typeof discogsId', typeof discogsId, discogsId, parseInt( discogsId, 10 ))

if( typeof discogsId !== 'undefined' ){

	if( discogsId == parseInt( discogsId, 10 ) ){

		discogsId = parseInt( discogsId, 10 );

		jQuery('#discogs').DiscogsAjaxian({
			discogsId : discogsId
		});

	}else{
		error = 'Error: ' + userName + '\'s Discogs ID is incorrect. It must be a number';
	}

}else{
	error = userName + ' has not added a Discogs ID to their profile';
}

if(typeof error === 'string' && error.length){
	jQuery( '#discogs' ).html( error );
	jQuery( '.tab_1980' ).hide();
}