<?php

class DirectorContent extends DirectorWrapper {
	public function get($id) {
		$this->parent->post[] = 'data[content_id]=' . $id;
		$response = $this->parent->send('get_content', 'get_content_' . $id);
		return $response->content;
	}
	
	public function all($options = array()) {
		$defaults = array(	'limit' => 0,
							'only_images' => false, 
							'only_active' => true,
							'sort_on' => 'created_on', 
							'sort_direction' => 'DESC', 
							'tags' => array(),
							'scope' => array());
		$options = array_merge($defaults, $options);
		$tail = 'get_content_list_';
		if ($options['limit'] > 0) {
			$this->parent->post[] = 'data[limit]=' . $options['limit'];
		}
		if ($options['only_images']) {
			$this->parent->post[] = 'data[only_images]=1';
		}
		if ($options['only_active']) {
			$this->parent->post[] = 'data[only_active]=1';
		} else {
			$this->parent->post[] = 'data[only_active]=0';
		}
		if (!empty($options['tags']) && $options['tags'][1] == 'all') {
			$all = true;
		} else {
			$all = false;
		}
		$tail .= join('_', array($options['limit'], (int) $options['only_images'], (int) $options['only_active'], (int) $all, $options['sort_on'], $options['sort_direction']));
		if (!empty($options['scope'])) {
			$tail .= '_' . $options['scope'][0] . '_' . $options['scope'][1];
			$this->parent->post[] = 'data[scope]=' . $options['scope'][0];
			$this->parent->post[] = 'data[scope_id]=' . $options['scope'][1];
		}
		if (!empty($options['tags'])) {
			$tail .= '_' . $options['tags'][0] . '_' . (int) $all;
			$this->parent->post[] = 'data[tags]=' . $options['tags'][0];
		}
		$this->parent->post[] = 'data[tags_exclusive]=' . $all; 
		$this->parent->post[] = 'data[sort_on]=' . $options['sort_on'];
		$this->parent->post[] = 'data[sort_direction]=' . $options['sort_direction'];
		if ($options['sort_on'] == 'random') {
			$this->parent->post[] = 'data[buster]=' . rand(1,10);
		}
		$response = $this->parent->send('get_content_list', $tail);
		return $response->contents[0];
	}
}

?>