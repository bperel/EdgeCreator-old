<?php

class Couleur_Point_Photo extends EC_Controller {
	
	function index($frac_x,$frac_y) {
		$this->init_model();
		echo $this->Modele_tranche->get_couleur_point_photo($frac_x, $frac_y);
	}
}
