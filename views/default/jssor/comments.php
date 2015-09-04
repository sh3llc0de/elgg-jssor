<?php

$offset = elgg_extract('offset', $vars, get_input('offset', 0));
$limit = elgg_extract('limit', $vars, get_input('limit', 0));
if (!$limit) {
	$limit = elgg_trigger_plugin_hook('config', 'comments_per_page', [], 25);
}

$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'comment',
	'container_guid' => $vars['guid'],
	'reverse_order_by' => true,
	'full_view' => true,
	'limit' => $limit,
	'preload_owners' => true,
	'distinct' => false,
	'offset_key' => 'coffset',
	'offset' => $offset,
	'url_fragment' => 'comments_container',
));

echo $content;
