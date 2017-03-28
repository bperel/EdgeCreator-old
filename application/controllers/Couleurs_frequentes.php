<?php

class Couleurs_Frequentes extends EC_Controller {
	
	function index() {
		$this->init_model();
		$couleurs=$this->Modele_tranche->get_couleurs_frequentes();

		$data = [
			'couleurs'=>$couleurs
        ];
		$this->load->view('couleursfrequentesview',$data);
	}
}