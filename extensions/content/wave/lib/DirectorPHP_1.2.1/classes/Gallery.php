<?php

class DirectorGallery extends DirectorWrapper {
	public function get($id, $options = array()) {
		$defaults = array(	'limit' => null,
							'order' => 'display',
							'with_content' => true);
		$options = array_merge($defaults, $options);
		$this->parent->post[] = 'data[gallery_id]=' . $id . '&data[limit]=' . $options['limit'] . '&data[order]=' . $options['order'] . '&data[with_content]=' . (int) $options['with_content'];
		$response = $this->parent->send('get_gallery', 'get_gallery_' . $id);
		return $response->gallery;
	}
	
	public function all() {
		$response = $this->parent->send('get_gallery_list', 'get_gallery_list');
		return $response->galleries[0];
	}
}

?>