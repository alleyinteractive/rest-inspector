(function( $ ) {
	'use strict';

	// Init JSON UI
	$('.rest-inspector-json-args').each( function (index) {
        setTimeout( renderJson, 15 * index, this );
    });

})( jQuery );

function renderJson(el) {
    var json = jQuery(el).data('rest-json');
    var show_level = jQuery(el).data('rest-json-depth') || 0;

    console.log(show_level);

    renderjson.set_show_to_level(show_level);

    el.appendChild(
        renderjson(json)
    );
}