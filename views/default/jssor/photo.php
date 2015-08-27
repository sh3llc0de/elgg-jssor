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


$image = $vars['image'];
$thumb_url = elgg_normalize_url($image->getIconURL('tiny'));
$photo_url = elgg_normalize_url($image->getIconURL('large'));
$index = $vars['i'];

$html =  "<div>\n";
$html .= "<img u=\"image\" src=\"$photo_url\" />\n";
$html .= "<img u=\"thumb\" src=\"$thumb_url\" />\n";
if (($vars['disable_captions'] != "true") && elgg_get_plugin_setting('enable_captions', 'jssor', 0) && $image->getTitle()) {
    $html .= "<div u=caption t=\"*\" class=\"captionOrange\"  style=\"position:absolute; left:20px; top: 30px; width:300px; height:30px;\">";
    $html .= $image->getTitle();
    $html .= "</div>\n";
}
$html .= "<div id=\"photoinfo" . $vars['i'] . "\"\n";
//description=\"". $image->description . "\"\n";
$html .= "index=\"". $index . "\"\n";
$html .= "guid=\"". $image->getGUID() . "\"\n";
$exif = unserialize($image->tp_exif);
if ($exif['latitude'] && $exif['longitude']) {
    $html .= "latitude=\"". $exif['latitude']  . "\"\n";
    $html .= "longitude=\"". $exif['longitude'] . "\"\n";
}
if ($exif['DateTime']) {
    $date = date_create_from_format('Y:m:d G:i:s', $exif['DateTime']);
    $html .= " captured=\"". date_format($date, 'F d Y G:i:s') . "\"\n";
}
$html .= " title=\"". $image->getTitle() . "\"\n";
$html .= " thumb=\"". $thumb_url . "\"\n";
$html .= "></div>";
$html .= "</div>\n";

echo $html;
