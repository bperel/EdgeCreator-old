<?php

class Prepublier extends CI_Controller {
	
	function index($prepublier_ou_depublier, $id_modele, $pays, $magazine, $numero, $nom_image) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
        $prepublier = $prepublier_ou_depublier === 'true';
        if ($prepublier) {
            $this->Modele_tranche->copier_image_temp_vers_gen($pays, $magazine, $numero, $nom_image);
        }
		$this->Modele_tranche->prepublier_modele($id_modele, $prepublier);
	}
}

?>
