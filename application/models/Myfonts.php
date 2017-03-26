<?php
class Myfonts extends CI_Model {
	var $p;
	var $chemin_image;
	static $regex_source_image='#src=[^"]+"(.*\.gif)#isU';
	var $font;
	var $color;
	var $color_bg;
	var $width;
	var $text;
	var $precision;

	var $data;
	var $im;

	function __construct($font=null,$color=null,$color_bg=null, $width=null, $text=null,$precision=18) {
		parent::__construct();
		if (is_null($font))
			return;
		$this->font=$font;
		$this->color=$color;
		$this->color_bg=$color_bg;
		if (preg_match('#^[.0-9]+$#',$width)) {
			$this->width=preg_replace('#^(.*\...).*#','$1',$width);
		}
		else {
			$this->width=$width;
		}
		$this->text=$text;
		$this->precision=$precision;

		$this->build();
	}
	
	function build() {
		$this->data = [
			'seed'=>'43',
			'dock'=>'false',
			'size'=>$this->precision*2,
			'w'=>$this->width,
			'src'=>'custom',
			'text'=>str_replace(' ','%20',$this->text),
			'fg'=>$this->color,
			'bg'=>$this->color_bg,
			'goodies'=>'ot.liga',
			urlencode('i[0]')=>urlencode($this->font.',,720,144')
        ];
		$texte_clean=str_replace("'","\'",preg_replace('#[ ]+\.$#','',$this->text));
		$requete_image_existe='SELECT ID FROM images_myfonts '
							 .'WHERE Font = \''.$this->font.'\' AND Color = \''.$this->color.'\' AND ColorBG = \''.$this->color_bg.'\''
							 .' AND Width = \''.$this->width.'\' AND Texte = \''.$texte_clean.'\'';
        $requete_image_existe_resultat = DmClient::get_query_results_from_dm_server($requete_image_existe, 'db_edgecreator');
		$image_existe=count($requete_image_existe_resultat) != 0;
		if ($image_existe && !isset($_GET['force_post'])) {
			$id_image=$requete_image_existe_resultat[0]->ID;
			$this->chemin_image=Modele_tranche::getCheminImages().'/images_myfonts/'.$id_image.'.gif';
			if (false !== (@$im=imagecreatefromgif($this->chemin_image))) { // Image stock�e, pas besoin de la reg�n�rer
				$this->im=$im;
				return;
			}
			else {
                DmClient::get_service_results(DmClient::$dm_server, 'DELETE', '/edgecreator/myfontspreview/'.$id_image, [], 'edgecreator');
			}
		}
		$this->p=new Post(
			"http://new.myfonts.com/widgets/testdrive/testdrive-ajax.php",
			$this->data,
			'GET'
		);

		$code_image=$this->p->content;
		preg_match(self::$regex_source_image, $code_image, $chemin);
		if (!array_key_exists(1, $chemin)) {
			echo 'Aucun chemin d\'image trouve sur '.$this->p->url;
			Fonction_executable::erreur('Image MyFonts non trouvee ; ('.$this->p->url.')');
		}
		else {
			$this->chemin_image=$chemin[1];
			$this->chemin_image=str_replace('\\','',$this->chemin_image);
			if (strpos($this->chemin_image,'http') !== 0) {
				$this->chemin_image='http:'.$this->chemin_image;
			}

            $resultat = DmClient::get_service_results(DmClient::$dm_server, 'PUT', '/edgecreator/myfontspreview', [
                'font' => $this->font,
                'fgColor' => $this->color,
                'bgColor' => $this->color_bg,
                'width' => $this->width,
                'text' => $texte_clean,
                'precision' => $this->precision,
            ], 'edgecreator');

            $im=imagecreatefromgif($this->chemin_image);
            imagegif($im, Modele_tranche::getCheminImages().'/images_myfonts/'.$resultat->previewid.'.gif');

            $this->im=$im;
		}
	}
}

class Post extends CI_Model {
	var $header;
	var $content;
	var $url;
	
	function __construct($url, $_data,$type='POST',$cookie='') {
		// convert variables array to string:
		$data = [];
		while(list($n,$v) = each($_data)){
			$data[] = ($n).'='.($v);
		}
		$data = implode('&', $data);

        $this->url=$url.'?'.$data;
		$this->content = DmClient::get_page($this->url);
		
		return;
		
		// For POST only
		/*
		// parse the given URL
		$url = parse_url($url);
		if ($url['scheme'] != 'http') {
			die('Only HTTP request are supported !');
		}
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];

		// open a socket connection on port 80
		$fp = fsockopen($host, 80);

		// send the request headers:
		fputs($fp, "$type $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Cookie: $cookie\r\n");
		fputs($fp, "Referer: http://coa.inducks.org\r\n");
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
		fputs($fp, "Connection: keep-alive\r\n\r\n");
		fputs($fp, $data);
		
		$result = '';
		while(!feof($fp)) {
			// receive the results of the request
			$result .= fgets($fp, 128);
		}

		// close the socket connection:
		fclose($fp);

		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);

		$header = isset($result[0]) ? $result[0] : '';
		$content = isset($result[1]) ? $result[1] : '';

		// return as array:
		$this->header=$header;
		$this->content=$content;
		*/
	}
}
?>
