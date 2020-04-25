<?php
class ParametrageG_wizard extends EC_Controller {
	static $pays;
	static $magazine;
	static $numero;
	static $etape;

	function index($etape=null, $nom_option_sel='null') {
		self::$etape=$etape === 'null' ? null : $etape;
		$nom_option=$nom_option_sel === 'null' ? null : $nom_option_sel;

		$this->load->helper('url');
		$this->load->helper('form');

		$this->init_model();

		$this->Modele_tranche->setUsername($this->session->userdata('user'));

		if (is_null(self::$etape)) { // Liste des étapes
			$etapes=$this->Modele_tranche->get_etapes_simple();
			if (count($etapes) === 0) {
				$fonction_dimension=new CountableObject();
				$fonction_dimension->Ordre=-1;
				$fonction_dimension->Nom_fonction='Dimensions';
				$etapes[]=$fonction_dimension;
			}
			$data= ['etapes'=>$etapes];
		}
		else {
			$fonction=$this->Modele_tranche->get_fonction_ec_v2(self::$etape);
			$options = new CountableObject();
			if (is_null($fonction)) {// Etape temporaire ou dimensions
				if (self::$etape === -1) {
					$fonction=new CountableObject();
					$fonction->Nom_fonction='Dimensions';
				}
				else {
                    $options = $this->Modele_tranche->get_options_ec_v2(self::$etape, true, true, $nom_option);
                }
			}
			else if ($this->Modele_tranche->has_no_option_ec_v2()) {
                $options=$this->Modele_tranche->get_noms_champs($fonction->Nom_fonction);
            }
            else {
$options=$this->Modele_tranche->get_options_ec_v2(self::$etape, true, false, $nom_option);
            }

			$data = [
				'options'=>$options
            ];
		}
		$this->load->view('parametragegview',$data);
	}
}
