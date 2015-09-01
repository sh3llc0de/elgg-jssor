/*
Copyright (c) 2015, Wade Benson
All rights reserved.
*/

<?php
/*
Copyright (c) 2015, Wade Benson
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those
of the authors and should not be interpreted as representing official policies,
either expressed or implied, of the FreeBSD Project.
*/

$files = array(
	'jssor.slider.mini',
	'jquery.fullscreen.min',
);

$plugin_path = elgg_get_plugins_path();

foreach ($files as $file) {
	readfile("{$plugin_path}jssor/vendors/$file.js");
	echo "\n\n";
}

$settings = elgg_get_plugin_from_id('jssor')->getAllSettings();
$settings = [
	'enable_captions' => elgg_extract('enable_captions', $settings),
	'enable_google_maps' => elgg_extract('enable_google_maps', $settings),
];

?>

define("jssor/gallery", function(require) {
    var elgg = require("elgg");
    var $ = require("jquery");

	/*
	(function(){
		var callback = function(){},
			callbackName = 'gmapscallback'+(new Date()).getTime();
		window[callbackName] = callback;
		define(['https://maps.googleapis.com/maps/api/js?callback=' + callbackName], function(){
			return google.maps;
		});
	})();
	*/

	slider_guid = parseInt($( "#gallery" ).attr("guid"));
    slider_limit = parseInt($( "#gallery" ).attr("limit"));
	slider_offset = parseInt($( "#gallery" ).attr("offset"));
	slider_total = parseInt($( "#gallery" ).attr("total"));
	slider_href = $(".elgg-breadcrumbs li:last a").attr("href");
	slider_photo = $("#photoinfo0");
	slider_map = null;
	slider_markers = [];
	slider_disable_captions = false;

	slider_settings = <?php echo json_encode($settings); ?>;
	for (var key in slider_settings) {
	    if (slider_settings[key] == "0") {
			slider_settings[key] = false;
	    }
	}

	jQuery(document).ready(function ($) {

	    var _CaptionTransitions = [
	    //CLIP|LR
		{$Duration: 900, $Clip: 3, $Easing: $JssorEasing$.$EaseInOutCubic },
	    //CLIP|TB
		{$Duration: 900, $Clip: 12, $Easing: $JssorEasing$.$EaseInOutCubic },

	    //ZMF|10
		{$Duration: 600, $Zoom: 11, $Easing: { $Zoom: $JssorEasing$.$EaseInExpo, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 },

	    //ZML|R
		{$Duration: 600, x: -0.6, $Zoom: 11, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Zoom: $JssorEasing$.$EaseInCubic }, $Opacity: 2 },
	    //ZML|B
		{$Duration: 600, y: -0.6, $Zoom: 11, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Zoom: $JssorEasing$.$EaseInCubic }, $Opacity: 2 },

	    //ZMS|B
		{$Duration: 700, y: -0.6, $Zoom: 1, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Zoom: $JssorEasing$.$EaseInCubic }, $Opacity: 2 },

	    //RTT|10
		{$Duration: 700, $Zoom: 11, $Rotate: 1, $Easing: { $Zoom: $JssorEasing$.$EaseInExpo, $Opacity: $JssorEasing$.$EaseLinear, $Rotate: $JssorEasing$.$EaseInExpo }, $Opacity: 2, $Round: { $Rotate: 0.8} },

	    //RTTL|R
		{$Duration: 700, x: -0.6, $Zoom: 11, $Rotate: 1, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Zoom: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear, $Rotate: $JssorEasing$.$EaseInCubic }, $Opacity: 2, $Round: { $Rotate: 0.8} },
	    //RTTL|B
		{$Duration: 700, y: -0.6, $Zoom: 11, $Rotate: 1, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Zoom: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear, $Rotate: $JssorEasing$.$EaseInCubic }, $Opacity: 2, $Round: { $Rotate: 0.8} },

	    //RTTS|R
		{$Duration: 700, x: -0.6, $Zoom: 1, $Rotate: 1, $Easing: { $Left: $JssorEasing$.$EaseInQuad, $Zoom: $JssorEasing$.$EaseInQuad, $Rotate: $JssorEasing$.$EaseInQuad, $Opacity: $JssorEasing$.$EaseOutQuad }, $Opacity: 2, $Round: { $Rotate: 1.2} },
	    //RTTS|B
		{$Duration: 700, y: -0.6, $Zoom: 1, $Rotate: 1, $Easing: { $Top: $JssorEasing$.$EaseInQuad, $Zoom: $JssorEasing$.$EaseInQuad, $Rotate: $JssorEasing$.$EaseInQuad, $Opacity: $JssorEasing$.$EaseOutQuad }, $Opacity: 2, $Round: { $Rotate: 1.2} },

	    //R|IB
		{$Duration: 900, x: -0.6, $Easing: { $Left: $JssorEasing$.$EaseInOutBack }, $Opacity: 2 },
	    //B|IB
		{$Duration: 900, y: -0.6, $Easing: { $Top: $JssorEasing$.$EaseInOutBack }, $Opacity: 2 },
	    ];

		var _SlideshowTransitions = [
		//Fade in L
			{$Duration: 1200, x: 0.3, $During: { $Left: [0.3, 0.7] }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade out R
			, { $Duration: 1200, x: -0.3, $SlideOut: true, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade in R
			, { $Duration: 1200, x: -0.3, $During: { $Left: [0.3, 0.7] }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade out L
			, { $Duration: 1200, x: 0.3, $SlideOut: true, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }

		//Fade in T
		, { $Duration: 1200, y: 0.3, $During: { $Top: [0.3, 0.7] }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade out B
			, { $Duration: 1200, y: -0.3, $SlideOut: true, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade in B
			, { $Duration: 1200, y: -0.3, $During: { $Top: [0.3, 0.7] }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade out T
			, { $Duration: 1200, y: 0.3, $SlideOut: true, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }

		//Fade in LR
			, { $Duration: 1200, x: 0.3, $Cols: 2, $During: { $Left: [0.3, 0.7] }, $ChessMode: { $Column: 3 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade out LR
			, { $Duration: 1200, x: 0.3, $Cols: 2, $SlideOut: true, $ChessMode: { $Column: 3 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade in TB
			, { $Duration: 1200, y: 0.3, $Rows: 2, $During: { $Top: [0.3, 0.7] }, $ChessMode: { $Row: 12 }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade out TB
			, { $Duration: 1200, y: 0.3, $Rows: 2, $SlideOut: true, $ChessMode: { $Row: 12 }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }

		//Fade in LR Chess
			, { $Duration: 1200, y: 0.3, $Cols: 2, $During: { $Top: [0.3, 0.7] }, $ChessMode: { $Column: 12 }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade out LR Chess
			, { $Duration: 1200, y: -0.3, $Cols: 2, $SlideOut: true, $ChessMode: { $Column: 12 }, $Easing: { $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade in TB Chess
			, { $Duration: 1200, x: 0.3, $Rows: 2, $During: { $Left: [0.3, 0.7] }, $ChessMode: { $Row: 3 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade out TB Chess
			, { $Duration: 1200, x: -0.3, $Rows: 2, $SlideOut: true, $ChessMode: { $Row: 3 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }

		//Fade in Corners
			, { $Duration: 1200, x: 0.3, y: 0.3, $Cols: 2, $Rows: 2, $During: { $Left: [0.3, 0.7], $Top: [0.3, 0.7] }, $ChessMode: { $Column: 3, $Row: 12 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }
		//Fade out Corners
			, { $Duration: 1200, x: 0.3, y: 0.3, $Cols: 2, $Rows: 2, $During: { $Left: [0.3, 0.7], $Top: [0.3, 0.7] }, $SlideOut: true, $ChessMode: { $Column: 3, $Row: 12 }, $Easing: { $Left: $JssorEasing$.$EaseInCubic, $Top: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2, $Outside: true }

		//Fade Clip in H
			, { $Duration: 1200, $Delay: 20, $Clip: 3, $Assembly: 260, $Easing: { $Clip: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade Clip out H
			, { $Duration: 1200, $Delay: 20, $Clip: 3, $SlideOut: true, $Assembly: 260, $Easing: { $Clip: $JssorEasing$.$EaseOutCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade Clip in V
			, { $Duration: 1200, $Delay: 20, $Clip: 12, $Assembly: 260, $Easing: { $Clip: $JssorEasing$.$EaseInCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
		//Fade Clip out V
			, { $Duration: 1200, $Delay: 20, $Clip: 12, $SlideOut: true, $Assembly: 260, $Easing: { $Clip: $JssorEasing$.$EaseOutCubic, $Opacity: $JssorEasing$.$EaseLinear }, $Opacity: 2 }
			];

		options = {
			$AutoPlay: false,                                    //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
			$AutoPlayInterval: 1500,                            //[Optional] Interval (in milliseconds) to go for next slide since the previous stopped if the slider is auto playing, default value is 3000
			$PauseOnHover: 0,                                //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1
			$FillMode: 1,
			$Loop: 0,

			$DragOrientation: 3,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
			$ArrowKeyNavigation: false,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
			$SlideDuration: 800,                                //Specifies default duration (swipe) for slide in milliseconds

			$SlideshowOptions: {                                //[Optional] Options to specify and enable slideshow or not
				$Class: $JssorSlideshowRunner$,                 //[Required] Class to create instance of slideshow
				$Transitions: _SlideshowTransitions,            //[Required] An array of slideshow transitions to play slideshow
				$TransitionsOrder: 1,                           //[Optional] The way to choose transition to play slide, 1 Sequence, 0 Random
				$ShowLink: true                                    //[Optional] Whether to bring slide link on top of the slider when slideshow is running, default value is false
			},

			$ArrowNavigatorOptions: {                       //[Optional] Options to specify and enable arrow navigator or not
				$Class: $JssorArrowNavigator$,              //[Requried] Class to create arrow navigator instance
				$ChanceToShow: 1                               //[Required] 0 Never, 1 Mouse Over, 2 Always
			},

			$ThumbnailNavigatorOptions: {                       //[Optional] Options to specify and enable thumbnail navigator or not
				$Class: $JssorThumbnailNavigator$,              //[Required] Class to create thumbnail navigator instance
				$ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
				$Lanes: 1,
				$Loop: 0,
				//$AutoCenter: 1,

				$ActionMode: 1,                                 //[Optional] 0 None, 1 act by click, 2 act by mouse hover, 3 both, default value is 1
				$SpacingX: 8,                                   //[Optional] Horizontal space between each thumbnail in pixel, default value is 0
				$DisplayPieces: 10,                             //[Optional] Number of pieces to display, default value is 1
				$ParkingPosition: 360                          //[Optional] The offset position to park thumbnail
			}
		};

	    if (slider_settings.enable_captions) {
			options['$CaptionSliderOptions'] = {                            //[Optional] Options which specifies how to animate caption
				$Class: $JssorCaptionSlider$,                   //[Required] Class to create instance to animate caption
				$CaptionTransitions: _CaptionTransitions,       //[Required] An array of caption transitions to play caption, see caption transition section at jssor slideshow transition builder
				$PlayInMode: 1,                                 //[Optional] 0 None (no play), 1 Chain (goes after main slide), 3 Chain Flatten (goes after main slide and flatten all caption animations), default value is 1
				$PlayOutMode: 3                                 //[Optional] 0 None (no play), 1 Chain (goes before main slide), 3 Chain Flatten (goes before main slide and flatten all caption animations), default value is 1
	        };
	    }

	    slider_create();

	    //responsive code begin
		//you can remove responsive code if you don't want the slider scales while window resizes
		$(window).bind("load", slider_scale);
		$(window).bind("resize", slider_scale);
		$(window).bind("orientationchange", slider_scale);
		//responsive code end
	});

	function slider_scale() {
	    if (!$.fullscreen.isFullScreen()) {
			var parentWidth = jssor_slider1.$Elmt.parentNode.clientWidth;
			var width = Math.max(Math.min(parentWidth, 1920), 300);

			if (width)
				jssor_slider1.$ScaleWidth(width);
			else
				window.setTimeout(ScaleSlider, 30);
	    } else {
			var width = document.body.clientWidth;
			if (width)
				jssor_slider1.$ScaleWidth(Math.max(Math.min(width, 1920), 300));
			else
				window.setTimeout(ScaleSlider, 30);

	    }
	}

	function slider_update_ui() {
		//console.log('update ui');
	    if ($( "#map_container" ).dialog( "isOpen" )) {
			slider_map_center();
	    }

	    if ($( "#photo_info" ).dialog( "isOpen" ) ) {
			slider_update_photo_info();
	    }

	    var cur_photo = slider_offset + jssor_slider1.$CurrentIndex() + 1;
	    $( "#slider" ).slider( "value", slider_offset );
	    $( "#amount" ).val( cur_photo );
	    $(".elgg-breadcrumbs li:last a").attr("href", slider_href + "&offset=" + slider_offset);

	    var captured = slider_photo.attr("captured");
	    if (captured) {
			$( "#photo_captured" ).html( captured );
	    } else {
			$( "#photo_captured" ).empty();
	    }

	    $("html, body").animate({ scrollTop: $("#gallery").offset().top }, 0);
	}

	function slider_create() {
	    jssor_slider1 = new $JssorSlider$("slider1_container", options);

	    jssor_slider1.$On($JssorSlider$.$EVT_SLIDESHOW_END, function(slideIndex) {
			//console.log('EVT_SLIDESHOW_END ' + slideIndex);
			var count = jssor_slider1.$SlidesCount() - 1;
			if (slideIndex == count) {
				if ((slider_offset + slider_limit) < slider_total) {
				slider_offset += slider_limit;
				slider_update(jssor_slider1.$IsAutoPlaying());
				}
			}
	    });

	    jssor_slider1.$On($JssorSlider$.$EVT_PARK, function(slideIndex) {
			//console.log('EVT_PARK ' + slideIndex);
			slider_photo = $("#photoinfo" + slideIndex);
			slider_update_ui();
	    });

		slider_scale();
	}

	function slider_update(auto_play,move,index) {
		//console.log('update auto_play: ' + auto_play + " move: " + move + " index: " + index);
	    elgg.get('ajax/view/jssor/gallery', {
			data: {
				user_guid: elgg.session.user.guid, // querystring
				guid: slider_guid,
				limit: slider_limit,
				offset: slider_offset,
				disable_captions: slider_disable_captions,
			},
			success: function (output) {
				if (auto_play) {
					jssor_slider1.$Pause();
				}
				jssor_slider1 = null;
				$('#slider1_container').remove();
				$('#gallery').html(output);
				slider_create();
				slider_scale();
				if (auto_play) {
					jssor_slider1.$Play();
				}
				update_map_markers();
				if (move) {
					var tmp = jssor_slider1.$SlidesCount() - 1;
					if (index >= 0) tmp = index;
					slider_goto(tmp);
				}
			}
	    });
	}


	function slider_next() {
	    if ((slider_offset + slider_limit) < slider_total) {
			slider_offset += slider_limit;
			slider_update(false);
	    }
	}

	function slider_prev() {
	    if (!slider_offset) return;
			slider_offset -= slider_limit;
	    if (slider_offset < 0) slider_offset = 0;
			slider_update(false, true, -1);
	}

	function slider_fullscreen() {
	    $('#fullscreen').fullscreen();
	}

	function comments_set_delete() {
		$('#comments li.elgg-item-object-comment').each(function() {
			var $c = $(this);
			var $guid = $($c).attr('id').replace("elgg-object-","");
			$c.find('.elgg-menu-item-delete').html("<span class=\"elgg-icon-delete elgg-icon\"></span>");
			$c.find('.elgg-menu-item-delete span').click(function(event) {
				var r = confirm(elgg.echo('deleteconfirm'));
				if (r == true) {
					//console.log("delete comment");
					elgg.action('comment/delete', {
						data: {
							guid: $guid,
						},
						success: function (wrapper) {
							//console.log('delete status: ' + wrapper.status);
							if (wrapper.status == 0) {
								comments_update();
							}
						}
					});
				}
			});
		});
	}

	function comments_update() {
		elgg.get('ajax/view/jssor/comments', {
			data: {
				guid: slider_photo.attr('guid'),
			},
			success: function (output) {
				$('#comments').html(output);
				//console.log('comments updated');
				comments_set_delete();
			}
	    });

	}

	function slider_update_photo_info() {
	    elgg.get('ajax/view/jssor/pinfo', {
			data: {
				user_guid: elgg.session.user.guid, // querystring
				guid: slider_photo.attr('guid'),
			},
			success: function (output) {
				$('#photo_canvas').html(output);
				// photo delete
				$('#photo_canvas li.elgg-menu-item-delete').html("<span class=\"elgg-icon-delete elgg-icon\"></span>");
				$('#photo_canvas li.elgg-menu-item-delete span').click(function(event) {
					var r = confirm(elgg.echo('deleteconfirm'));
					if (r == true) {
						elgg.action('photos/delete', {
							data: {
								guid: slider_photo.attr('guid'),
							},
							success: function (wrapper) {
								//console.log('save status: ' + wrapper.status);
								if (wrapper.status == 0) {
									slider_update(false,true,jssor_slider1.$CurrentIndex());
								}
							}
						});
					}
				});

				// photo edit
				$('#photo_canvas li.elgg-menu-item-edit').html("<span class=\"elgg-menu-content\">Edit</span>");
				$('#photo_canvas li.elgg-menu-item-edit span').click(function(event) {
					$('#editphoto').toggle();
				});
				$('#editphoto form').submit(function(event) {
					var $form = $(this);
					var $index = jssor_slider1.$CurrentIndex();
					event.preventDefault();
					elgg.action('photos/image/save', {
						data: {
							guid: slider_photo.attr('guid'),
							title: $form.find('input[name=title]').val(),
							description: $form.find('textarea[name=description]').val(),
							tags: $form.find('input[name=tags]').val(),
						},
						success: function (wrapper) {
							//console.log("edit photo updated status: " + wrapper.status);
							if (wrapper.status == 0) {
								console.log($index);
								slider_update(false,true,$index);
							}
						}
					});
				});

				// comments
				$('#photo_info').dialog( "option", "title", slider_photo.attr('title') );
				$('#comments_container form').submit(function(event) {
					var $form = $(this);
					var $comment = $form.find('textarea[name=generic_comment]');
					//console.log("add new comment: " + $comment.val());
					event.preventDefault();
					elgg.action('comment/save', {
						data: {
							entity_guid: slider_photo.attr('guid'),
							generic_comment: $comment.val(),
						},
						success: function (wrapper) {
							//console.log('save status: ' + wrapper.status);
							if (wrapper.status == 0) {
								$comment.val('');
								comments_update();
							}
						}
					});
				});
				comments_set_delete();
			}
	    });
	}

	function slider_goto(index) {
	    jssor_slider1.$PlayTo(index);
	    slider_photo = $("#photoinfo" + index);
	}

	function update_map_markers() {
	    if (!slider_settings.enable_google_maps) return;
	    for (var i = 0; i < slider_markers.length; i++) {
			slider_markers[i].setMap(null);
	    }
	    slider_markers = [];
	    var count = jssor_slider1.$SlidesCount() - 1;
	    for (i = 0; i < count; i++) {
			var p = $("#photoinfo" + i);
			var lat = parseFloat(p.attr('latitude'));
			var lng = parseFloat(p.attr('longitude'));
			var title = p.attr('title');
			var text = "<a onclick=\"_g.goto(" + i + ")\"><b>" + title + "</b><br/>"
			text += "<img src=\"" + p.attr('thumb') + "\"></a>";
			if (lat && lng) {
				var marker = new google.maps.Marker({
				position: new google.maps.LatLng(lat, lng),
				map: slider_map,
				title: title,
				});
				set_map_maker_text(marker, text);
				slider_markers.push(marker);
			}
	    }
	    if (slider_markers && slider_markers.length) {
			$("#map_button").show();
	    } else {
			$("#map_button").hide();
	    }
	}

	function set_map_maker_text(marker, text) {
	    var infowindow = new google.maps.InfoWindow({
			content: text
	    });

	    marker.addListener('click', function() {
			infowindow.open(marker.getMap(), marker);
	    });
	}

	function slider_map_center() {
	    if (!slider_settings.enable_google_maps) return;
	    var lat = parseFloat(slider_photo.attr('latitude'));
	    var lng = parseFloat(slider_photo.attr('longitude'));
	    if (slider_map && lat && lng) {
			slider_map.setCenter(new google.maps.LatLng(lat, lng));
	    } else if (slider_map && slider_markers && slider_markers.length) {
			slider_map.setCenter(slider_markers[0].getPosition());
	    }
	}

	function initMap() {
		if (!slider_settings.enable_google_maps) return;
		if (!slider_map) {
		    var mapCanvas = document.getElementById('map_canvas');
		    var mapOptions = {
				center: new google.maps.LatLng(-34.397, 150.644),
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.ROADMAP
		    }
		    slider_map = new google.maps.Map(mapCanvas, mapOptions);
		    update_map_markers();
		}
	}

	$(function() {
	    $( "#slider" ).slider({
			range: "min",
			min: 0,
			max: slider_total - 1,
			value: slider_offset,
			slide: function( event, ui ) {
				$( "#amount" ).val( ui.value );
			},
			stop: function(event, ui) {
				slider_offset = ui.value;
				slider_update(false);
			}
	    });
	    $( "#total_photos" ).html( "/ " + slider_total );

	    if (!$.fullscreen.isNativelySupported()) {
			$("#fs_button").hide();
	    }

	    if (slider_settings.enable_google_maps) {
			$( "#map_container" ).dialog({
				autoOpen: false,
				height: 460,
				width: 530,
				minHeight: 460,
				minWidth: 530,
				maxHeight: 480,
				maxWidth: 640,
				title: "Google Maps",
			});

			$( "#map_button" ).click(function() {
				$( "#map_container" ).dialog( "open" );
				initMap();
				slider_map_center();
			});
	    } else {
			$("#map_button").hide();
	    }

	    $( "#photo_info" ).dialog({
			autoOpen: false,
			height: 460,
			width: 530,
			minHeight: 460,
			minWidth: 530,
			maxHeight: 480,
			maxWidth: 640,
			title: "Photo Info",
			modal: false,
	    });

	    $( "#pinfo_button" ).click(function() {
			$( "#photo_info" ).dialog( "open" );
			slider_update_photo_info();
	    });

	    if (slider_settings.enable_captions) {
			$( "#captions_box" ).change(function() {
				slider_disable_captions = $('#captions_box').prop('checked');
				var index = jssor_slider1.$CurrentIndex();
				slider_update(jssor_slider1.$IsAutoPlaying(), true, index);
			});
	    } else {
			$( "#captions_disable" ).hide();
			$( "#captions_box" ).hide();
	    }

	    $('#gallery').keydown(function(e) {
			var count = jssor_slider1.$SlidesCount() - 1;
			var index = jssor_slider1.$CurrentIndex();

			switch (e.which) {
				case $.ui.keyCode.UP:
					if ($.fullscreen.isNativelySupported()) {
						if ($.fullscreen.isFullScreen()) {
							$.fullscreen.exit();
						} else {
							slider_fullscreen();
						}
					}
				break;
				case $.ui.keyCode.DOWN:
					if (jssor_slider1.$IsAutoPlaying()) {
						jssor_slider1.$Pause();
					} else {
						jssor_slider1.$Play();
					}
				break;
				case $.ui.keyCode.RIGHT:
					if (index == count) {
						slider_next();
					} else {
						slider_goto(index+1);
					}
				break;
				case $.ui.keyCode.LEFT:
					if (index == 0) {
						slider_prev();
					} else {
						slider_goto(index-1);
					}
					break;
				case $.ui.keyCode.SPACE:
					if (jssor_slider1.$IsAutoPlaying()) {
						jssor_slider1.$Pause();
					} else {
						if (index == count) {
							slider_next();
						} else {
							slider_goto(index+1);
						}
					}
				break;
			}
			e.preventDefault();
	    });
	});

	return {
		play : function() {
			jssor_slider1.$Play();
		},
		pause: function() {
			jssor_slider1.$Pause();
		},
		fullscreen: function() {
			slider_fullscreen();
		},
		prev: function() {
			slider_prev();
		},
		next: function() {
			slider_next();
		},
		goto: function(index) {
			slider_goto(index);
		}
	};


});

