<?php
class TranchesEnCours extends EC_Controller {

	function load($id_modele=null) {
		$id_modele=$id_modele==='null' ? null : $id_modele;

		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));

        if (is_null($id_modele)) {
            $resultats_tranches_en_cours = $this->Modele_tranche->get_tranches_en_cours();
            $resultats_tranches_en_attente = $this->Modele_tranche->get_tranches_en_attente();
            $resultats_tranches_en_attente_d_edition = $this->Modele_tranche->get_tranches_en_attente_d_edition();

            $data = [
                'tranches_en_cours'=>$resultats_tranches_en_cours,
                'tranches_en_attente'=>$resultats_tranches_en_attente,
                'tranches_en_attente_d_edition'=>$resultats_tranches_en_attente_d_edition
            ];
        }
        else {
            $resultats_tranches_en_cours = $this->Modele_tranche->get_tranches_en_cours($id_modele);

            $this->session->set_userdata('id_modele', $id_modele);
            $this->session->set_userdata('pays', $resultats_tranches_en_cours[0]->pays);
            $this->session->set_userdata('magazine', $resultats_tranches_en_cours[0]->magazine);
            $this->session->set_userdata('numero', $resultats_tranches_en_cours[0]->numero);

            $data = [
                'tranches_en_cours'=>$resultats_tranches_en_cours
            ];
        }
		$this->load->view('tranchesencoursview',$data);

		return $data;
	}
}
