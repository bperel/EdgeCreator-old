<?php

class Update_Photo extends EC_Controller {
	
	function index($nom_fichier_photo_principale,$est_utilisateur_affecte='true') {
		
		$this->init_model();
        if ($est_utilisateur_affecte === 'true') {
		    $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }
		$this->Modele_tranche->update_photo_principale($nom_fichier_photo_principale);
	}
}