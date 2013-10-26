<?php

class Creer_Modele_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$with_user) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
        if ($with_user === 'true') {
		    $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }
		$this->Modele_tranche->creer_modele($pays,$magazine,$numero);
		$infos_insertion=$this->Modele_tranche->insert_etape($pays,$magazine,$numero,'_',-1,'Dimensions');

		$data = array(
			'infos_insertion'=>$infos_insertion
		);
		
		$this->load->view('insertview',$data);
	}
}

?>
