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

elgg_register_event_handler('init', 'system', 'jssor_init');

function jssor_init() {
	elgg_register_simplecache_view('js/jssor/settings.js');
	elgg_register_simplecache_view('js/jssor/gallery.js');

	elgg_register_page_handler('jssor', 'jssor_page_handler');
	#elgg_register_css('jssor', 'mod/jssor/css/jssor.css');

	elgg_register_ajax_view('jssor/gallery');
	elgg_register_ajax_view('jssor/pinfo');
	elgg_register_ajax_view('jssor/comments');

	elgg_register_plugin_hook_handler('register', 'menu:entity', 'jssor_entity_menu_setup');
	elgg_extend_view('object/album', 'jssor/album_extend');

	$action_path = dirname(__FILE__) . '/actions/jssor';
	elgg_register_action("jssor/admin/exifupdate", "$action_path/admin/exifupdate.php", 'admin');
}

function jssor_page_handler($page) {
    if (!isset($page[0])) {
		return false;
    }

    if ($page[0] == 'album') {
		elgg_require_js('jssor/settings');
		elgg_require_js('jssor/gallery');
	    include elgg_get_plugins_path() . 'jssor/pages/jssor/album.php';
	    return true;
    }

    return false;
}

function jssor_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];
	$handler = elgg_extract('handler', $params, false);
	if ($handler != 'photos') {
		return $return;
	}

	if (elgg_instanceof($entity, 'object', 'image')) {
		$album = $entity->getContainerEntity();
		$url = 'jssor/album?guid=' . $album->getGUID() . '&i=' . $entity->getGUID();
		$params = array(
			'href' => $url,
			'text' => elgg_echo('jssor:gallery:view'),
		);
		$text = elgg_view('output/url', $params);

		$options = array(
			'name' => 'gallery_view',
			'text' => $text,
			'priority' => 40,
		);
		$return[] = ElggMenuItem::factory($options);
	}

	if (elgg_instanceof($entity, 'object', 'album')) {
		$album = $entity;
		$offset = get_input('offset');
		if ($offset) {
			$url = 'jssor/album?guid=' . $album->getGUID() . '&o=' . get_input('offset');
		} else {
			$url = 'jssor/album?guid=' . $album->getGUID();
		}
		$params = array(
			'href' => $url,
			'text' => elgg_echo('jssor:gallery:view'),
		);
		$text = elgg_view('output/url', $params);

		$options = array(
			'name' => 'gallery_view',
			'text' => $text,
			'priority' => 40,
		);
		$return[] = ElggMenuItem::factory($options);
	}


	return $return;
}
