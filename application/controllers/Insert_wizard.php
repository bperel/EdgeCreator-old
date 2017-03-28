<?php

class Insert_Wizard extends EC_Controller {
	
	function index($pos,$etape,$nom_fonction) {
		$this->init_model();
		$infos_insertion=$this->Modele_tranche->insert_etape($pos, $etape, $nom_fonction);

		$data = [
			'infos_insertion'=>$infos_insertion
        ];
		
		$this->load->view('insertview',$data);
	}
}