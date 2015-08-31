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

$guid = $vars['guid'];
$photo = get_entity($guid);

if (!$photo) {
    return;
}

if (!($photo instanceof TidypicsImage)) {
    return;
}

if (!elgg_is_xhr()) {
    register_error('Sorry, Ajax only!');
    forward();
}

elgg_group_gatekeeper();

$album = $photo->getContainerEntity();

$owner_link = elgg_view('output/url', array(
	'href' => "photos/owner/" . $photo->getOwnerEntity()->username,
	'text' => $photo->getOwnerEntity()->name,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($photo->time_created);
$categories = elgg_view('output/categories', $vars);

$owner_icon = elgg_view_entity_icon($photo->getOwnerEntity(), 'tiny');

$subtitle = "$author_text $date $categories";

$params = array(
	'entity' => $photo,
	'title' => false,
	'subtitle' => $subtitle,
	'tags' => $tags,
);
$list_body = elgg_view('object/elements/summary', $params);

$params = array('class' => 'mbl');
$summary = elgg_view_image_block($owner_icon, $list_body, $params);

$metadata = elgg_view_menu('entity', array(
			'entity' => $vars['entity'],
			'handler' => 'jssor',
			'sort_by' => 'priority',
			'class' => 'elgg-menu-hz',
		));
echo $metadata;
echo "<br/>";
echo "<div id=\"editphoto\" style=\"padding: 5px; border: 2px dashed red; display:none;\">";
$form_vars = tidypics_prepare_form_vars($photo);
echo elgg_view_form('photos/image/save', array('method' => 'post'), $form_vars);
echo "</div>";
echo elgg_view('photos/tagging/tags', array( 'entity' => $photo));
echo $summary;

if ($photo->description) {
	echo elgg_view('output/longtext', array(
		'value' => $photo->description,
		'class' => 'mbl',
	));
}

// Comments
$attr = [
	'id' => 'comments_container',
	'class' => (array) elgg_extract('class', $vars, []),
];
$attr['class'][] = 'elgg-comments';

// work around for deprecation code in elgg_view()
unset($vars['internalid']);

$comments = "<div id=\"comments\">";
$comments .= elgg_view('jssor/comments', array('guid' => $guid));
$comments .= "</div>";

$comments .= elgg_view_form('comment/save', array(), $vars);

echo elgg_format_element('div', $attr, $comments);

