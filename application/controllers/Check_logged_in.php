<?php

class Check_Logged_In extends EC_Controller {
	
	function index() {
		$this->init_model();
		$user=$this->session->userdata('user');
		echo isset($user) && $user!=='demo' ? 1 : 0;
	}
}