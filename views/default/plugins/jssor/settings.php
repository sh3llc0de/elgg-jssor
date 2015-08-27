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

echo elgg_echo('jssor:exif:warning');
echo "<br/>";
echo elgg_view('output/url', array(
		'text' => elgg_echo('jssor:exif:update'),
		'href' => 'action/jssor/admin/exifupdate',
		'is_action' => true,
	));


$plugin = $vars['plugin'];

if (!isset($plugin->enable_captions)) {
    $plugin->enable_captions = true;
}

if (!isset($plugin->enable_google_maps)) {
    $plugin->enable_google_maps = true;
}

echo "<br/><br/>";
$checkboxes = array('enable_captions', 'enable_google_maps');
foreach ($checkboxes as $checkbox) {
    echo '<div>';
    echo '<label>';
    echo elgg_view('input/checkbox', array(
	'name' => "params[$checkbox]",
	'value' => true,
	'checked' => (bool)$plugin->$checkbox,
    ));
    echo ' ' . elgg_echo("jssor:settings:$checkbox");
    echo '</label>';
    echo '</div>';
}
