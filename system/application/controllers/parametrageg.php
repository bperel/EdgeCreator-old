<?php
class ParametrageG extends Controller {
    static $pays;
    static $magazine;
    static $etape;
    
    function index($pays=null,$magazine=null,$etape=null) {
        
        if (in_array(null,array($pays,$magazine))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$pays=$pays;
        self::$magazine=$magazine;
        self::$etape=$etape;
        
        $this->load->library('session');
        $this->load->database();
        $this->db->query('SET NAMES UTF8');
        $this->load->helper('url');
        $this->load->helper('form');
        
        $this->load->model('Modele_tranche');
        $numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
        $this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
        $this->Modele_tranche->setPays(self::$pays);
        $this->Modele_tranche->setMagazine(self::$magazine);
        if (is_null($etape)) { // Liste des �tapes
            $etapes=$this->Modele_tranche->get_etapes_simple(self::$pays,self::$magazine);
            $data=array('etapes'=>$etapes);
        }
        else {
            $fonctions=$this->Modele_tranche->get_fonctions(self::$pays,self::$magazine,self::$etape);
            $options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$fonctions[0]->Nom_fonction, null, false, true);

            $data = array(
                'fonctions'=>$fonctions,
                'options'=>$options
            );
        }
        $this->load->view('parametragegview',$data);
    }
}

?>
