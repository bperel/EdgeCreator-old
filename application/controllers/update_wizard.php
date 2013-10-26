<?php

class Update_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$etape,$parametrage,$est_utilisateur_affecte=true) {
		parse_str($parametrage,$parametrage);
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
        if ($est_utilisateur_affecte === true) {
            $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }
		$this->Modele_tranche->update_etape($pays,$magazine,$numero,$etape,$parametrage);
	}
}

?>
