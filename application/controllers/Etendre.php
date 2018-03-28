<?php
class Etendre extends EC_Controller {
	static $pays;
	static $magazine;
	static $numero;
	static $nouveau_numero;
	
	function index($pays=null,$magazine=null,$numero=null,$nouveau_numero=null) {
		
		try {
			if (in_array(null, [$pays,$magazine,$numero,$nouveau_numero])) {
				$this->load->view('errorview', ['Erreur'=> 'Nombre d\'arguments insuffisant']);
				exit();
			}
			self::$pays=$pays;
			self::$magazine=$magazine;
			self::$numero=$numero;
			self::$nouveau_numero=$nouveau_numero;

			$this->load->helper('url');
			
			$this->init_model();

			$privilege=$this->Modele_tranche->get_privilege();
			if ($privilege === 'Affichage') {
				$this->load->view('errorview', ['Erreur'=>'droits insuffisants']);
				return;
			}
			$this->Modele_tranche->setUsername($this->session->userdata('user'));
			
			//$this->Modele_tranche->dupliquer_modele_magazine_si_besoin(self::$pays,self::$magazine);
			
			$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
			$this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
			$resultat_clonage = $this->Modele_tranche->etendre_numero($pays,$magazine,$numero,$nouveau_numero);

            $this->load->view('listergview', [
                'liste'=>[
                    'resultat_clonage' => $resultat_clonage
                ],
                'format'=>'json'
            ]);
		}
		catch (Exception $e) {
	    	echo 'Exception reçue : ',  $e->getMessage(), "\n";
	    	echo '<pre>';print_r($e->getTrace());echo '</pre>';
		}
	}
}
