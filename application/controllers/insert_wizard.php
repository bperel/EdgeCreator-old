<?php

class Insert_Wizard extends EC_Controller {
	
	function index($pays,$magazine,$numero,$pos,$etape,$nom_fonction) {
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$infos_insertion=$this->Modele_tranche->insert_etape($pays,$magazine,$numero,$pos,$etape,$nom_fonction);

		$data = array(
			'infos_insertion'=>$infos_insertion
		);
		
		$this->load->view('insertview',$data);
	}
}