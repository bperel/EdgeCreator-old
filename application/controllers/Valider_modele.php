<?php

class Valider_Modele extends EC_Controller {

	function index($nom_image,$createurs,$photographes) {
		$this->init_model();
		$this->load->library('email');
		$this->load->helper('email');
        $username=$this->session->userdata('user');
		$this->Modele_tranche->setUsername($username);

        if ($this->Modele_tranche->copier_image_temp_vers_gen($nom_image)) {
            $this->Modele_tranche->marquer_modele_comme_pret_publication($createurs,$photographes);
        }
        else {
            header("HTTP/1.0 500 Internal Server Error");
        }
	}
}