<?php

class Update_Photo extends EC_Controller {
	
	function index($nom_fichier_photo_principale) {
		
		$this->init_model();
		$this->Modele_tranche->update_photo_principale($nom_fichier_photo_principale);
	}
}