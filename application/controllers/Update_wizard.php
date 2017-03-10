<?php

class Update_Wizard extends EC_Controller {
	
	function index($pays,$magazine,$numero,$etape,$parametrage,$est_utilisateur_affecte='true') {
		parse_str($parametrage,$parametrage);
		
		$this->init_model();
        if ($est_utilisateur_affecte === 'true') {
            $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }
		$this->Modele_tranche->update_etape($pays,$magazine,$numero,$etape,$parametrage);
	}
}