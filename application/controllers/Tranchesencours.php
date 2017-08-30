<?php
class TranchesEnCours extends EC_Controller {
	
	function load($id_modele=null, $pays=null, $magazine=null, $numero=null) {
		$id_modele=$id_modele==='null' ? null : $id_modele;
		$est_load_tranche_unique = !(is_null($id_modele) && is_null($pays));
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		if ($privilege == 'Affichage') {
			$this->load->view('errorview', ['Erreur'=>'droits insuffisants']);
			return;
		}
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$resultats_tranches_en_cours = $this->Modele_tranche->get_tranches_en_cours($id_modele,$pays,$magazine,$numero);
		$resultats_tranches_en_attente = $this->Modele_tranche->get_tranches_en_attente();

		if ($est_load_tranche_unique) {
            $this->session->set_userdata('id_modele', $resultats_tranches_en_cours[0]->id);
        }

		$data = [
			'tranches_en_cours'=>$resultats_tranches_en_cours,
			'tranches_en_attente'=>$resultats_tranches_en_attente
        ];
		$this->load->view('tranchesencoursview',$data);
		
		return $data;
	}
}
