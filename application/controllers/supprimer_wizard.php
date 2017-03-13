<?php
class Supprimer_Wizard extends EC_Controller {
	static $pays;
	static $magazine;
	static $etape;
	
	function index($pays=null,$magazine=null,$numero=null,$etape=null) {
		if (in_array(null, [$pays,$magazine,$etape,$numero])) {
			$this->load->view('errorview', ['Erreur'=>'Nombre d\'arguments insuffisant']);
			exit();
		}
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->supprimer_etape($pays,$magazine,$numero,$etape);
	}
}