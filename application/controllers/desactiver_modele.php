<?php

class Desactiver_Modele extends EC_Controller {
	
	function index($pays,$magazine,$numero) {
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$this->Modele_tranche->desactiver_modele($pays,$magazine,$numero);
	}
}