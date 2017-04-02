<?php
class Supprimer_Wizard extends EC_Controller {
	
	function index($etape=null) {
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->supprimer_etape($etape);
	}
}