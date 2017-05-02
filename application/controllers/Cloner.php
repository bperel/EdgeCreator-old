<?php
class Cloner extends EC_Controller {
	
	function index($pos_relative=null,$etape_courante=null) {
		
		if (in_array(null, [$pos_relative,$etape_courante])) {
			$this->load->view('errorview', ['Erreur'=> 'Nombre d\'arguments insuffisant']);
			exit();
		}
        
		$this->load->helper('url');
		$this->load->helper('form');
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));

		$infos_insertion=$this->Modele_tranche->cloner_etape_numero($pos_relative, $etape_courante);
		
		$data = [
				'infos_insertion'=>$infos_insertion
        ];
		
		$this->load->view('insertview',$data);
		
	}
	
	function est_clonable($pays=null,$magazine=null,$numeros=null) {
		if (in_array(null, [$pays,$magazine,$numeros])) {
			$this->load->view('errorview', ['Erreur'=> 'Nombre d\'arguments insuffisant']);
			exit();
		}
		
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$numeros_clonables = $this->Modele_tranche->get_valeurs_options($pays,$magazine,explode(',',$numeros));
		
		$this->load->view('listergview', [
			'liste'=>$numeros_clonables,
			'format'=>'json'
        ]);
	}
}