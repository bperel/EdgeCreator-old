<?php

class Couleur_Point_Photo extends EC_Controller {
	
	function index($pays,$magazine,$numero,$frac_x,$frac_y) {
		$this->init_model();
        $this->Modele_tranche->setUsername($this->session->userdata('user'));
		echo $this->Modele_tranche->get_couleur_point_photo($pays,$magazine,$numero, $frac_x, $frac_y);
	}
}
