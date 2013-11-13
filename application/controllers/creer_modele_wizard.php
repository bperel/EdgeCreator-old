<?php

class Creer_Modele_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$with_user) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
        if ($with_user === 'true') {
		    $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }
		$this->Modele_tranche->creer_modele($pays,$magazine,$numero);
		$infos_insertion=$this->Modele_tranche->insert_etape($pays,$magazine,$numero,'_',-1,'Dimensions');

        $options = $this->Modele_tranche->get_options($pays,$magazine,-1,$numero,false,false,false,null,false);
        print_r($options);
        if (count($options) > 0) {
            // Copie des dimensions si elles ont �t� renseign�es lors d'un envoi de photos
            $this->Modele_tranche->update_etape($pays,$magazine,$numero,-1,array('Dimension_x'=>$options->Dimension_x, 'Dimension_y'=>$options->Dimension_y));

            //Mise � jour de la photo principale si une photo a �t� sp�cifi�e lors de l'envoi de photos
            $nom_photo_principale = $this->Modele_tranche->get_photo_principale($pays,$magazine,$numero,false);
            $this->Modele_tranche->update_photo_principale($pays,$magazine,$numero,$nom_photo_principale);
            $this->Modele_tranche->setUsername($this->session->userdata('user'));

            $id_modele_non_affecte = $this->Modele_tranche->get_id_modele($pays,$magazine,$numero,null,true);
            $this->Modele_tranche->desactiver_modele_par_id($id_modele_non_affecte);
        }

		$data = array(
			'infos_insertion'=>$infos_insertion
		);
		
		$this->load->view('insertview',$data);
	}
}

?>
