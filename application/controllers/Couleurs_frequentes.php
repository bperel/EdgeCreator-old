<?php

class Couleurs_Frequentes extends EC_Controller {
	
	function index($pays,$magazine,$numero) {
		$this->init_model();
        $this->Modele_tranche->setUsername($this->session->userdata('user'));
		$id_modele=$this->Modele_tranche->get_id_modele($pays,$magazine,$numero);
		$couleurs=$this->Modele_tranche->get_couleurs_frequentes($id_modele);

		$data = [
			'couleurs'=>$couleurs
        ];
		$this->load->view('couleursfrequentesview',$data);
	}
}