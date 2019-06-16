<?php

class Check_Logged_In extends EC_Controller {
	
	function index() {
		$this->init_model();
		$user=$this->session->userdata('user');
		if (isset($user)) {
            $this->load->model('Modele_tranche_Wizard','Modele_tranche');
            $privilege=$this->Modele_tranche->get_privilege();
            echo json_encode(['username' => $user, 'privilege' => $privilege]);
        }
		else {
		    echo json_encode([]);
        }
	}
}
