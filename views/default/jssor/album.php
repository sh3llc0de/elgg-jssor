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


    $guid = get_input('guid');
    $image = get_input('i');
    if (!$guid) {
	register_error(elgg_echo('jssor:gallery:notfound'));
	forward(REFERER);
    }
    $album = get_entity($guid);
    if (!($album instanceof TidypicsAlbum)) {
	register_error(elgg_echo('jssor:gallery:notfound'));
	forward(REFERER);
    }

    $offset = get_input('o', 0);
    if (isset($image)) {
	$offset = $album->getIndex($image) - 1;
	if ($offset < 0) $offset = 0;
    }
    if (!$offset) $offset = 0;
    $limit = 10;

    $settings = elgg_get_plugin_from_id('jssor')->getAllSettings();
    $settings = [
	'enable_captions' => elgg_extract('enable_captions', $settings),
	'enable_google_maps' => elgg_extract('enable_google_maps', $settings),
    ];
?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="/mod/jssor/views/default/js/jssor/jssor.slider.mini.js"></script>
    <script type="text/javascript" src="/mod/jssor/views/default/js/jssor/jquery.fullscreen.min.js"></script>
<?php
    if ($settings[enable_google_maps]) {
	echo "<script src=\"https://maps.googleapis.com/maps/api/js\"></script>";
    }
?>
    <script>
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
                $PauseOnHover: 1,                                //[Optional] Whether to pause when mouse over if a slider is auto playing, 0 no pause, 1 pause for desktop, 2 pause for touch device, 3 pause for desktop and touch device, 4 freeze for desktop, 8 freeze for touch device, 12 freeze for desktop and touch device, default value is 1
		$FillMode: 1,
		$Loop: 0,

                $DragOrientation: 3,                                //[Optional] Orientation to drag slide, 0 no drag, 1 horizental, 2 vertical, 3 either, default value is 1 (Note that the $DragOrientation should be the same as $PlayOrientation when $DisplayPieces is greater than 1, or parking position is not 0)
                $ArrowKeyNavigation: true,   			            //[Optional] Allows keyboard (arrow key) navigation or not, default value is false
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
        slider_limit = <?php echo $limit; ?>;
	slider_offset = <?php echo $offset; ?>;
	slider_total = <?php echo $album->getSize(); ?>;
	slider_href = $(".elgg-breadcrumbs li:last a").attr("href");
	slider_photo = $("#photoinfo0");
	slider_map = null;
	slider_markers = [];
	slider_disable_captions = false;

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
	    update_map_button();

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
		$( "#photo_captured" ).html( " ::: " + elgg.echo('jssor:captured') + ": " + captured );
	    } else {
		$( "#photo_captured" ).empty();
	    }
	}

	function slider_create() {
	    jssor_slider1 = new $JssorSlider$("slider1_container", options);

	    jssor_slider1.$On($JssorSlider$.$EVT_SLIDESHOW_END, function(slideIndex) {
		var count = jssor_slider1.$SlidesCount() - 1;
		if (slideIndex == count) {
		    if ((slider_offset + slider_limit) < slider_total) {
			slider_offset += slider_limit;
			slider_update(jssor_slider1.$IsAutoPlaying());
		    }
		}
	    });

	    jssor_slider1.$On($JssorSlider$.$EVT_SLIDESHOW_START, function(slideIndex) {
		slider_photo = $("#photoinfo" + slideIndex);
		slider_update_ui();
	    });

	    jssor_slider1.$On($JssorSlider$.$EVT_PARK, function(slideIndex) {
		slider_photo = $("#photoinfo" + slideIndex);
		slider_update_ui();
	    });
	}

	function slider_update(auto_play) {
	    elgg.get('ajax/view/jssor/gallery?guid=' + <?php echo get_input('guid'); ?>, {
		data: {
		    user_guid: elgg.session.user.guid, // querystring
		    limit: slider_limit,
		    offset: slider_offset,
		    disable_captions: slider_disable_captions,
		},
		success: function (output) {
		    $('#slider1_container').remove();
		    $('#gallery').html(output);
		    slider_create();
		    slider_scale();
		    if (auto_play) {
			jssor_slider1.$Play();
		    }
		    update_map_markers();
		    slider_update_ui();
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
	    slider_update(false);
	}

	function slider_fullscreen() {
	    $('#fullscreen').fullscreen();
	}

	function slider_update_photo_info() {
	    elgg.get('ajax/view/jssor/pinfo', {
		data: {
		    user_guid: elgg.session.user.guid, // querystring
		    guid: slider_photo.attr('guid'),
		},
		success: function (output) {
		    $('#photo_canvas').html(output);
		    $('#photo_info').dialog( "option", "title", slider_photo.attr('title') );
		}
	    });
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
		var text = "<b>" + title + "</b><br/>"
		text += "<img src=\"" + p.attr('thumb') + "\">";
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
	    }
	}

	function initMap() {
		if (!slider_settings.enable_google_maps) return;
		if (!slider_map) {
		    var lat = parseFloat(slider_photo.attr('latitude'));
		    var lng = parseFloat(slider_photo.attr('longitude'));
		    if (!lat) lat = -34.397;
		    if (!lng) lng = 150.644;
		    var mapCanvas = document.getElementById('map_canvas');
		    var mapOptions = {
			center: new google.maps.LatLng(lat, lng),
			zoom: 13,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		    }
		    slider_map = new google.maps.Map(mapCanvas, mapOptions);
		}
		update_map_markers();
	}

	function update_map_button() {
	    if (slider_settings.enable_google_maps) {
		if (slider_photo.attr('latitude') && slider_photo.attr('longitude')) {
		    $("#map_button").show();
		} else {
		    $("#map_button").hide();
		}
	    } else {
		$("#map_button").hide();
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
	    });

	    $( "#pinfo_button" ).click(function() {
		$( "#photo_info" ).dialog( "open" );
		slider_update_photo_info();
	    });

	    if (slider_settings.enable_captions) {
		$( "#captions_box" ).change(function() {
		    slider_disable_captions = $('#captions_box').prop('checked');
		    slider_update(jssor_slider1.$IsAutoPlaying());
		});
	    } else {
		$( "#captions_disable" ).hide();
		$( "#captions_box" ).hide();
	    }
	});
    </script>

