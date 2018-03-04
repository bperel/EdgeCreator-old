<?php
if (isset($erreur)) {
    ?>Erreur : <?=$erreur?><?php
}
else {
    ?>Envoi réalisé avec succès !
    <?php
    if (isset($est_photo_tranche)) {?>
        <script type="text/javascript">
			if (window.parent.$('wizard-photos')
				&& window.parent.$('wizard-photos').parent().is(':visible')) {
				window.parent.lister_images_gallerie('Photos');
			}
			else {
				window.parent.afficher_photo_tranche();
			}
        </script><?php
    }
    ?>
    <script type="text/javascript">
        var nomFichier = '<?=$nomFichier?>';
        window.parent.nom_photo_tranches_multiples = nomFichier;
		window.parent.$('.ui-dialog:visible')
            .find('button')
            .filter(function() {
                return window.parent.$(this).text() === 'Suivant';
            }).button('option','disabled', false);

			var galleryPanel = window.parent.$(window.parent.$.find('.accordion:visible h3:eq(2)'));
			galleryPanel.trigger('click');
			setTimeout(function() {
				var imageInGallery = window.parent.$(window.parent.$.find('.gallery img[title="' + nomFichier + '"]'));
				imageInGallery.trigger('click');
			}, 500);
    </script><?php
}
if (isset($proposer_autre_envoi)) {?>
<div>
    <a href="<?=$self_url?>">Autre envoi</a>
</div>
<?php }