<?php
class JS extends EC_Controller {
	
	function index($nom1='',$nom2='') {
		
		$str=file_get_contents(getcwd().'/helpers/'.$nom1.(empty($nom2)?'':'/'.$nom2));
		$data= ['contenu'=>$str];
		
		$this->load->view('jsview',$data);
		
	}
}