<div id="fullscreen">
<div id="gallery">
<?php
    echo elgg_view('jssor/gallery', array( 'guid' => $guid, 'limit' => $limit, 'offset' => $offset));
?>
</div> <!-- gallery -->
<div id="controls">
<button type="button" onclick="jssor_slider1.$Play()"><?php echo elgg_echo("jssor:play"); ?></button>
<button type="button" onclick="jssor_slider1.$Pause()"><?php echo elgg_echo("jssor:pause"); ?></button>
<button type="button" onclick="slider_prev()"><?php echo elgg_echo("jssor:prev"); ?></button>
<button type="button" onclick="slider_next()"><?php echo elgg_echo("jssor:next"); ?></button>
<button id="fs_button" type="button" onclick="slider_fullscreen()"><?php echo elgg_echo("jssor:fullscreen"); ?></button>
<button id="map_button" type="button"><?php echo elgg_echo("jssor:googlemaps"); ?></button>
<button id="pinfo_button" type="button"><?php echo elgg_echo("jssor:photo:info"); ?></button>
<input id="captions_box" type="checkbox" value='1'><span id="captions_disable"><?php echo elgg_echo("jssor:disable:captions"); ?></span>
<div id="slider"></div>
<?php echo elgg_echo("jssor:photos"); ?>:
<input type="text" id="amount" readonly style="align:right; width:100px; border:0; color:#f6931f; font-weight:bold;">
<span id="total_photos">/0</span><span id="photo_captured"></span>
</div> <!-- controls -->
<div id="map_container">
<style>
      #map_canvas {
        width: 500px;
        height: 400px;
      }
</style>
<div id="map_canvas"></div>
</div> <!-- map_container -->
<div id="photo_info">
<style>
      #photo_canvas {
        width: 500px;
        height: 400px;
      }
</style>
<div id="photo_canvas"></div>
</div> <!-- photo_info -->
</div> <!-- fullscreen -->


