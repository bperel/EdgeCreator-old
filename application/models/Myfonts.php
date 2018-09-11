<?php
class Myfonts extends CI_Model {
    private static $myFontsSessionIdsKey = 'myfonts_session_ids';

    private $font;
    private $color;
    private $color_bg;
    private $width;
    private $text;
    private $precision;

	public $im;

	function __construct($font=null,$color=null,$color_bg=null, $width=null, $text=null,$precision=18) {
		parent::__construct();
		if (is_null($font)) {
		    return;
        }
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

    /**
     * @param string $fontBrand
     * @param string $fontFamily
     * @param string $fontVariant
     * @return string
     * @throws Exception
     */
    private function getMyFontsSessionId($fontBrand, $fontFamily, $fontVariant) {
        $fontVariantKey = implode('/', [$fontBrand, $fontFamily, $fontVariant]);
        $storedMyFontsSessionIds = $this->session->userdata(self::$myFontsSessionIdsKey);
        if (!is_array($storedMyFontsSessionIds)) {
            $this->session->set_userdata(self::$myFontsSessionIdsKey, $storedMyFontsSessionIds = []);
        }
        if (array_key_exists($fontVariantKey, $storedMyFontsSessionIds)) {
            return $storedMyFontsSessionIds[$fontVariantKey];
        }
        else {
            $url = "https://www.myfonts.com/fonts/$fontBrand/$fontFamily/";
            $doc = new DOMDocument();
            if (!$doc->loadHTML(file_get_contents($url))) {
                throw new Exception("Couldn't load from URL $url");
            }
            $xpath = new DOMXpath($doc);

            $elements=$xpath->query("//*[contains(@class, 'testdrive_container')]/a[contains(@href,'/$fontVariant/')]//img");
            if ($elements->length === 0) {
                throw new Exception("Couldn't find variant $fontVariant in $url");
            }
            $previewUrl = $elements->item(0)->getAttribute('data-src');
            if (empty($previewUrl)) {
                throw new Exception("Couldn't find preview's URL for variant $fontVariant in $url");
            }
            preg_match('#(?<=id=)[^&]+#', $previewUrl, $match, PREG_OFFSET_CAPTURE, 0);
            if (count($match) === 0) {
                throw new Exception("Couldn'f find session ID in URL $previewUrl");
            }
            $sessionId = $match[0][0];

            $this->session->set_userdata(self::$myFontsSessionIdsKey, array_merge($storedMyFontsSessionIds, [$fontVariantKey => $sessionId]));
            return $sessionId;
        }
    }

    /**
     * @param string $url
     * @param array $data
     * @return resource
     */
    private function downloadPreview($url, $data) {
        return imagecreatefrompng($url.'?'.http_build_query($data));
    }
	
	private function build() {
		$texte_clean=str_replace("'","\'",preg_replace('#[ ]+\.$#','',$this->text));
		$requete_image_existe="
          SELECT ID FROM images_myfonts
          WHERE Font  = '{$this->font}'  AND Color = '{$this->color}' AND ColorBG = '{$this->color_bg}'
            AND Width = '{$this->width}' AND Texte = '$texte_clean'";
        $requete_image_existe_resultat = DmClient::get_query_results_from_dm_server($requete_image_existe, 'db_edgecreator');
		$image_existe=count($requete_image_existe_resultat) > 0;
		if ($image_existe) {
			$id_image=$requete_image_existe_resultat[0]->ID;
			if (false !== (@$im=imagecreatefromgif(Modele_tranche::getCheminImages().'/images_myfonts/'.$id_image.'.gif'))
             || false !== (@$im=imagecreatefrompng(Modele_tranche::getCheminImages().'/images_myfonts/'.$id_image.'.png'))) { // Image stockée, pas besoin de la régénérer
				$this->im=$im;
				return;
			}

            DmClient::get_service_results_ec(DmClient::$dm_server, 'DELETE', '/edgecreator/myfontspreview/' . $id_image, []);
        }

        list($fontBrand, $fontFamily, $fontVariant) = explode('/', $this->font);

		try {
            libxml_use_internal_errors(true);
            $this->im = $this->downloadPreview('https://render.myfonts.net/fonts/font_rend.php', [
                'id'=>$this->getMyFontsSessionId($fontBrand, $fontFamily, $fontVariant),
                'rs'=>$this->precision*2,
                'w'=>$this->width,
                'src'=>'custom',
                'rbe'=>'fixed',
                'rt'=>str_replace(' ','%20',$this->text),
                'fg'=>$this->color,
                'bg'=>$this->color_bg
            ]);

            $result = DmClient::get_service_results_ec(DmClient::$dm_server, 'PUT', '/edgecreator/myfontspreview', [
                'font' => $this->font,
                'fgColor' => $this->color,
                'bgColor' => $this->color_bg,
                'width' => $this->width,
                'text' => $texte_clean,
                'precision' => $this->precision,
            ]);

            imagepng($this->im, Modele_tranche::getCheminImages()."/images_myfonts/{$result->previewid}.png");
        }
        catch(Exception $e) {
            Fonction_executable::erreur("Erreur : {$e->getMessage()}");
        }
        finally {
            libxml_use_internal_errors(false);
        }
	}
}
