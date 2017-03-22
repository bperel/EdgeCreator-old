<?php

class Creer_Modele_Wizard extends EC_Controller {
	
	function index($pays,$magazine,$numero,$with_user) {
		$this->init_model();
        if ($with_user === 'true') {
		    $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }

		$this->Modele_tranche->creer_modele($pays,$magazine,$numero);
		$infos_insertion=$this->Modele_tranche->insert_etape($pays,$magazine,$numero, null,-1,'Dimensions');

        $options = $this->Modele_tranche->get_options($pays, $magazine, -1, $numero, false, false, null, false);
        print_r($options);
        if (count($options) > 0) {
            // Copie des dimensions si elles ont été renseignées lors d'un envoi de photos
            $this->Modele_tranche->update_etape($pays,$magazine,$numero,-1,
                ['Dimension_x'=>$options->Dimension_x, 'Dimension_y'=>$options->Dimension_y]);

            //Mise à jour de la photo principale si une photo a été spécifiée lors de l'envoi de photos
            $nom_photo_principale = $this->Modele_tranche->get_photo_principale($pays,$magazine,$numero);
            $this->Modele_tranche->update_photo_principale($pays,$magazine,$numero,$nom_photo_principale);
            $this->Modele_tranche->setUsername($this->session->userdata('user'));
        }

		$data = [
			'infos_insertion'=>$infos_insertion
        ];
		
		$this->load->view('insertview',$data);
	}
}
