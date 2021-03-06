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
	'enable_captions' => elgg_extract('enable_captions', $settings, true),
	'enable_google_maps' => elgg_extract('enable_google_maps', $settings, true),
];

?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<?php
    if ($settings[enable_google_maps]) {
       echo "<script src=\"https://maps.googleapis.com/maps/api/js\"></script>";
    }
?>
<script>
_g = null;
require(['jssor/gallery'], function(g){
    _g = g;
});
</script>
<style>
*:focus {
    outline: none;
}
#fullscreen {
	background: black;
	text-align: center;
}
#controls {
	color: white;
	background: linear-gradient(#505050, #383838);
	padding: 10px 0px 10px 0px;
}
#gallery {
	margin: 0 auto;
}
.label {
	color: #f6931f;
}
</style>

<div id="fullscreen">
<div id="gallery" tabindex="1" guid="<?php echo $guid; ?>" offset="<?php echo $offset; ?>" limit="<?php echo $limit; ?>" total="<?php echo $album->getSize(); ?>"">
<?php
   echo elgg_view('jssor/gallery', array( 'guid' => $guid, 'limit' => $limit, 'offset' => $offset));
?>
</div> <!-- gallery -->
<div id="controls">
<span class='label'><?php echo elgg_echo("jssor:photos"); ?>:</span>
<span id="amount" readonly style="color:Gold; font-weight:bold;"></span>
<span id="total_photos">/0</span><span class='label'> ::: <?php echo elgg_echo('jssor:captured'); ?>:</span> <span id="photo_captured"></span>
<div id="slider" style="margin: 5px 20px 7px 20px;"></div>
<?php
	echo elgg_view('input/button', array( 'id' => 'play_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:play")));
	echo elgg_view('input/button', array( 'id' => 'pause_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:pause")));
	echo elgg_view('input/button', array( 'id' => 'prev_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:prev")));
	echo elgg_view('input/button', array( 'id' => 'next_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:next")));
	echo elgg_view('input/button', array( 'id' => 'fs_button', 'class' => 'elgg-button-action', 'onclick' => '_g.fullscreen()', 'value' => elgg_echo("jssor:fullscreen")));
	echo elgg_view('input/button', array( 'id' => 'map_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:googlemaps")));
	echo elgg_view('input/button', array( 'id' => 'pinfo_button', 'class' => 'elgg-button-action', 'value' => elgg_echo("jssor:photo:info")));
?>
<input id="captions_box" type="checkbox" value='1'><span id="captions_disable" class="label"><?php echo elgg_echo("jssor:disable:captions"); ?></span>
</div> <!-- controls -->
</div> <!-- fullscreen -->

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


