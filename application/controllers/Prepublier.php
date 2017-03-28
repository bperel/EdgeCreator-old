<?php

class Prepublier extends EC_Controller {
	
	function index($prepublier_ou_depublier, $nom_image) {
		$this->init_model();
        $prepublier = $prepublier_ou_depublier === 'true';
        if ($prepublier) {
            $this->Modele_tranche->copier_image_temp_vers_gen($nom_image);
        }
		$this->Modele_tranche->prepublier_modele($prepublier);
	}
}