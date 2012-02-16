<?php

class DirectorUser extends DirectorWrapper {
	
	function scope($options = array()) {
		$defaults = array('all' => false);
		$options = array_merge($defaults, $options);
		if (!isset($options['model']) || !isset($options['id'])) {
			$this->handle_error('Required parameter missing in user->scope');
		} else {
			$this->parent->user_scope = array($options['model'], $options['id'], $options['all']);
		}
	}
	
	function all($options = array()) {
		$defaults = array('sort' => 'name');
		$options = array_merge($defaults, $options);
		$tail = 'get_users_';
		if (!empty($this->parent->user_scope)) {
			$this->parent->post[] = 'data[user_scope_model]=' . $this->parent->user_scope[0];
			$this->parent->post[] = 'data[user_scope_id]=' . $this->parent->user_scope[1];
			$this->parent->post[] = 'data[user_scope_all]=' . $this->parent->user_scope[2];
			$tail .= $this->parent->user_scope[0] . '_' . $this->parent->user_scope[1] . '_' . (int) $this->parent->user_scope[2] . '_';
		} else {
			$tail .= '0_0_0_';
		}
		$this->parent->post[] = 'data[user_sort]=' . $options['sort'];
		$tail .= $options['sort'];
		$response = $this->parent->send('get_users', $tail);
		return $response->users[0];
	}
}

?>