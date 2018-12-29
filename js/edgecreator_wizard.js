(function($){
	$(window).load(function () {
		$('#connexion,#deconnexion').button();
		$('.tip').tooltip();

		if (!username) {
			// afficher_dialogue_accueil();
			jquery_connexion();
		}
		else {
			init_action_bar();
			if (privilege === 'Affichage') {
				$('#wizard-envoyer-photo').addClass('first');
				launch_wizard('wizard-envoyer-photo');
			}
			else {
				launch_wizard('wizard-1');
			}
		}

		$('#zoom_slider').slider({
			value: 1 /* Valeur n°1 du tableau, donc = 1.5*/,
			min: 0,
			max: valeurs_possibles_zoom.length - 1,
			step: 1,
			change: function (event, ui) {
				zoom = valeurs_possibles_zoom[ui.value];
				$('#zoom_value').html(zoom);
				chargement_courant = 0;
				charger_previews_numeros(chargements[chargement_courant], true);
			},
			slide: function (event, ui) {
				$('#zoom_value').html(valeurs_possibles_zoom[ui.value]);
			}
		});
	});

	$.fn.d = function(){
		return this.closest('.ui-dialog');
	};

	$.fn.valeur = function(nom_option){
	if (this.hasClass('options_etape'))
		return this.find('[name="option-'+nom_option+'"]');
	else
		return this.find('[name="'+nom_option+'"]');
	};
})(jQuery);

$.widget("ui.tooltip", $.ui.tooltip, {
	options: {
		content: function () {
			return $(this).prop('title');
		}
	}
});

$.fn.remplirIntituleNumero = function(data) {
	var conteneur_intitule = $('.intitule_magazine.template').clone(true).removeClass('template');

	$.each(data, function(nom, valeur) {
		conteneur_intitule.data()[nom] = valeur;

		var element = conteneur_intitule.find('[name="'+nom+'"]');
		if (nom === 'wizard_pays') {
			element.attr({src: 'images/flags/'+valeur+'.png'});
		}
		else {
			element.text(valeur);
		}
	});

	this.html(conteneur_intitule);

	return this;
};

$.fn.afficher_libelle_numero = function(id_tranche, tranche_en_cours, avec_reference_id_tranche) {
	this
		.css({backgroundImage: 'url("images/flags/'+tranche_en_cours.pays+'.png")'})
		.data(tranche_en_cours)
		.html(tranche_en_cours.str_userfriendly);

	if (avec_reference_id_tranche) {
		this.attr({for:id_tranche});
	}

	return this;
};

$(window).scroll(function() {
	if (modification_etape && modification_etape.find('#options-etape--Polygone').length > 0) {
		var options=modification_etape.find('[name="form_options"]');
		positionner_points_polygone(options);
	}
});

var INTERVAL_CHECK_LOGGED_IN=5;
(function check_logged_in() {
	$.ajax({
		url: '/check_logged_in/',
		type: 'post',
		success:function(data) {
			if (data === '1') {
				setTimeout(check_logged_in, 1000*60*INTERVAL_CHECK_LOGGED_IN);
			}
			else {
				jqueryui_alert_from_d($('#wizard-session-expiree'),function() {
					location.replace(base_url);
				});
			}
		}
	});
})();

var current_color_input;
$(function() {
	$('#selecteur_couleur').tabs({
		activate: function(event, ui) {
			if ($(ui.newPanel).attr('id') === 'depuis_photo') {
				if ($('[name="description_selection_couleur"]:visible').length > 0) {
					$('#photo_tranche_courante img')
						.addClass('cross')
						.click(function(e) {
							var frac = [ (e.offsetX || e.clientX - $(e.target).offset().left) / $(this).width(),
										 (e.offsetY || e.clientY - $(e.target).offset().top)  / $(this).height() ];
							$.ajax({
								url: '/couleur_point_photo/'+['index',frac[0],frac[1]].join('/'),
								type: 'post',
								success:function(data) {
									colorpicker.val('#'+data).trigger('change');
									$('#selecteur_couleur').tabs( "option", "active", 0);
								}
							});
						});
				}
			}
		}
	});
	$('#pas_de_photo_tranche').html($('#message-aucune-image-de-tranche .libelle').clone(true));

	// Déplacement des objets
	$('body').on('keydown', function(e) {
		var draggable = $('.ui-draggable:visible');

		if (draggable.length  === 0) {
			return false;
		}

		var dialogue=$('.wizard.preview_etape.modif').d(),
			nom_fonction=dialogue.data('nom_fonction'),
			position = draggable.position(),
			distance = 1, // Distance in pixels the draggable should be moved
			options_maj = [];

		// Reposition if one of the directional keys is pressed
		switch (e.keyCode) {
			case 37: position.left -= distance; break; // Left
			case 38: position.top  -= distance; break; // Up
			case 39: position.left += distance; break; // Right
			case 40: position.top  += distance; break; // Down
			default: return true; // Exit and bubble
		}
		draggable.css(position);

		switch(nom_fonction) {
			case 'Image': options_maj = ['Decalage_x', 'Decalage_y']; break;
			case 'TexteMyFonts': options_maj = ['Pos_x', 'Pos_y']; break;
			case 'Rectangle': options_maj = ['Pos_x_debut', 'Pos_x_fin', 'Pos_y_debut', 'Pos_y_fin']; break;
		}

		tester_options_preview(options_maj);
		tester();

		// Don't scroll page
		e.view.event.preventDefault();
	});

	colorpicker=$('#picker')
		.change(function() {
			affecter_couleur_input(current_color_input, $(this).val().replace(/#/g,''));
			callback_test_picked_color();
		});

	$('#fermer_selecteur_couleur')
		.button()
		.click(function() {
			$('#conteneur_selecteur_couleur').addClass('cache');
			$('#photo_tranche_courante img')
				.removeClass('cross')
				.off('click');
		});
});

var dimensions = {};

var numeros_multiples;
var id_modele;

var wizard_options={};
var id_wizard_courant=null;
var id_wizard_precedent=null;
var num_etape_courante=null;
var nom_photo_principale=null;
var colorpicker=null;

var etape_ajout;
var etape_ajout_pos;

var valeurs_possibles_zoom = [1, 1.5, 2, 4, 6, 8];
zoom=1.5;
var nom_photo_tranches_multiples;

var NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES=10;
var LARGEUR_DIALOG_TRANCHE_FINALE=65;
var LARGEUR_INTER_ETAPES=40;

var COTE_CARRE_DEPLACEMENT=10;

var PADDING_PARAMETRAGE_ETAPE=10;

var TEMPLATES ={numero:/\[Numero\]/,
				'numero[]':/\[Numero\[([0-9]+)\]\]/ig,
				largeur:/(?:([0-9.]+)(\*))?\[Largeur\](?:(\*)([0-9.]+))?/i,
				hauteur:/(?:([0-9.]+)(\*))?\[Hauteur\](?:(\*)([0-9.]+))?/i,
				caracteres_speciaux:/°/i};

var REGEX_FICHIER_PHOTO=/\/([^\/]+\.([^.]+)\.photo_([^.]+?)\.[a-z]+)$/;
var REGEX_FICHIER_PHOTO_MULTIPLE=/.*_([^\.]+)\.jpg$/;

var REGEX_NUMERO=/tranche_([^_]+)_([^_]+)_([^_]+)/;
var REGEX_TO_WIZARD=/to\-(wizard\-[0-9]*)/g;
var REGEX_DO_IN_WIZARD=/do\-in\-wizard\-(.*)/g;
var REGEX_POLICE_MYFONTS=/(?:http:\/\/)?(?:www\.)?(?:new\.)?myfonts.com\/fonts\/(.*)\//g;
var REGEX_OPTION=/option\-([A-Za-z0-9]+)/g;
var REGEX_DECIMAL=/\-?[0-9]+\.?[0-9]*/g;

function can_launch_wizard(id) {
	if (! (id.match(/^wizard\-[a-z0-9-]+$/g))) {
		jqueryui_alert('Identifiant d\'assistant invalide : '+id);
		return false;
	}
	if ($('#'+id).length === 0) {
		jqueryui_alert('Assistant inexistant : '+id);
		return false;
	}
	return true;
}

function launch_wizard(id, p) {
	id_wizard_courant=id;

	p = p || {}; // Paramètres de surcharge
	var buttons={},
		dialogue = $('#'+id),
		first 	 = dialogue.hasClass('first') 	  || (p.first 	  !== undefined	&& p.first),
		modal	 = dialogue.hasClass('modal')	      || (p.modal 	  !== undefined	&& p.modal),
		closeable= dialogue.hasClass('closeable') || (p.closeable !== undefined	&& p.closeable),
		deadend = dialogue.hasClass('deadend'),
		extensible=dialogue.hasClass('extensible');

	$('#'+id+' .controlgroup').controlgroup();
	$('#'+id+' .button').button();
	$('#wizard-1 .controlgroup .disabled').button("option", "disabled", true);

	if (!first) {
		buttons["Precedent"]=function() {
			$( this ).dialog().dialog( "close" );
			launch_wizard(id_wizard_precedent);
		};
	}

	switch(id) {
		case 'wizard-ajout-etape':
			dialogue.find('form input[name="etape"]').val(etape_ajout);
			dialogue.find('form input[name="pos"]').val(etape_ajout_pos);
			buttons={
				OK: function() {
					var formData=$(this).find('form').serializeObject();
					formData.etape = parseInt(formData.etape);

					var panelOuvert = $('#wizard-ajout-etape .accordion').accordion('option','active');
					switch(panelOuvert) {
						case 0: // A partir de zéro
							$.ajax({
								url: '/insert_wizard/'+['index',formData.pos,formData.etape,formData.nom_fonction].join('/'),
								type: 'post',
								dataType:'json',
								success:function(data) {
									$('#wizard-ajout-etape').dialog().dialog( "close" );
									$.each(data.infos_insertion.decalages, function() {
										$('*').getElementsWithData('etape',this.old).data('etape',this.new);
									});
									ajouter_preview_etape(data.infos_insertion.numero_etape, formData.nom_fonction);
									charger_previews(true);
								}
							});
						break;
						case 1: // Clonage
							$.ajax({
								url: '/cloner/'+['index',formData.pos,formData.etape_a_cloner].join('/'),
								type: 'post',
								dataType:'json',
								success:function(data) {
									$('#wizard-ajout-etape').dialog().dialog( "close" );
									$.each(data.infos_insertion.decalages, function() {
										$('*').getElementsWithData('etape',this.old).data('etape',this.new);
									});
									ajouter_preview_etape(data.infos_insertion.numero_etape, data.infos_insertion.nom_fonction);
									charger_previews(true);
								},
								error: function() {
									jqueryui_alert("Une erreur est survenue lors de la création d'étape", "Erreur");
								}
							});

						break;
					}
				},
				Annuler:function() {
					$( this ).dialog().dialog( "close" );
				}
			};
		break;
		case 'wizard-images':
			buttons= {
			  OK: function() {
				var action_suivante=wizard_check($(this).attr('id'));
				if (action_suivante !== null) {
					var type_gallerie='';
					$.each(['photo_principale','autres_photos','photos_texte'], function(i,classe) {
						if ($('#'+id).hasClass(classe)) {
							type_gallerie=classe;
						}
					});
					switch (type_gallerie) {
						case 'photo_principale' :
							var est_photo_renseignee = !$('#pasDePhoto').prop('checked');
							if (est_photo_renseignee) {
								nom_photo_principale=$(this).find('.gallery li img.selected').attr('src')
									.match(REGEX_FICHIER_PHOTO)[1];
									maj_photo_principale();
							}
							wizard_do($(this),action_suivante);
						break;
						case 'autres_photos':
			   				tester_options_preview(['Source']);
							$(this).dialog().dialog( "close" );
						break;
						case 'photos_texte':
							wizard_goto($('#'+id), 'wizard-myfonts', {
								height: $(window).height()-60,
								width: $(window).width()-40,
								first: true
							});
							$(this).dialog().dialog( "close" );

						break;
					}
				}
			},
			Annuler:function() {
				$( this ).dialog().dialog( "close" );
			}
		};
		break;
		case 'wizard-confirmation-validation-modele-contributeurs':
			buttons["OK"]=function() {
				if (wizard_check(id)) {
				   	var form=$('#'+id+' form').serializeObject();
				   	var photographes=typeof(form.photographes) === "string" ? form.photographes : form.photographes.join(',') .replace(/ /g, "+");
				   	var createurs=	 typeof(form.createurs)	=== "string" ? form.createurs 	: form.createurs.join(',') .replace(/ /g, "+");
					var nom_image=$('#wizard-confirmation-validation-modele .image_preview').filter(function () {
						return $(this).data().numero === numero;
					})
						.attr('src').match(/[.0-9]+$/g)[0];
					$.ajax({
						url: '/valider_modele/'+['index',nom_image,createurs,photographes].join('/'),
						type: 'post',
						success:function() {
							jqueryui_alert_from_d($('#wizard-confirmation-validation-modele-ok'), function() {
								location.replace(base_url);
							});
						},
						error:function() {
							jqueryui_alert("Une erreur est survenue pendant la validation de la tranche.<br />"
										  +"Contactez le webmaster",
										  "Erreur");
						}
					});
				}
			};
		break;
		case 'wizard-myfonts':
			buttons={
				OK: function() {
					var police=$(this).find('form').serializeObject().url_police
						.replace(REGEX_POLICE_MYFONTS,'$1')
						.replace(/\//g,'.');
					$(modification_etape).find('[name="option-URL"]').val(police);
					tester_options_preview(['URL']);
					load_myfonts_preview(true,true,true);
					$( this ).dialog().dialog( "close" );
				},
				Annuler:function() {
					$( this ).dialog().dialog( "close" );
				}
			};
		break;
		default:
			if (!deadend) {
				buttons["Suivant"]=function() {
					var action_suivante=wizard_check($(this).attr('id'));
					if (action_suivante !== null) {
						wizard_do($(this),action_suivante);
					}
				};
			}
		break;
	}
	dialogue.dialog({
		width:  extensible ? 'auto' : (p.width || 475),
		height: p.height|| 'auto',
		position: 'top',
		modal: modal,
		autoResize: true,
		resizable: false,
		buttons: buttons,
		draggable: dialogue.hasClass('draggable'),
		open:function() {
			var dialog=$(this).d();
			dialog.attr({id: 'dialog-' + $(this).attr('id')});

			$(this).css({maxHeight:(
										$('#body').height()
									   -dialog.find('.ui-dialog-titlebar').height()
									   -dialog.find('.ui-dialog-buttonpane').height()*2
									   -dialog.css('top')
									 )+'px'});

			if (!closeable) {
				dialog.find(".ui-dialog-titlebar-close").hide();
			}

			wizard_init($(this).attr('id'));
		},
		close: function() {
			if (closeable) {
				var form = $('#'+id+' form');
				var hasOnClose = form.serializeObject().onClose;
				if (hasOnClose) {
					wizard_goto($('#id'), form.serializeObject().onClose.replace(REGEX_TO_WIZARD,'$1'));
				}
			}
		}
	});
}

function wizard_goto(wizard_courant, id_wizard_suivant, p) {
	if (can_launch_wizard(id_wizard_suivant)) {
		var id_wizard_courant = wizard_courant.attr('id');
		wizard_options[id_wizard_courant]=wizard_courant.find('form').serializeObject();
		id_wizard_precedent=id_wizard_courant;
		wizard_courant.dialog().dialog( "close" );
		launch_wizard(id_wizard_suivant, p);
	}
}

function wizard_do(wizard_courant, action) {
	if (action.indexOf("goto_") !== -1) {
		wizard_goto(wizard_courant,action.substring("goto_".length));
	}
	else {
		switch(wizard_courant.attr('id')) {
			case 'wizard-1':
				switch(action) {
					case 'conception':
						id_modele=wizard_courant.find('form').serializeObject().choix_tranche_en_cours.split(/_/g)[1];

						//
						// else { // Nouvelle tranche => paramétrage des dimensions, etc.
						// 	if (get_option_wizard('wizard-creer-collection','choix_tranche') !== undefined
						// 		|| get_option_wizard('wizard-creer-hors-collection','wizard_pays') !== undefined) {
						// 		var tranche_collection = get_option_wizard('wizard-creer-collection','choix_tranche');
						// 		if (tranche_collection !== undefined) {
						// 			var tranche=tranche_collection.match(REGEX_NUMERO);
						// 			pays=tranche[1];
						// 			magazine=tranche[2];
						// 			numero=tranche[3];
						// 		}
						//
						// 		if (numero === undefined) {
						// 			// Ajout du modèle de tranche et de la fonction Dimensions avec les paramétres par défaut
						// 			var dimension_x = get_option_wizard('wizard-dimensions','Dimension_x');
						// 			var dimension_y = get_option_wizard('wizard-dimensions','Dimension_y');
						// 			creer_modele_tranche(pays, magazine, numero, true, function () {
						// 				dimensions = {x: parseInt(dimension_x), y: parseInt(dimension_y)};
						// 				charger_etapes_tranche_en_cours();
						// 			});
						// 			return;
						//
						// 		}
						// 	}
						// }

						charger_etapes_tranche_en_cours();
					break;
				}
			break;
			case 'wizard-dimensions':
				switch(action) {
					case 'enregistrer':
						var publicationcode=get_option_wizard('wizard-creer-hors-collection','wizard_magazine');
						pays=publicationcode.split('/')[0];
						magazine=publicationcode.split('/')[1];
						numero=get_option_wizard('wizard-creer-hors-collection','wizard_numero');

						var form_data = wizard_courant.find('form').serializeObject();
						dimensions = {x: parseInt(form_data.Dimension_x), y: parseInt(form_data.Dimension_y)};

						creer_modele_tranche(pays, magazine, numero, true, function () {
							wizard_goto(wizard_courant, 'wizard-images');
						});

					break;
				}

			break;
			case 'wizard-resize':
				switch(action) {
					case 'enregistrer':
						if (wizard_check('wizard-resize') !== undefined) {
							var image = wizard_courant.find('.jrac_viewport img');
							var source = 'photos';
							var destination = wizard_courant.find('[name="destination"]').val();
							var decoupage_nom = image.attr('src').match(REGEX_FICHIER_PHOTO);
							if (!decoupage_nom) {
								jqueryui_alert("Le nom de l'image est invalide : " + image.attr('src'), "Nom invalide");
								return;
							}

							var numero_image = decoupage_nom[2];
							var nom = decoupage_nom[3];
							var jrac_crop = $('.jrac_crop');
							var jrac_crop_position = jrac_crop.position();
							var x1 = jrac_crop_position.left,
								x2 = jrac_crop_position.left + jrac_crop.width(),
								y1 = jrac_crop_position.top,
								y2 = jrac_crop_position.top  + jrac_crop.height();
							rogner_image(image, nom, source, destination, pays, magazine, numero, x1, x2, y1, y2, numero_image);
						}
					wizard_goto(wizard_courant,'wizard-images');
					break;
				}
			break;
			case 'wizard-selectionner-numero-photo-multiple':
				switch(action) {
					case 'affectation-numero-tranche':
						var zone = wizard_courant.data('zone');
						zone.find('.renseigne').removeClass('cache');
						zone.find('.non_renseigne').addClass('cache');

						var magazine_complet = wizard_courant.find('form [name="wizard_magazine"] option:selected').text();
						var data = $.extend({}, wizard_courant.find('form').serializeObject(), {wizard_magazine_complet: magazine_complet});

						zone.find('.intitule_numero .renseigne').remplirIntituleNumero(data);
					break;
				}
			break;
		}
		wizard_courant.dialog("close");
	}
}

function wizard_check(wizard_id) {
	var erreur=null;
	var wizard = $('#' + wizard_id);
	var form = wizard.find('form');
	var choix = form.find('[name="choix"]');
	var valeur_choix = form.serializeObject().choix;
	if (choix.length !== 0 && valeur_choix === undefined) {
		erreur='Le formulaire n\'est pas correctement rempli';
	}
	else {
		var is_to_wizard = valeur_choix !== undefined && valeur_choix.match(REGEX_TO_WIZARD);
		var is_do_in_wizard = valeur_choix !== undefined && valeur_choix.match(REGEX_DO_IN_WIZARD);
		if (is_to_wizard || is_do_in_wizard) {
			if (form.find('[name="wizard_numero"]').length > 0) {
				if (chargement_listes) {
					erreur='Veuillez attendre que la liste des numéros soit chargée';
				}
				else {
					if (valeur_choix !== 'to-wizard-numero-inconnu') {
						switch(wizard_id) {
							case 'wizard-creer-hors-collection':
								if (! verifier_peut_creer_numero_selectionne(wizard)) {
									erreur='La tranche de ce numéro est déjà disponible ou en cours de conception';
								}
							break;
							case 'wizard-modifier':
								if (! verifier_peut_modifier_numero_selectionne(wizard)) {
									erreur='La tranche de ce numéro est déjà en cours de conception.';
								}
						}
					}
				}
			}
			if (form.find('[name="Dimension_x"]').length > 0) {
				$.each($(['Dimension_x','Dimension_y']),function(i,nom_champ) {
					var valeur= wizard.find('[name="'+nom_champ+'"]').val();
					var bornes_valeur=nom_champ === 'Dimension_x' ? [3, 60] : [100, 450];
					if ( valeur === ''
						|| valeur.search(/^[0-9]+$/) !== 0) {
						erreur="Le champ "+nom_champ+" est vide ou n'est pas un nombre";
					}
					valeur=parseInt(valeur);
					if (valeur < bornes_valeur[0] || valeur > bornes_valeur[1]) {
						erreur="Le champ "+nom_champ+" doit être compris entre "+bornes_valeur[0]+" et "+bornes_valeur[1];
					}

				});
			}

			switch(wizard_id) {
				case 'wizard-1':
					if (valeur_choix === 'do-in-wizard-conception'
					 && form.serializeObject().choix_tranche_en_cours === 0) {
						erreur='Si vous souhaitez poursuivre une création de tranche, cliquez dessus pour la sélectionner.<br />'
							  +'Sinon, cliquez sur "Créer une tranche de magazine" ou "Modifier une tranche de magazine".';
					}
				break;
				case 'wizard-creer-collection':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des numéros soit chargée';
					else if (form.serializeObject().choix_tranche === 0) {
						erreur='Veuillez sélectionner un numéro.';
					}
				break;
				case 'wizard-selectionner-numero-photo-multiple':
					if (! verifier_peut_creer_numero_selectionne(wizard)) {
						erreur='La tranche de ce numéro est déjà disponible ou en cours de conception';
					}
				break;
				case 'wizard-decouper-photo':
					if ($('.rectangle_selection_tranche:not(.template)').filter(function() {
						return $(this).find('.intitule_magazine').length === 0;
					}).length > 0) {
						erreur='Vous n\'avez pas spécifié les numéros de toutes les zones de la photo.';
					}
				break;
				case 'wizard-modifier':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des numéros soit chargée';
					else if (valeur_choix === 'to-wizard-clonage-silencieux'
						  && !wizard.find('[name="wizard_numero"]').find('option:selected').is('.tranche_prete, .cree_par_moi, .en_cours')) {
						erreur='La tranche de ce numéro n\'existe pas.<br />'
							  +'Sélectionnez "Créer une tranche de magazine" dans l\'écran précédent pour la créer '
							  +'ou sélectionnez un autre numéro.';
					}
				break;

				case 'wizard-proposition-clonage':
					if (valeur_choix === 'to-wizard-clonage'
					 && form.find('[name="tranche_similaire"]').filter(':checked').length === 0) {
						erreur='Si vous avez trouvé une tranche similaire, cliquez dessus pour la sélectionner.<br />'
							  +'Sinon, cliquez sur "Créer une tranche originale".';
					}
				break;

				case 'wizard-images':
					if (form.find('ul.gallery li img.selected').length === 0
					 && !$('#pasDePhoto').prop('checked')) {
						erreur='Veuillez sélectionner une photo de tranche.';
					}
				break;

				case 'wizard-resize':
					if (wizard.find('.error:not(.cache)').length > 0) {
						erreur='Veuillez corriger les erreurs avant de continuer.';
					}
				break;
				case 'wizard-confirmation-validation-modele-contributeurs':
					if (! form.serializeObject().photographes
					 || ! form.serializeObject().createurs) {
						erreur='Au moins un photographe et un designer doivent être spécifiés.';
					}
				break;
			}
		}
	}
	if (erreur !== null) {
		jqueryui_alert(erreur);
	}
	else {
		if (valeur_choix === undefined)
			return true;
		if (is_to_wizard)
			return 'goto_'+valeur_choix.replace(REGEX_TO_WIZARD,'$1');
		if (is_do_in_wizard)
			return valeur_choix.replace(REGEX_DO_IN_WIZARD,'$1');
	}
	return null;
}

var chargement_listes=false;
var modification_etape=null;

function wizard_init(wizard_id) {

	var wizard = $('#'+wizard_id);

	// Transfert vers un autre assistant
	wizard.find('button[value^="to-wizard-"]').click(function() {
		wizard_do($('#'+id_wizard_courant),'goto_'+$(this).val().replace(REGEX_TO_WIZARD,'$1'));
		event.preventDefault();
	});

	// Action en restant dans l'assistant
	wizard.find('button[value^="do-in-wizard-"]').click(function() {
		var action = $(this).val().replace(REGEX_DO_IN_WIZARD,'$1');
		wizard_do($('#'+id_wizard_courant),action);
		event.preventDefault();
	});

	// Actions à l'initialisation de l'assistant
	switch(wizard_id) {
		case 'wizard-1':
			$.ajax({
				url: '/tranchesencours/'+['load'].join('/'),
				dataType:'json',
				type: 'get',
				success:function(data) {
					var tranches_editeur = data.tranches_en_cours;
					var tranches_en_attente = data.tranches_en_attente;
					var tranches_en_attente_d_edition = data.tranches_en_attente_d_edition;
					wizard.afficher_liste_magazines(wizard.find('#tranches_en_attente'), 'groupe_tranches_en_attente', tranches_en_attente, false);
					wizard.afficher_liste_magazines(wizard.find('#tranches_en_cours'), 'groupe_tranches_en_cours', tranches_editeur, true);
					wizard.afficher_liste_magazines(wizard.find('#tranches_en_attente_pour_edition'), 'groupe_tranches_en_cours', tranches_en_attente_d_edition, true);
				}
			});
			break;

		case 'wizard-envoyer-photo':
			wizard.parent().find('button').filter(function() { return $(this).text() === 'Suivant'; }).button('option','disabled', true);
		break;

		case 'wizard-decouper-photo':
			$('#image_tranche_multiples').attr({src: edges_url+'/tranches_multiples/'+nom_photo_tranches_multiples});
			$('#ajouter_zone_photo_multiple').click(function() {
				var nouvelle_zone = wizard.find('.rectangle_selection_tranche.template').clone(true).removeClass('template');
				nouvelle_zone.find('.suppression').click(function() {
					$(this).closest('.rectangle_selection_tranche').remove();
				});
				nouvelle_zone.find('.zone_intitule_numero').click(function() {
					$('#wizard-selectionner-numero-photo-multiple').data({zone: $(this).closest('.rectangle_selection_tranche')});
					launch_wizard('wizard-selectionner-numero-photo-multiple');
				});
				nouvelle_zone
					.css({zIndex: 100+$('#zone_selection_tranches_multiples .rectangle_selection_tranche').length})
					.draggable({
						containment: '#zone_selection_tranches_multiples'
					})
					.resizable({
						containment: '#zone_selection_tranches_multiples'
					})
					.mouseover(function() {
						$(this).find('.edition_numero_tranche').removeClass('cache');
					})
					.mouseout(function() {
						$(this).find('.edition_numero_tranche').addClass('cache');
					});
				$('#zone_selection_tranches_multiples').prepend(nouvelle_zone);
			});
		break;

		case 'wizard-selectionner-numero-photo-multiple':
			wizard_charger_liste_pays();
		break;

		case 'wizard-confirmation-photo-multiple':
			var image_tranches_multiples = $('#image_tranche_multiples');
			$('#wizard-decouper-photo').parent().addClass('invisible');
			var id_fichier_image_multiple = image_tranches_multiples.attr('src').replace(REGEX_FICHIER_PHOTO_MULTIPLE,'$1');
			var pos_image = image_tranches_multiples.position();
			var tranches_a_creer = $.map($('.rectangle_selection_tranche:not(.template)'), function(element) {
				element = $(element);
				var data=element.find('.intitule_magazine').data();
				return {
					element: element,
					dimensions: {x: data.Dimension_x, y: data.Dimension_y},
					pays: data.wizard_magazine.split('/')[0],
					magazine: data.wizard_magazine.split('/')[1],
					numero: data.wizard_numero
				};
			});

			creer_prochain_modele_tranche(tranches_a_creer, 0, image_tranches_multiples, id_fichier_image_multiple, pos_image);

		break;

		case 'wizard-creer-collection':
			chargement_listes=true;
			$.ajax({
				url: '/numerosdispos/'+['index','null','null','true'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					if (typeof(data.erreur) !=='undefined')
						jqueryui_alert(data);
					else {
						wizard.afficher_liste_magazines(wizard.find('#tranches_non_pretes'), data.tranches_non_pretes, true);
					}
					chargement_listes=false;
				},
				error:function(data) {
					jqueryui_alert('Erreur : '+data);
				}
			});
		break;

		case 'wizard-creer-hors-collection': case 'wizard-modifier':
			if (get_option_wizard('wizard-creer-hors-collection', 'wizard_pays')
			 || get_option_wizard('wizard-creer-collection', 'wizard_pays') !== undefined)
				break;

			wizard_charger_liste_pays();
		break;

		case 'wizard-proposition-clonage':
			if (get_option_wizard('wizard-proposition-clonage', 'tranche_similaire') !== undefined)
				break;
			wizard.find('.chargement').removeClass('cache');
			wizard.find('.tranches_affichees_magazine, .controlgroup').addClass('cache');
			if (numero === undefined) {
				if (get_option_wizard('wizard-creer-collection','choix_tranche')!== undefined) {
					var tranche=get_option_wizard('wizard-creer-collection','choix_tranche').split(/_/g);
					pays=	 tranche[1];
					magazine=tranche[2];
					numero=	 tranche[3];
				}
				else {
					var publicationcode=get_option_wizard('wizard-creer-hors-collection', 'wizard_magazine');
					pays = publicationcode.split('/')[0];
					magazine = publicationcode.split('/')[1];
					numeros_multiples=get_option_wizard('wizard-creer-hors-collection', 'wizard_numero');
					if (typeof numeros_multiples !== 'object') {
						numeros_multiples = [numeros_multiples];
					}
					numero=numeros_multiples[0];
				}
			}
			if (!(numeros_multiples && numeros_multiples.length)) {
				numeros_multiples = [numero];
			}

			selecteur_cellules_preview='#'+wizard_id+' .tranches_affichees_magazine td:not(.libelle_numero)';

			charger_tranches_proches(numeros_multiples, true, NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, afficher_tranches_proches);
		break;

		case 'wizard-clonage':
			wizard.parent().find('.ui-dialog-buttonpane button').button("option", "disabled", true);
			var numero_a_cloner = get_option_wizard('wizard-proposition-clonage', 'tranche_similaire');
			wizard.find('.numero_similaire').html(numero_a_cloner);
			cloner_numero(numero_a_cloner, numeros_multiples.slice(0));
		break;

		case 'wizard-clonage-silencieux':
			wizard.parent().find('.ui-dialog-buttonpane button').button("option", "disabled", true);
			pays=get_option_wizard('wizard-modifier', 'wizard_pays');
			magazine=get_option_wizard('wizard-modifier', 'wizard_magazine');
			numero=get_option_wizard('wizard-modifier', 'wizard_numero');

			$.ajax({
				url: '/etendre/'+['index',pays,magazine,numero,numero].join('/'),
				type: 'post',
				success:function(data) {
					wizard.parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
					if (typeof(data.erreur) !=='undefined')
						jqueryui_alert(data);
					else {
						wizard.find('.loading').addClass('cache');
						wizard.find('.done').removeClass('cache');
					}
				},
				error:function(data) {
					wizard.parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
					jqueryui_alert('Erreur : '+data);
				}
			});
		break;

		case 'wizard-images':
			$('#pasDePhoto').prop({checked: false});
			wizard.find('.accordion').accordion({
				activate: function (event, ui) {
					var toWizardResize = $('#to-wizard-resize');

					toWizardResize.addClass('cache');
					switch ($(ui.newHeader).attr('id')) {
						case 'gallery':
							var type_gallerie = wizard.hasClass('photo_principale') ? 'Photos' : 'Source';
							lister_images_gallerie(type_gallerie);
							break;
						case 'section_photo':
							toWizardResize.removeClass('cache');
							var source_photo_tranche = $('#photo_tranche_courante img').attr('src');
							if (source_photo_tranche) {
								var nom_fichier_photo = source_photo_tranche.match(/\/([^\/]+$)/)[1];
								afficher_galerie('Photos', [nom_fichier_photo], wizard.find('.selectionner_photo_tranche'));
							}
							break;
					}
				}
			});
		break;

		case 'wizard-dimensions':
			if (dimensions.x) {
				charger_etapes_tranche_en_cours();
			}
			else {
				var dimensions_connues= get_option_wizard('wizard-1','choix') === 'do-in-wizard-conception';

				if (dimensions_connues) {
					creer_modele_tranche(pays, magazine, numero, true, function () {
						charger_etapes_tranche_en_cours();
					}); // Création du modéle sans les dimensions (qui seront copiées du modéle non affecté)
				}
			}
		break;

		case 'wizard-ajout-etape':
			var etape_existe = $('.wizard.preview_etape:not(.template):not(.final)').length > 0;
			var accordeon = wizard.find('.accordion');
			accordeon.accordion({
				active: etape_existe ? 1 : 0
			});
			wizard.find('.aucune_etape').toggle(!etape_existe);
			wizard.find('.etape_existante').toggle(etape_existe);

			$.ajax({
				url: '/listerg/'+['index','Fonctions'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					var select=$('<select>',{name:'nom_fonction'});
					$.each(data, function(i, nom_fonction) {
						select.append($('<option>',{value:i}).html(nom_fonction));
					});
					$('#liste_fonctions').html(select);
				}
			});
			$('#selectionner_etape_base').click(function() {
				wizard.dialog().dialog("close");
				$('.dialog-preview-etape')
					.addClass('cloneable')
					.click(function() {
						$('#section_etape_a_cloner')
							.removeClass('cache');
						$('#etape_a_cloner')
							.val($(this).data('etape'));
						wizard.dialog().dialog("open");
						$('.dialog-preview-etape')
							.removeClass('cloneable')
							.off('click');
					});
			});
		break;
		case 'wizard-resize':
			wizard.find('img')
			  .jrac({image_height:480})
				.bind('jrac_events', surveiller_selection_jrac);
		break;

		case 'wizard-confirmation-validation-modele':
			selecteur_cellules_preview='#'+wizard_id+' .tranches_affichees_magazine td:not(.libelle_numero)';
			charger_tranches_proches([numero], false, NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, afficher_tranches_proches);
		break;

		case 'wizard-confirmation-validation-modele-contributeurs':
			$.ajax({
				url: '/listerg/'+['index','Utilisateurs',[].join('_')].join('/'),
				type: 'post',
				dataType:'json',
				success:function(data) {
				   var utilisateur_courant=$('#utilisateur').html();

			 	   $.each(wizard.find('span'),function(i,span) {
			 		   var type_contribution=$(span).attr('id');
			 		   $.each(data, function(username, user) {
			 			   var id = $(span).attr('id')+'_'+username;
			 			   var option = $('<input>',{
			 				   id:   id,
			 				   name: $(span).attr('id'),
			 				   type: 'checkbox'
			 			   }).val(username);
			 			   var coche=(type_contribution === 'photographes' &&  user.indexOf('photographe') !== -1)
								  || (type_contribution === 'createurs'	&& (user.indexOf('createur') !== -1
										  								   || utilisateur_courant===username));
			 			   option.prop({checked: coche, readOnly: coche});
			 			   $(span).append(
			 					$('<div>')
			 					   	.css({fontWeight: coche?'bold':'normal'})
			 					   	.append(option)
			 					   	.append($('<label>',{for: id}).text(username)));
			 		   });
			 	   });
				}
			});
		break;
		case 'wizard-myfonts':
			var image_selectionnee = window.location.host === 'localhost'
				? prompt('URL image ?')
				: $('#wizard-images input[name="selected"]').val();
			wizard.find('iframe').attr({src:'http://www.myfonts.com/WhatTheFont/upload?url='+image_selectionnee});
			$('.toggle_exemple').click(function() {
				$('.exemple_cache, .exemple_affiche').toggleClass('cache');
			});
		break;
	}
}

$.fn.afficher_liste_magazines = function(element_wrapper, classe_template, data, peut_editer) {
	var wizard = this;
	var explication = wizard.find('.explication');
	var chargement = wizard.find('.chargement');
	var tranches = traiter_tranches(data);

	explication.addClass('cache');
	chargement.addClass('cache');

	if (tranches.length > 0) {
		element_wrapper.removeClass('cache');
		explication.removeClass('cache');
		wizard.find('#do-in-wizard-conception').button('option','disabled',false);

		$.each(tranches, function(i, tranche_en_cours) {
			var bouton_tranche_en_cours=wizard.find('.template.' + classe_template).clone(true).removeClass('template');
			var id_tranche=['tranche', tranche_en_cours.id].join('_');
			bouton_tranche_en_cours.find('input')
				.attr({id: id_tranche})
				.val(id_tranche);
			bouton_tranche_en_cours.find('label.libelle_tranche')
				.afficher_libelle_numero(id_tranche, tranche_en_cours, peut_editer)
				.click(function() {
					wizard.find('[name="est_nouvelle_conception_tranche"]').val($(this).closest('[name="tranches_non_affectees"]').length > 0);
					wizard.find('#do-in-wizard-conception').click();
				});
			if (element_wrapper.find('#'+id_tranche).length === 0) {
				element_wrapper.append(bouton_tranche_en_cours);
			}
		});

		element_wrapper.removeClass('cache');

		if (peut_editer) {
			element_wrapper.controlgroup();
		}

		wizard.find('#to-wizard-creer, #to-wizard-modifier').click(function() {
			element_wrapper.find('.ui-state-active').removeClass('ui-state-active');
		});
	}
	else {
		element_wrapper.remove();
	}

	return this;
};

function limiter_tranches_pretes_parmi_tranches_affichees(tranches_affichees, tranches_pretes, limite, depuis_gauche) {
	var num_tranche = 0;

	if (depuis_gauche) { // On limite par la gauche du tableau
		tranches_affichees.reverse();
	}

	tranches_affichees = tranches_affichees.filter(function(tranche) {
		if (tranches_pretes.indexOf(tranche) !== -1) {
			return num_tranche++ < limite;
		}
		return true;
	});

	if (depuis_gauche) {
		tranches_affichees.reverse();
	}

	return tranches_affichees;
}

function afficher_tranches(wizard_courant, tranches_affichees, numeros, tranches_pretes, tranches_affichees_clonables) {
	var est_contexte_clonage = tranches_affichees_clonables !== undefined;

	var tableau_tranches_affichees = $('<table>');
	var ligne_numeros_tranches_affichees1 = $('<tr>');
	var ligne_tranches_affichees = $('<tr>');
	var ligne_tranche_selectionnee = $('<tr>');
	var ligne_qualite_tranche = $('<tr>');
	var ligne_numeros_tranches_affichees2 = $('<tr>');
	tableau_tranches_affichees
		.append(ligne_numeros_tranches_affichees1)
		.append(ligne_tranches_affichees)
		.append(ligne_tranche_selectionnee)
		.append(ligne_qualite_tranche)
		.append(ligne_numeros_tranches_affichees2);

	var element_tranches_affichees = wizard_courant.find('.tranches_affichees_magazine');

	if (element_tranches_affichees.find('.libelle_numero').length && numeros.length === 1) { // Tranches déjà affichées précédemment. On ne charge que la tranche courante
		reload_numero(numeros[0], false, true);
	}
	else {
		element_tranches_affichees.html($('<div>').addClass('controlgroup').html(tableau_tranches_affichees));
		$.each(tranches_affichees, function(i, tranche_affichee) {
			var numero_tranche_affichee = tranche_affichee;
			if (est_contexte_clonage && !tranches_affichees_clonables[numero_tranche_affichee]) {
				return true;
			}

			var est_tranche_courante = numeros.indexOf(numero_tranche_affichee) !== -1;
			var est_tranche_publiee = tranches_pretes[numero_tranche_affichee] !== 'en_cours';

			ligne_tranches_affichees.append($('<td>').data('numero', numero_tranche_affichee)); // On insére ce <td> avant les autres pour qu'il soit trouvé par le chargeur d'image

			var td_numero = $('<td>').addClass('libelle_numero').data('numero', numero_tranche_affichee)
				.html(
					$(est_tranche_courante ? '<b>' : '<span>').html('n°' + numero_tranche_affichee)
				);

			if (est_contexte_clonage) {
				var td_qualite = $('<td>').addClass('qualite_tranche');
				var td_radio = $('<td>');

				if (!est_tranche_courante) {
					td_qualite.html(
						$('.qualite_tranche.template')
							.clone(true)
							.removeClass('template')
							.find('.qualite_tranche_' + tranches_affichees_clonables[numero_tranche_affichee].qualite)
							.removeClass('hidden')
					);
					td_radio.html(
						$('<input>', {
							type: 'radio',
							name: 'tranche_similaire',
							readonly: 'readonly'
						}).val(numero_tranche_affichee)
					);
				}
				ligne_tranche_selectionnee.append(td_radio);
				ligne_qualite_tranche.append(td_qualite);
			}
			if (!(est_contexte_clonage && est_tranche_courante)) {
				reload_numero(numero_tranche_affichee, est_tranche_publiee && !est_tranche_courante, !est_tranche_courante);
			}

			ligne_numeros_tranches_affichees1.append(td_numero);
			ligne_numeros_tranches_affichees2.append(td_numero.clone(true));
		});
	}

	if (est_contexte_clonage) {
		wizard_courant.find('.image_preview').click(function () {
			wizard_courant.find('.image_preview').removeClass('selected');
			$(this).addClass('selected');
			wizard_courant.find('input[type="radio"][value="' + $(this).data('numero') + '"]').prop('checked', true);
		});
	}
	else { // Contexte validation de tranche
		var toggle_cacher_libelles = wizard_courant.find('[name="cacher_libelles_magazines"]');
		if (!$._data(toggle_cacher_libelles[0], "events")) {
			toggle_cacher_libelles.click(function () {
				wizard_courant.find('.libelle_numero').toggle();
			});
		}
	}
	wizard_courant.find('.chargement').addClass('cache');
	wizard_courant.find('.tranches_affichees_magazine, .controlgroup').removeClass('cache');

	selecteur_cellules_preview = '.wizard.preview_etape div.image_etape';
}

function charger_tranches_proches(numeros, est_contexte_clonage, max_tranches_proches, callback) {
	charger_liste_numeros(pays,magazine, function(data) {
		var numeros_existants=data.numeros_dispos;

		var tranches_pretes=[];
		var tranches_affichees = [];
		var numero_courant_trouve=false;
		$.each(numeros_existants, function(numero_existant) {
			if (numero_existant !== 'Aucun') {
				var est_tranche_prete=data.tranches_pretes[numero_existant] !== undefined;
				if (numeros.indexOf(numero_existant) !== -1) {
					tranches_affichees = limiter_tranches_pretes_parmi_tranches_affichees(tranches_affichees, tranches_pretes, max_tranches_proches/2, true);
					tranches_affichees.push(numero_existant);
					numero_courant_trouve=true;
				}
				else if (est_tranche_prete) {
					// On arrête aprés "max_tranches_proches" tranches similaires + le nouveau numéro
					if (!numero_courant_trouve || tranches_affichees.length < max_tranches_proches + 1) {
						tranches_pretes.push(numero_existant);
						tranches_affichees.push(numero_existant);
					}
				}
			}
		});

		if (!numero_courant_trouve) {
			// Entrer ici signifie qu'il n'y a pas de tranches prêtes après le numéro sélectionné
			tranches_affichees = limiter_tranches_pretes_parmi_tranches_affichees(tranches_affichees, tranches_pretes, max_tranches_proches/2, false);
		}

		if (callback) {
			callback(tranches_affichees, data.tranches_pretes, numeros, est_contexte_clonage);
		}
	});
}

function afficher_tranches_proches(tranches_affichees, tranches_pretes, numeros, est_contexte_clonage) {
	var wizard_courant = $('#'+id_wizard_courant);
	if (est_contexte_clonage) { // Filtrage des tranches qui sont prêtes mais sans modèle
		// Pas de proposition de tranche
		if (tranches_pretes.length === 0) {
			wizard_do(wizard_courant,'goto_wizard-dimensions');
		}
		$.ajax({
			url: '/cloner/'+['est_clonable',pays,magazine,tranches_affichees.join(',')].join('/'),
			type: 'post',
			dataType:'json',
			success: function (tranches_clonables) {
				var tranches_affichees_clonables = [];
				$.each(tranches_affichees, function(i, tranche_affichee) {
					if (tranches_clonables[tranche_affichee] !== undefined || numeros.indexOf(tranche_affichee) !== -1) {
						tranches_affichees_clonables[tranche_affichee] = tranches_clonables[tranche_affichee];
					}
				});
				afficher_tranches(wizard_courant, tranches_affichees, numeros, tranches_pretes, tranches_affichees_clonables);
			}
		});
	}
	else {
		afficher_tranches(wizard_courant, tranches_affichees, numeros, tranches_pretes);
	}
}

function cloner_numero(numero_a_cloner, nouveaux_numeros) {
	var wizard = $('#wizard-clonage');
	var nouveau_numero = nouveaux_numeros.shift();
	wizard.find('.nouveau_numero').html(nouveau_numero);
	$.ajax({
		url: '/etendre/' + ['index', pays, magazine, numero_a_cloner, nouveau_numero].join('/'),
		dataType:'json',
		type: 'post',
		success: function (data) {
			id_modele = data.resultat_clonage.id_modele;
			if (typeof(data.erreur) !== 'undefined')
				jqueryui_alert(data);
			else {
				if (nouveaux_numeros.length) {
					cloner_numero(numero_a_cloner, nouveaux_numeros);
				}
				else {
					wizard.parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
					wizard.find('.loading').addClass('cache');
					wizard.find('.done').removeClass('cache');
					wizard.find('.clonage_partiel').toggleClass('cache', !Object.keys(data.resultat_clonage.etapes_non_clonees).length);
				}
			}
		},
		error: function (data) {
			jqueryui_alert('Erreur : ' + data);
		}
	});
}

function traiter_tranches(tranches) {
	var tranches_traitees=[];
	$.each(tranches, function(i, tranche_en_cours) {
		tranche_en_cours.str=tranche_en_cours.pays+'_'+tranche_en_cours.magazine+'_'+tranche_en_cours.numero;
		tranche_en_cours.str_userfriendly=tranche_en_cours.magazine_complet+' n°'+tranche_en_cours.numero;
		tranches_traitees.push(tranche_en_cours);
	});
	return tranches_traitees;
}

function charger_etapes_tranche_en_cours() {
	$('#'+id_wizard_courant).dialog().dialog("close");
	$('.wizard.preview_etape:not(.template)').remove();

	$.ajax({
		url: '/tranchesencours/' + ['load', id_modele].join('/'),
		type: 'post',
		dataType: 'json',
		success: function (data) {
			var tranche = traiter_tranches(data.tranches_en_cours)[0];
			pays = tranche.pays;
			magazine = tranche.magazine;
			numero = tranche.numero;
			id_modele = tranche.id;

			$('#nom_complet_tranche_en_cours')
				.html($('<img>', {src: 'images/flags/' + pays + '.png'}))
				.append(' ' + tranche.str_userfriendly);

			$('#action_bar').removeClass('cache');

			$.ajax({ // Numéros d'étapes
				url: '/parametrageg_wizard/' + ['index'].join('/'),
				type: 'post',
				dataType: 'json',
				success: function (etapes) {
					etapes_valides = etapes.filter(function (etape) {
						return parseInt(etape.Ordre) > -1;
					});

					charger_couleurs_frequentes();

					$.ajax({ // Détails des étapes
						url: '/parametrageg_wizard/' + ['index', -1, 'null'].join('/'),
						type: 'post',
						dataType: 'json',
						success: function (data) {
							$('#zoom').removeClass('cache');
							$('#zoom_slider').slider({
								change: function (event, ui) {
									zoom = valeurs_possibles_zoom[ui.value];
									$('#zoom_value').html(zoom);
									$('#zoom_slider .ui-slider-handle').blur();
									update_previews_dimensions();
								}
							});

							var texte = "";
							$.each(data, function(option_nom, option) {
								$.each(option, function(intervalle, valeur) {
									if (intervalle !== 'type' && intervalle !== 'valeur_defaut' && intervalle !== 'description') {
										if (intervalle === "valeur" && typeof(valeur) === 'undefined')
											texte = option.valeur_defaut;
										else
											texte = valeur;
									}
								});
								switch (option_nom) {
									case 'Dimension_x':
										$('#Dimension_x').val(texte);
										dimensions.x = parseInt(texte);
										break;
									case 'Dimension_y':
										$('#Dimension_y').val(texte);
										dimensions.y = parseInt(texte);
										break;
								}
							});
							$('#modifier_dimensions')
								.removeClass('cache')
								.button()
								.click(function (event) {
									event.preventDefault();
									verifier_changements_etapes_sauves($('.modif').d(), 'wizard-confirmation-rechargement', function () {
										var form_options = $(event.currentTarget).d().find('[name="form_options"]');
										var parametrage = form_options.serialize();

										$.ajax({
											url: '/update_wizard/' + ['index', -1, parametrage].join('/'),
											type: 'post',
											success: function () {
												update_previews_dimensions();
											}
										});
									});
								});

							var wizard_etape_finale = $('.wizard.preview_etape.template').clone(true);
							wizard_etape_finale
								.append(
									$('<span>', {id: 'photo_tranche_precedente'}).addClass('photo_tranche'),
									$('<div>').data('etape', 'final').addClass('image_etape finale'),
									$('<span>', {id: 'photo_tranche_suivante'}).addClass('photo_tranche'),
									$('<span>', {id: 'photo_tranche_courante'}).addClass('photo_tranche')
								);


							wizard_etape_finale.dialog({
								appendTo: '#preview',
								resizable: false,
								draggable: false,
								minWidth: 0,
								height: 'auto',
								position: ['right', 'top'],
								closeOnEscape: false,
								modal: false,
								open: function () {
									$(this).removeClass('template').addClass('final');
									$(this).data('etape', 'finale');
									$(this).d().addClass('dialog-preview-etape finale')
										.data('etape', 'finale');
									$(this).d().find(".ui-dialog-titlebar-close").hide();
									$(this).d().find('.ui-dialog-titlebar').css({
										padding: '.3em .6em;',
										textAlign: 'center'
									})
										.html('Preview');

									afficher_photo_tranche(function() {
										charger_tranches_proches([numero], false, 2, function (tranches_proches, tranches_pretes) {
											var numero_courant_trouve = false;
											selecteur_cellules_preview = '#photo_tranche_precedente, #photo_tranche_suivante';
											chargements = [];
											$.each(tranches_proches, function(i, numero_proche) {
												if (numero_proche === numero) {
													numero_courant_trouve = true;
												}
												else {
													var id_tranche = numero_courant_trouve ? 'photo_tranche_suivante' : 'photo_tranche_precedente';
													$('#' + id_tranche).data('numero', numero_proche);
													chargements.push(numero_proche);
												}
											});

											if (chargements.length) {
												chargement_courant = 0;
												charger_tranche_proche_suivante(tranches_pretes, function() {
													ajouter_et_charger_previews();
												});
											}
											else {
												ajouter_et_charger_previews();
											}
										});
									});
								}
							});

							$('.ajout_etape').click(function () {
								if (modification_etape) {
									verifier_changements_etapes_sauves(modification_etape, 'wizard-confirmation-annulation', function () {
										launch_wizard('wizard-ajout-etape');
									});
								}
								else {
									etape_ajout = $(this).data().etape;
									etape_ajout_pos = $(this).data().pos;
									launch_wizard('wizard-ajout-etape');
								}
							});

							$('.wizard.preview_etape:not(.final)').click(function () {
								var dialogue = $(this).d();
								if (!dialogue.hasClass('cloneable')) {
									if (modification_etape) {
										if (dialogue.data('etape') !== modification_etape.data('etape')) {
											verifier_changements_etapes_sauves(modification_etape, 'wizard-confirmation-annulation', function () {
												ouvrir_dialogue_preview(dialogue);
											});
										}
									}
									else {
										if (dialogue.find('.image_preview').length > 0) {
											ouvrir_dialogue_preview(dialogue);
										}
									}
								}
							});
						}
					});
				}
			});
		}
	});
}

function ajouter_preview_etape(num_etape, nom_fonction) {
	var wizard_etape = $('.wizard.preview_etape.template').clone(true);
	var div_preview=$('<div>').data('etape',num_etape+'').addClass('image_etape');
	var div_preview_vide=$('<div>')
		.addClass('preview_vide cache')
		.width (dimensions.x *zoom)
		.height(dimensions.y *zoom);
	wizard_etape
		.append(div_preview)
		.append(div_preview_vide);

	wizard_etape.dialog({
		appendTo: '#current-steps',
		resizable: false,
		draggable: false,
		width: 'auto',
		minWidth: 0,
		minHeight: div_preview_vide.height()+'px',
		closeOnEscape: false,
		modal: false,
		open:function() {
			$(this).removeClass('template');
			$(this).data('etape',num_etape);
			$(this).d()
				.addClass('dialog-preview-etape')
				.data({
					etape: num_etape,
					nom_fonction: nom_fonction
				});
			$(this).d().find('.ui-dialog-titlebar').addClass('logo_option')
												   .css({padding:'.3em .6em;'})
										 		   .prepend($('<img>',{height:18,src:base_url+'images/fonctions/'+nom_fonction+'.png',
 	   															 	   alt:nom_fonction}));
			$(this).d().find('.ui-dialog-title').addClass('cache').html(nom_fonction);
		},
		beforeClose:function() {
			$('#num_etape_a_supprimer').html($(this).data('etape'));
			$('#wizard-confirmation-suppression').dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Supprimer": function() {
						var etape=$('#num_etape_a_supprimer').html();
						$.ajax({
							url: '/supprimer_wizard/'+['index',etape].join('/'),
							type: 'post',
							success:function() {
								$('#wizard-confirmation-suppression').dialog().dialog( "close" );
								$('.dialog-preview-etape,.wizard.preview_etape:not(.template)').getElementsWithData('etape',etape).remove();
								chargements[0]='final';
								charger_previews(true);
							}
						});
					},
					"Annuler":function() {
						$(this).dialog().dialog("close");
					}
				}
			});
			return false;
		}
	});
	wizard_etape.d().resize(function(e) {
		if (!($(e.target).hasClass('wizard') || $(e.target).hasClass('ui-dialog'))) {
			return;
		}
		if (modification_etape && modification_etape.find('#options-etape--Polygone').length !== 0) {
			var options=modification_etape.find('[name="form_options"]');
			positionner_points_polygone(options);
		}
	});
	chargements.push(num_etape+'');
}

function charger_tranche_proche_suivante(tranches_pretes, callback) {
	var est_tranche_publiee = tranches_pretes[chargements[chargement_courant]] !== 'en_cours';
	charger_previews_numeros(chargements[chargement_courant], true, est_tranche_publiee, function() {
		if (chargements.length > 0) {
			charger_tranche_proche_suivante(tranches_pretes, callback);
		}
		else {
			callback();
		}
	})
}

function ajouter_et_charger_previews() {
	selecteur_cellules_preview = '.wizard.preview_etape div.image_etape';

	chargements = [];
	jQuery.each(etapes_valides, function(i, etape) {
		ajouter_preview_etape(etape.Ordre, etape.Nom_fonction);
	});
	placer_dialogues_preview();
	charger_previews();
}

function charger_previews(forcer_placement_dialogues) {
	forcer_placement_dialogues = forcer_placement_dialogues || false;
	chargements.push('final'); // On ajoute l'étape finale

	chargement_courant=0;
	charger_preview_etape(chargements[0],true,'_',function() {
		placer_dialogues_preview();
	});
}

function largeur_max_preview_etape_ouverte() {
	var largeur_autres=0;
	$.each($('.wizard.preview_etape:not(.template),#wizard-conception'), function() {
		largeur_autres+=$(this).dialog().dialog('option','width')+LARGEUR_INTER_ETAPES;
	});
	return $(window).width()-largeur_autres;
}

function ouvrir_dialogue_preview(dialogue) {
	modification_etape=dialogue;

	var num_etape=dialogue.data('etape');
	num_etape_courante=num_etape;
	var nom_fonction=dialogue.data('nom_fonction');

	var section_preview_etape=dialogue.find('.preview_etape');
	section_preview_etape.addClass('modif');
	dialogue.addClass('modif');

	section_preview_etape.find('img,.preview_vide').toggleClass('cache');

	var section_preview_vide=dialogue.find('.preview_vide');
	dialogue.find('.ui-dialog-titlebar .ui-dialog-title').removeClass('cache');
	section_preview_vide.after($('#options-etape--'+nom_fonction)
						.removeClass('cache')
						.css({minHeight:section_preview_vide.height()+'px'}));

	section_preview_etape.dialog().dialog('option','buttons',{
		Fermer: function() {
			verifier_changements_etapes_sauves($(this).d(),'wizard-confirmation-annulation');
		},
		Valider: function() {
			valider();
		}
	});
	section_preview_etape.find('button').button();
	section_preview_etape.find('.controlgroup').controlgroup();
	recuperer_et_alimenter_options_preview(num_etape);
}

function fermer_dialogue_preview(dialogue) {
	dialogue.removeClass('modif')
			.css({width:'auto'});
	dialogue.find('.ui-dialog-buttonpane').remove();
	dialogue.find('.ui-dialog-titlebar .ui-dialog-title').addClass('cache');
	dialogue.find('.options_etape').addClass('cache');
	dialogue.find('.image_etape img, .preview_vide').toggleClass('cache');
	dialogue.find('[name="form_options"],[name="form_options_orig"]').remove();
	dialogue.find('.preview_etape').removeClass('modif');
	dialogue.find('.ui-draggable').draggable('destroy');
	dialogue.find('.ui-resizable').resizable('destroy');
	$('#conteneur_selecteur_couleur').addClass('cache');
	modification_etape=null;
}

function placer_dialogues_preview() {
	var currentSteps = $('#current-steps');
	var dialogues=currentSteps.find('.dialog-preview-etape').d();
	var ajoutEtapeTemplate = $('.ajout_etape.template');

	$('.ajout_etape:not(.template)').remove();

	dialogues.sort(function(a, b) {
		return parseInt($(a).data('etape')) < parseInt($(b).data('etape')) ? -1 : 1;
	});
	dialogues.detach().appendTo(currentSteps);

	currentSteps.prepend(ajoutEtapeTemplate.clone(true).removeClass('template hidden')
		.data({
			etape: 0,
			pos: 'avant'
		}).tooltip());

	$.each(dialogues,function(i,dialogue) {
		var elDialogue = $(dialogue);
		elDialogue.addClass('etape');
		var etape = parseInt(elDialogue.data('etape'));

		$(elDialogue).after(
			ajoutEtapeTemplate.clone(true).removeClass('template hidden')
				.data({
					etape: etape,
					pos: 'apres'
				}).tooltip()
		);
	});
}

function recuperer_et_alimenter_options_preview(num_etape) {
	$('.preview_vide').css({backgroundColor:''});
	var section_preview_etape=$('.wizard.preview_etape').getElementsWithData('etape',num_etape);
	var nom_fonction=section_preview_etape.d().data('nom_fonction');
	$.ajax({
		url: '/parametrageg_wizard/'+['index',num_etape,'null'].join('/'),
		type: 'post',
		dataType:'json',
		success:function(data) {
			var valeurs={};
			$.each(data, function(option_nom, option) {
				if (typeof(option.valeur) ==='undefined')
                    option.valeur=option.valeur_defaut;
				valeurs[option_nom]=option.valeur;
			});
			alimenter_options_preview(valeurs, section_preview_etape, nom_fonction);
		}
	});
}

function positionner_agrafe(elAgrafes, image, posRelativeY1, posRelativeY2, hauteur) {
	var pos_x_debut = image.position().left + image.width() / 2 - .25 * zoom;
	var largeur = zoom;
	var pos_y_agrafe1 = image.position().top + posRelativeY1;
	var pos_y_agrafe2 = image.position().top + posRelativeY2;

	elAgrafes.filter('.premiere').css({top: pos_y_agrafe1 + 'px'});
	elAgrafes.filter('.deuxieme').css({top: pos_y_agrafe2 + 'px'});
	elAgrafes
		.css({
			left: pos_x_debut + 'px',
			width:	largeur + 'px',
			height:   hauteur + 'px'
		})
		.removeClass('cache');
}


function alimenter_options_preview(valeurs, section_preview_etape, nom_fonction) {

	var form_userfriendly=section_preview_etape.find('.options_etape');
	var form_options = section_preview_etape.find('[name="form_options"]');
	if (form_options.length === 0) {
		form_options=$('<form>',{name:'form_options'});
		for(var nom_option in valeurs) {
			form_options.append($('<input>',{name:nom_option,type:'hidden'}).val(templatedToVal(valeurs[nom_option])));
		}
		section_preview_etape
			.append(form_options)
			.append(
				form_options.clone(true)
					.attr({name:'form_options_orig'})
			);
	}

	var image = section_preview_etape.find('.preview_vide');
	image
		.width (dimensions.x*zoom)
		.height(dimensions.y*zoom);

	var padding_dialogue = form_userfriendly.d().outerWidth(false)
						 - form_userfriendly.d().innerWidth();
	form_userfriendly.css({marginLeft:(padding_dialogue+image.width()+PADDING_PARAMETRAGE_ETAPE)+'px'});

	var checkboxes=[];
	switch(nom_fonction) {
		case 'Agrafer':
			var agrafes = form_userfriendly.find('.agrafe');

			positionner_agrafe(
				agrafes,
				image,
				parseFloat(templatedToVal(valeurs.Y1))*zoom,
				parseFloat(templatedToVal(valeurs.Y2))*zoom,
				parseFloat(templatedToVal(valeurs.Taille_agrafe))*zoom
			);

			agrafes
				.draggable({
					axis: 'y',
					stop:function(event) {
						var element=$(event.target);
						tester_options_preview([element.hasClass('premiere') ? 'Y1' : 'Y2'],element);
					}
				})
				.resizable({
					handles:'s',
					resize:function(event, ui) {
						tester_options_preview(['Taille_agrafe'],ui.element);
					}
				});
		break;

		case 'Degrade':

			var pos_x_debut=image.position().left +parseFloat(templatedToVal(valeurs.Pos_x_debut))*zoom;
			var pos_x_fin=image.position().left +parseFloat(templatedToVal(valeurs.Pos_x_fin))*zoom;
			var pos_y_debut=image.position().top +parseFloat(templatedToVal(valeurs.Pos_y_debut))*zoom;
			var pos_y_fin=image.position().top +parseFloat(templatedToVal(valeurs.Pos_y_fin))*zoom;

			var rectangle = form_userfriendly.find('.rectangle_degrade');

			rectangle
				.css({
					top:	pos_y_debut 		   +'px',
					left:   pos_x_debut 		   +'px',
					width:  (pos_x_fin-pos_x_debut)+'px',
					height: (pos_y_fin-pos_y_debut)+'px'
				})
				.removeClass('cache')
				.draggable({//containment:limites_drag,
					 stop:function() {
						tester_options_preview(['Pos_x_debut','Pos_y_debut','Pos_x_fin','Pos_y_fin']);
					 }
				})
				.resizable({
					 stop:function() {
						 tester_options_preview(['Pos_x_fin', 'Pos_y_fin']);
					 }
				});
			coloriser_rectangle_degrade(rectangle,'#'+valeurs.Couleur_debut,'#'+valeurs.Couleur_fin,valeurs.Sens);

			var choix = form_userfriendly.find('[name="option-Sens"]');
			choix.click(function() {
   				tester_options_preview(['Sens']);
   				coloriser_rectangle_degrade(rectangle,null,null,$(this).val());
			});
		break;

		case 'DegradeTrancheAgrafee':
			positionner_agrafe(form_userfriendly.find('.agrafe'), image, 0.2*image.height(), 0.8*image.height(), 0.05*image.height());

			var rectangle1 = form_userfriendly.find('.premier.rectangle_degrade');
			var rectangle2 = form_userfriendly.find('.deuxieme.rectangle_degrade');

			rectangle1.css({left:image.position().left+'px'});
			rectangle2.css({left:parseInt(image.position().left+image.width()/2)+'px'});
			form_userfriendly.find('.rectangle_degrade')
				.css({top:	image.position().top +'px',
					  width:  image.width()/2	  	 +'px',
					  height: image.height()		 +'px'})
				.removeClass('cache');
			coloriser_rectangles_degrades(valeurs.Couleur);

		break;
		case 'Remplir':
			$('.preview_vide').css({backgroundColor: '#'+valeurs.Couleur});

			var largeur_croix=form_userfriendly.find('.point_remplissage').width()/2;
			var limites_drag=[(image.offset().left			 	 -largeur_croix+1),
							  (image.offset().top 			 	 -largeur_croix+1),
							  (image.offset().left+image.width() -largeur_croix-1),
							  (image.offset().top +image.height()-largeur_croix-1)];
			form_userfriendly.find('.point_remplissage')
				.css({
					left:(image.position().left-largeur_croix+1+parseFloat(valeurs.Pos_x)*zoom)+'px',
					top :(image.position().top -largeur_croix+1+parseFloat(valeurs.Pos_y)*zoom)+'px'})
				.removeClass('cache')
				.draggable({
					containment:limites_drag,
					stop:function() {
						tester_options_preview(['Pos_x', 'Pos_y']);
					}});
		break;
		case 'Arc_cercle':

			var arc=form_userfriendly.find('.arc_position');

			if (section_preview_etape.find('.preview_vide .arc_position').length === 0) {
				arc = arc.clone(true);
				arc.appendTo(section_preview_etape.find('.preview_vide'));
			}
			else {
				arc = section_preview_etape.find('.preview_vide .arc_position');
			}
			dessiner(arc, 'Arc_cercle', form_options);

			checkboxes.push('Rempli');
			form_userfriendly.valeur('Rempli')
							 .val(valeurs.Rempli === 'Oui')
							 .change(function() {
								 var nom_option=$(this).attr('name').replace(REGEX_OPTION,'$1');
								 tester_options_preview([nom_option]);
								 dessiner(arc, 'Arc_cercle', $('[name="form_options"]'));
							 });
			form_userfriendly.valeur('drag-resize').change(function() {
				var arc = section_preview_etape.find('.preview_vide .arc_position');
				if ($(this).val()==='deplacement') {
					if (arc.is('.ui-resizable')) {
						arc.resizable("destroy");
					}
					arc.draggable({
						stop: function() {
						   tester_options_preview(['Pos_x_centre', 'Pos_y_centre']);
						}
					});
				}
				else {
					if (arc.is('.ui-draggable')) {
						arc.draggable("destroy");
					}
					arc.resizable({
						 stop: function() {
						   tester_options_preview(['Largeur','Hauteur','Pos_x_centre','Pos_y_centre']);
						   dessiner(arc, 'Arc_cercle', $('[name="form_options"]'));
						 }
					 });
				}
			});
			form_userfriendly.find('#Arc_deplacement').click();

		break;

		case 'Polygone':

			var polygone=form_userfriendly.find('.polygone_position');

			if (section_preview_etape.find('.preview_vide .polygone_position').length === 0) {
				polygone = polygone.clone(true);
				polygone.appendTo(section_preview_etape.find('.preview_vide'));
			}
			else {
				polygone = section_preview_etape.find('.preview_vide .polygone_position');
			}
			dessiner(polygone, 'Polygone', form_options, function() {
				positionner_points_polygone(form_options);

			});

			form_userfriendly.valeur('action').change(function() {
				var action = $(this).val();
				form_userfriendly.find('#descriptions_actions div').addClass('cache');
				form_userfriendly.find('#descriptions_actions div#description_'+action).removeClass('cache');
				positionner_points_polygone(form_options);
			});

			form_userfriendly.find('#Point_deplacement').click();

		break;
		case 'Rectangle':

			var position_rectangle=form_userfriendly.find('.rectangle_position');

			var pos_x_debut=image.position().left+parseFloat(templatedToVal(valeurs.Pos_x_debut))*zoom;
			var pos_y_debut=image.position().top +parseFloat(templatedToVal(valeurs.Pos_y_debut))*zoom;
			var pos_x_fin=image.position().left+parseFloat(templatedToVal(valeurs.Pos_x_fin))*zoom;
			var pos_y_fin=image.position().top +parseFloat(templatedToVal(valeurs.Pos_y_fin))*zoom;

			position_rectangle.css({left:			  pos_x_debut +'px',
									top: 			  pos_y_debut +'px',
									width: (pos_x_fin-pos_x_debut)+'px',
									height:(pos_y_fin-pos_y_debut)+'px'})
						  .removeClass('cache')
						  .draggable({//containment:limites_drag,
					  		  stop:function() {
				   				tester_options_preview(['Pos_x_debut','Pos_y_debut','Pos_x_fin','Pos_y_fin']);
				   			  }
						  })
						  .resizable({
								stop:function() {
					   				tester_options_preview(['Pos_x_fin','Pos_y_fin']);
					   			}
						  });

			checkboxes.push('Rempli');
			form_userfriendly.valeur('Rempli')
							 .val(valeurs.Rempli === 'Oui')
							 .change(function() {
								 var nom_option=$(this).attr('name').replace(REGEX_OPTION,'$1');
								 tester_options_preview([nom_option]);
								 coloriser_rectangle_preview(valeurs.Couleur,$(this).prop('checked'));
							 });

			coloriser_rectangle_preview(valeurs.Couleur,valeurs.Rempli === 'Oui');

		break;
		case 'Image':

			$.each($(['Source']),function(i,option_nom) {
				form_userfriendly.valeur(option_nom).val(valeurs[option_nom]);
			});

			definir_et_positionner_image(templatedToVal(valeurs.Source));

			form_userfriendly.find('[name="parcourir"]').click(function(event) {
				event.preventDefault();

				$('#wizard-images')
					.addClass('autres_photos')
					.removeClass('photo_principale photos_texte');
				launch_wizard('wizard-images', {
					modal:true,
					first: true,
					width: 600
				});
			});

		break;
		case 'TexteMyFonts':

			$.each($(['Chaine','URL','Largeur']),function(i,option_nom) {
				form_userfriendly.valeur(option_nom).val(valeurs[option_nom]);
			});

			var tester_et_charger_preview_myfonts = function() {
				var nom_option=$(this).attr('name').replace(REGEX_OPTION,'$1');
				tester_options_preview([nom_option]);
			};

			form_userfriendly.find('input[name="option-Chaine"],input[name="option-URL"],input[name="option-Largeur"]')
				.blur(tester_et_charger_preview_myfonts);

			form_userfriendly.find('.modifier_police').click(function() {
				$('#wizard-images')
					.addClass('photos_texte')
					.removeClass('photo_principale autres_photos');
				launch_wizard('wizard-images', {
					modal:true,
					first: true,
					width: 600
				});
			});

			checkboxes.push('Demi_hauteur');
			form_userfriendly
				.valeur('Demi_hauteur')
				.change(tester_et_charger_preview_myfonts);

			$(document).mouseup( stopRotate);
			var input_rotation=form_userfriendly.valeur('Rotation');
			input_rotation.data('currentRotation',0)
						  .mousedown( startRotate );
			$('[name~="fixer_rotation"]').click(function() {
				var angle=$(this).prop('name').split(/ /g)[1];
				rotateImageDegValue(input_rotation,angle);
			});
			rotateImageDegValue(input_rotation,-1*parseFloat(valeurs.Rotation));

			form_userfriendly.find('.accordion').accordion({
				active: 0,
				activate:function() {
					var section_active_integration=$(this).find('.ui-accordion-content-active').hasClass('finition_texte_genere');
					if (section_active_integration) {
						generer_et_positionner_preview_myfonts(false,true,false);
					}
				}
			});
			generer_et_positionner_preview_myfonts(true,false,true);

		break;
	}

	form_userfriendly.find('.couleur').each(function() {
		var input=$(this);
		input
			.click(function() {
				current_color_input = $(this);
				colorpicker.val('#'+current_color_input.val()).trigger('change');

				$('#conteneur_selecteur_couleur').removeClass('cache');
				$('.couleur.selected').removeClass('selected');
				current_color_input.addClass('selected');
			})
			.blur(function() {
				$(this).removeClass('selected');
			});

		var nom_option=input.attr('name').replace(REGEX_OPTION,'$1');
		affecter_couleur_input(input, valeurs[nom_option]);
	});

	$.each(checkboxes, function(i, checkbox) {
		form_userfriendly
			.valeur(checkbox)
			.attr('checked',valeurs[checkbox]==='Oui');
	});
}

function dessiner(element, type, form_options, callback) {
	callback = callback || function() {};
	var url_appel='/dessiner/'+"index/"+type+"/"+zoom+"/0";
	var options = [];
	switch(type) {
		case 'Arc_cercle':
			options = ['Couleur','Pos_x_centre','Pos_y_centre','Largeur','Hauteur','Angle_debut','Angle_fin','Rempli'];
		break;
		case 'Polygone':
			options = ['X','Y','Couleur'];
		break;
	}

	element.css({left:(parseFloat(form_options.valeur('Pos_x_centre').val())*zoom
					 - parseFloat(form_options.valeur('Largeur').val())		*zoom/2)+'px',
				 top :(parseFloat(form_options.valeur('Pos_y_centre').val())*zoom
					 - parseFloat(form_options.valeur('Hauteur').val())		*zoom/2)+'px'});

	$.each($(options),function(i,nom_option) {
		if (nom_option === 'Pos_x_centre')
			url_appel+="/"+toFloat2Decimals(parseFloat(form_options.valeur('Largeur').val())/2);
		else if (nom_option === 'Pos_y_centre')
			url_appel+="/"+toFloat2Decimals(parseFloat(form_options.valeur('Hauteur').val())/2);
		else
			url_appel+="/"+form_options.valeur(nom_option).val();
	});
	element
		.attr({src:url_appel})
		.load(function() {
			$(this).removeClass('cache');
			callback();
		});
}

var INTERVALLE_AJOUT_POINT_POLYGONE=2;
var derniere_demande_ajout_point=0;
function positionner_points_polygone(form_options) {
	var dialogue = $('.wizard.preview_etape.modif');
	var preview_vide = dialogue.find('.preview_vide');
	var options_etape = dialogue.find('.options_etape');
	var polygone = preview_vide.find('.polygone_position');

	if (polygone.length === 0) {
		return;
	}

	var liste_x=form_options.valeur('X').val().split(',');
	var liste_y=form_options.valeur('Y').val().split(',');

	var points_a_placer=[];
	for (var i=0;i<liste_x.length;i++) {
		var x=zoom*parseFloat(liste_x[i]);
		var y=zoom*parseFloat(liste_y[i]);
		points_a_placer.push([
			i,
			x-COTE_CARRE_DEPLACEMENT/2,
			y-COTE_CARRE_DEPLACEMENT/2
		]);

	}

	preview_vide.find('.point_polygone:not(.modele)').remove();
	for (i in points_a_placer) {
		var point = points_a_placer[i];
		var nouveau_point= options_etape.find('.point_polygone.modele')
			.clone(true)
				.removeClass('modele cache')
				.attr({name:'point'+point[0]})
				.css({marginLeft:point[1]+'px',
			 		  marginTop: point[2]+'px'})
			 	.mouseleave(function() {
			 		$(this).removeClass('focus');
			 		if ($(this).draggable()) {
			 			$(this).draggable("destroy");
					}
			 		$(this).click(function() {});
			 	})
			 	.mouseover(function() {
			 		$(this).addClass('focus');
			 		var action = options_etape.valeur('action').filter(':checked').val();

			 		switch(action) {
					case 'ajout':
						$(this).click(function() {
							var millis=new Date().getTime();
							if (millis - derniere_demande_ajout_point < INTERVALLE_AJOUT_POINT_POLYGONE*1000) {
								return;
							}
							derniere_demande_ajout_point=millis;

							var point1=$(this);
							var nom_point1=point1.attr('name');
							var num_point1=parseInt(nom_point1.substring(5,nom_point1.length));
							var point2=$('.point_polygone[name="point'+(num_point1+1)+'"]');
							if (point2.length === 0) {
								point2=$('.point_polygone[name="point0"]');
							}
							for (var i=$('.point_polygone:not(.modele)').length -1; i>=num_point1+1; i--) {
								$('.point_polygone[name="point'+i+'"]').attr({name:'point'+(i+1)});
							}
							var nouveau_point={marginLeft:(parseFloat(point1.css('margin-left').replace(/px$/,''))
											   				 +parseFloat(point2.css('margin-left').replace(/px$/,'')))/2,
											   marginTop: (parseFloat(point1.css('margin-top' ).replace(/px$/,''))
													   		 +parseFloat(point2.css('margin-top' ).replace(/px$/,'')))/2};

							point1.after($('<div>').addClass('point_polygone')
												   .attr({name:'point'+(num_point1+1)})
												   .css(nouveau_point));

				 			tester_options_preview(['X','Y']);
				 			dessiner(polygone, 'Polygone', form_options, function() {
					 			positionner_points_polygone(form_options);
				 			});
						});
					break;
					case 'deplacement':
						$(this).draggable({
					 		stop: function() {
					 			tester_options_preview(['X','Y']);

					 			var form_options = $('[name="form_options"]');
					 			dessiner(polygone, 'Polygone', form_options, function() {
						 			positionner_points_polygone(form_options);
					 			});
							}
						});
					break;
					case 'suppression':
						$(this).click(function() {
							var nom_point=$(this).attr('name');
							$('#nom_point_a_supprimer').html(nom_point);
							$('#wizard-confirmation-suppression-point').dialog({
								resizable: false,
								height:250,
								modal: true,
								buttons: {
									"Supprimer": function() {
										$('#wizard-confirmation-suppression-point').dialog().dialog( "close" );
										var nom_point=$('#nom_point_a_supprimer').html();
										$('.point_polygone[name="'+nom_point+'"]:not(.modele)').remove();

							 			tester_options_preview(['X','Y']);
							 			dessiner(polygone, 'Polygone', form_options, function() {
								 			positionner_points_polygone(form_options);
							 			});
									}
								}
							});
						});

					break;
				}
			 });
		preview_vide.append(nouveau_point);
	}

}

function positionner_image(preview) {
	var form_userfriendly=modification_etape.find('.options_etape');
	var position_image=form_userfriendly.find('.image_position');
	var dialogue=preview.d();
	var valeurs=dialogue.find('[name="form_options"]').serializeObject();
	var image=dialogue.find('.preview_vide');

	var ratio_image=preview.prop('width')/preview.prop('height');

	var largeur=toFloat2Decimals(image.width() * parseFloat(valeurs.Compression_x));
	var hauteur=toFloat2Decimals(image.width() * parseFloat(valeurs.Compression_y) / ratio_image);

	var pos_x=image.position().left+parseFloat(valeurs.Decalage_x)*zoom;
	var pos_y;
	if (valeurs.Position === 'bas') {
		pos_y=image.position().top + image.height() - hauteur - parseFloat(valeurs.Decalage_y)*zoom;
	}
	else {
		pos_y=image.position().top +parseFloat(valeurs.Decalage_y)*zoom;
		if (valeurs.Mesure_depuis_haut === 'Non') { // Le pos_y est mesuré entre le haut de la tranche et le bas du texte
			pos_y-=parseFloat(hauteur);
		}
	}

	if (position_image.hasClass('ui-resizable')) {
		position_image.resizable('destroy');
	}

	position_image
		.addClass('outlined')
		.css({outlineColor:'#000000',
			backgroundImage:'',
			backgroundColor:'white',
			left:pos_x+'px',
			top: pos_y+'px',
			width:largeur+'px',
			height:hauteur+'px'})
		.removeClass('cache')
		.html(
			$('<img>', {src:preview.attr('src')})
				.error(afficher_erreur_image_inexistante)
		)
		.draggable({//containment:limites_drag
			stop:function() {
				tester_options_preview(['Decalage_x','Decalage_y']);
			}
		})
		.resizable({
			stop:function() {
				tester_options_preview(['Compression_x','Compression_y']);
			}
		});
}

function definir_et_positionner_image(source) {
	if (source === '') {
		return;
	}
	var form_userfriendly=modification_etape.find('.options_etape');
	var apercu_image=form_userfriendly.find('.apercu_image');
	apercu_image
		.attr({src:edges_url+'/'+pays+'/elements/'+source})
		.load(function() {
			positionner_image($(this));
		})
		.error(afficher_erreur_image_inexistante);
}
function coloriser_rectangle_preview(couleur,est_rempli) {
	if (est_rempli) {
		modification_etape.find('.rectangle_position')
			.css({backgroundColor: couleur})
			.removeClass('outlined');
	}
	else {
		modification_etape.find('.rectangle_position')
			.css({outlineColor:couleur, backgroundColor:''})
			.addClass('outlined');
	}
}

function coloriser_rectangles_degrades(c1) {
	var coef_degrade=1.75;

	var c1_rgb=hex2rgb(c1);
	var c2=rgb2hex(parseInt(c1_rgb[0]/coef_degrade),
				   parseInt(c1_rgb[1]/coef_degrade),
				   parseInt(c1_rgb[2]/coef_degrade));
	coloriser_rectangle_degrade(modification_etape.find('.premier.rectangle_degrade'), c1,c2);
	coloriser_rectangle_degrade(modification_etape.find('.deuxieme.rectangle_degrade'),c2,c1);
}

function coloriser_rectangle_degrade(element,couleur1,couleur2, sens) {
	sens = sens || 'Horizontal';
	if (couleur1 === null) {// On garde la même couleur
		var regex=/, from\(((?:(?!\),).)+)/g;
		couleur1 = element.css('background').match(regex)[0].replace(regex,'$1');
	}
	if (couleur2 === null) {// On garde la même couleur
		var regex = /, to\(((?:(?!\)\) ).)+)/g;
		couleur2 = element.css('background').match(regex)[0].replace(regex,'$1');
	}
	if (sens === 'Horizontal') {
		element.css({background: '-webkit-gradient(linear, left top, right top, from('+couleur1+'), to('+couleur2+'))'});
	}
	else {
		element.css({background: '-webkit-gradient(linear, left top, left bottom, from('+couleur1+'), to('+couleur2+'))'});
	}
}

function verifier_changements_etapes_sauves(dialogue, id_dialogue_proposition_sauvegarde, callback) {
	callback=callback || function() {};
	if ( dialogue.find('[name="form_options"]').serialize()
	 !== dialogue.find('[name="form_options_orig"]').serialize()) {
		$("#"+id_dialogue_proposition_sauvegarde).dialog({
			resizable: false,
			height:300,
			modal: true,
			buttons: {
				"Sauvegarder les changements": function() {
					$('#wizard-confirmation-annulation').dialog().dialog( "close" );
					valider(function() {
						fermer_dialogue_preview($('.modif'));
						callback();
					});
				},
				"Fermer l'etape sans sauvegarder": function() {
					fermer_dialogue_preview($('.modif'));
					$( this ).dialog().dialog( "close" );
					chargements=['final']; // Etape finale
					chargement_courant=0;
					charger_preview_etape(chargements[0],true,'_',callback);
				},
				"Revenir a l'edition d'etape": function() {
					$( this ).dialog().dialog( "close" );
				}
			}
		});
	}
	else {
		fermer_dialogue_preview($('.modif'));
		callback();
	}
}

function tester() {
	var dialogue=modification_etape.d();

	var form_options=dialogue.find('[name="form_options"]');

	chargements=['final']; // Etape finale
	chargement_courant=0;
	charger_preview_etape(chargements[0],true,num_etape_courante+"."+form_options.serialize(),function() {});
}

function valider(callback) {
  var modif_form_wrapper = $('.modif');
  var parametrage		  =modif_form_wrapper.find('[name="form_options"]').serialize();
	var parametrage_orig=modif_form_wrapper.find('[name="form_options_orig"]').serialize();
	if (parametrage === parametrage_orig) {
		fermer_dialogue_preview(modification_etape);
	}
	else {
		callback = callback || function(){};
		$.ajax({
			url: '/update_wizard/'+['index',num_etape_courante,parametrage].join('/'),
			type: 'post',
			success:function() {
				charger_couleurs_frequentes();
				reload_current_and_final_previews(callback);
			}
		});
	}
}

function tester_options_preview(noms_options, element) {
	var dialogue=$('.wizard.preview_etape.modif').d();
	var form_options=dialogue.find('[name="form_options"]');
	var form_userfriendly=dialogue.find('.options_etape');
	var nom_fonction=dialogue.data('nom_fonction');
	var image=dialogue.find('.preview_vide');

	var tester_apres = false;

	$.each(noms_options,function(i, nom_option) {
		var val=null;
		if (nom_option.indexOf('Couleur') === 0) {
			val=form_userfriendly.valeur(nom_option).val().replace(/#/g,'');
		}
		else {
			switch(nom_fonction) {
				case 'Agrafer':
					switch(nom_option) {
						case 'Taille_agrafe':
							form_userfriendly.find('.agrafe').not(element).height(element.height());
							val = element.height()/zoom;
						break;
						case 'Y1': case 'Y2':
							val = (element.offset().top-image.offset().top)/zoom;
						break;
					}
				break;
				case 'Degrade':
					var zone_degrade=dialogue.find('.rectangle_degrade');
					switch(nom_option) {
						case 'Pos_x_debut':
							val = toFloat2Decimals(parseFloat((zone_degrade.offset().left - image.offset().left)/zoom));
						break;
						case 'Pos_y_debut':
							val = toFloat2Decimals(parseFloat((zone_degrade.offset().top  - image.offset().top )/zoom));
						break;
						case 'Pos_x_fin':
							val = toFloat2Decimals(parseFloat((zone_degrade.offset().left + zone_degrade.width() - image.offset().left)/zoom));
						break;
						case 'Pos_y_fin':
							val = toFloat2Decimals(parseFloat((zone_degrade.offset().top  + zone_degrade.height()- image.offset().top )/zoom));
						break;
						case 'Sens':
							val = form_userfriendly.valeur(nom_option).filter(':checked').val();
						break;
					}
				break;
				case 'Remplir':
					var point_remplissage=dialogue.find('.point_remplissage');
					var limites_drag_point_remplissage=point_remplissage.draggable('option','containment');
					switch(nom_option) {
						case 'Pos_x':
							val = toFloat2Decimals(parseFloat((point_remplissage.offset().left - limites_drag_point_remplissage[0])/zoom));
						break;
						case 'Pos_y':
							val = toFloat2Decimals(parseFloat((point_remplissage.offset().top - limites_drag_point_remplissage[1])/zoom));
						break;
					}
				break;
				case 'Arc_cercle':
					var arc=dialogue.find('.arc_position');
					switch(nom_option) {
						case 'Pos_x_centre':
							val = toFloat2Decimals(parseFloat(form_options.valeur('Largeur').val())/2
												 + parseFloat(arc.position().left)/zoom);
						break;
						case 'Pos_y_centre':
							val = toFloat2Decimals(parseFloat(form_options.valeur('Hauteur').val())/2
									 			 + parseFloat(arc.position().top)/zoom);
						break;
						case 'Largeur':
							val=arc.width()/zoom;
						break;
						case 'Hauteur':
							val=arc.height()/zoom;
						break;
						case 'Rempli':
							val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';
						break;
					}
				break;
				case 'Polygone':
					switch(nom_option) {
						case 'X':
							var x = [];
							$.each(dialogue.find('.point_polygone:not(.modele)'),function(i,point) {
								point=$(point);
								x[i] = (point.offset().left + point.scrollLeft() - image.offset().left + COTE_CARRE_DEPLACEMENT/2) / zoom;

							});
							val=x.join(',');
						break;
						case 'Y':
							var y = [];
							$.each(dialogue.find('.point_polygone:not(.modele)'),function(i,point) {
								point=$(point);
								y[i] = (point.offset().top + point.scrollTop() - image.offset().top + COTE_CARRE_DEPLACEMENT/2) / zoom;

							});
							val=y.join(',');
						break;
					}
				break;
				case 'Rectangle':
					var positionnement= modification_etape.find('.rectangle_position');
					switch(nom_option) {
						case 'Pos_x_debut':
							val = toFloat2Decimals(parseFloat(positionnement.offset().left - image.offset().left)/zoom);
						break;
						case 'Pos_y_debut':
							val = toFloat2Decimals(parseFloat(positionnement.offset().top  - image.offset().top )/zoom);
						break;
						case 'Pos_x_fin':
							val = toFloat2Decimals(parseFloat(positionnement.offset().left + positionnement.width() - image.offset().left)/zoom);
						break;
						case 'Pos_y_fin':
							val = toFloat2Decimals(parseFloat(positionnement.offset().top  + positionnement.height()- image.offset().top )/zoom);
						break;
						case 'Rempli':
							val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';
						break;
					}
				break;
				case 'Image':
					var positionnement=dialogue.find('.image_position');
					switch(nom_option) {
						case 'Decalage_x':
							val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
						break;
						case 'Decalage_y':
							var pos_y_image;
							if (form_userfriendly.valeur('Position') === 'bas') {
								pos_y_image=image.height() - (positionnement.offset().top - image.offset().top);
							}
							else {
								pos_y_image=positionnement.offset().top - image.offset().top;
							}

							val = toFloat2Decimals(parseFloat((pos_y_image)/zoom));
							form_options.valeur('Mesure_depuis_haut').val('Oui');
						break;
						case 'Compression_x':
							val = toFloat2Decimals(positionnement.width()/image.width());
						break;
						case 'Compression_y':
							var compression_x=parseFloat(form_options.valeur('Compression_x').val());

							var image_preview=dialogue.find('.apercu_image');
							var ratio_image=image_preview.prop('width')/image_preview.prop('height');
							var ratio_positionnement=positionnement.width()/positionnement.height();
							val = toFloat2Decimals(compression_x*(ratio_image/ratio_positionnement));
						break;
						case 'Source':
							val=$('.gallery:visible li:not(.template) img.selected').attr('src').replace(/.*\/([^\/]+)/,'$1');
							form_userfriendly.valeur(nom_option).val(val);

							definir_et_positionner_image(val);
					}
				break;
				case 'TexteMyFonts':
					var positionnement=dialogue.find('.image_position');
					switch(nom_option) {
						case 'Pos_x':
							val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
						break;
						case 'Pos_y':
							var pos_y_rectangle=positionnement.offset().top - image.offset().top;
							val = toFloat2Decimals(parseFloat((pos_y_rectangle)/zoom));
							form_options.valeur('Mesure_depuis_haut').val('Oui');
						break;
						case 'Compression_x':
							val = toFloat2Decimals(positionnement.width()/image.width());
						break;
						case 'Compression_y':
							var image_preview_ajustee=$('body>.apercu_myfonts img');
							var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');
							var hauteur_image=dialogue.find('.image_position').height();
							var largeur_preview=dialogue.find('.preview_vide').width();

							val = hauteur_image * ratio_image_preview_ajustee / largeur_preview;
						break;
						case 'Chaine': case 'URL':
							val=form_userfriendly.valeur(nom_option).val();
						break;
						case 'Largeur':
							var largeur_courante=form_options.valeur('Largeur').val();
							var largeur_physique_preview=dialogue.find('div.extension_largeur').offset().left
														-dialogue.find('.finition_texte_genere .apercu_myfonts img').offset().left;
							val=parseInt(largeur_courante)* (largeur_physique_preview/largeur_physique_preview_initiale);
						break;
						case 'Demi_hauteur':
							val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';
						break;
						case 'Rotation':
							val=-1*radToDeg(form_userfriendly.valeur(nom_option).data('currentRotation'));
						break;
					}
				break;
			}
		}
		form_options.valeur(nom_option).val(val);

		if (nom_fonction === 'TexteMyFonts' && ['Chaine','URL','Largeur','Demi_hauteur','Rotation'].indexOf(nom_option) !== -1) {
			var generer_preview_proprietes = nom_option === 'Chaine'  || nom_option === 'URL',
				generer_preview_finition = nom_option === 'Largeur' || nom_option === 'Demi_hauteur';
			generer_et_positionner_preview_myfonts(generer_preview_proprietes,
												   generer_preview_finition,
												   true);
		}
		else {
			tester_apres=true;
		}
	});
	if (tester_apres) {
		tester();
	}
}

function generer_et_positionner_preview_myfonts(gen_preview_proprietes, gen_preview_finition, gen_tranche) {
	load_myfonts_preview(gen_preview_proprietes,gen_preview_finition,gen_tranche, !gen_tranche ? function() {} : function() {
		var dialogue=modification_etape.d();
		var form_userfriendly=dialogue.find('.options_etape');
		var valeurs=dialogue.find('[name="form_options"]').serializeObject();
		var image=dialogue.find('.preview_vide');

		var position_texte=form_userfriendly.find('.image_position');
		var image_preview_ajustee=$('body>.apercu_myfonts img');
		var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');

		var largeur=toFloat2Decimals(image.width() * parseFloat(valeurs.Compression_x));
		var hauteur=toFloat2Decimals(image.width() * parseFloat(valeurs.Compression_y) / ratio_image_preview_ajustee);

		var pos_x=image.position().left+parseFloat(valeurs.Pos_x)*zoom;
		var pos_y=image.position().top +parseFloat(valeurs.Pos_y)*zoom;
		if (valeurs.Mesure_depuis_haut === 'Non') { // Le pos_y est mesuré entre le haut de la tranche et le bas du texte
			pos_y-=parseFloat(hauteur);
		}

		position_texte.css({left:pos_x+'px',
							top: pos_y+'px',
							width:largeur+'px',
							height:hauteur+'px'})
					  .removeClass('cache')
					  .draggable({//containment:limites_drag,
				  		  stop:function() {
			   				tester_options_preview(['Pos_x','Pos_y']);
			   				tester();
			   			  }
					  })
					  .resizable({
							stop:function() {
				   				tester_options_preview(['Compression_x','Compression_y']);
				   			}
					  });
		var image_a_positionner = image_preview_ajustee.clone(false);
		if (position_texte.find('img').length === 0) {
			position_texte.append(image_a_positionner);
		}
		else {
			position_texte.find('img').replaceWith(image_a_positionner);
		}

		if (dialogue.find('[name="original_preview_width"]' ).val() === '') {
			dialogue.find('[name="original_preview_width"]' ).val(largeur);
			dialogue.find('[name="original_preview_height"]').val(hauteur);
		}

		placer_extension_largeur_preview();
		tester();
	});
}


function reload_current_and_final_previews(callback) {
	chargements=[modification_etape.data('etape')];
	fermer_dialogue_preview(modification_etape);
	charger_preview_etape(chargements[0],true, undefined, function() {
		chargements=['final'];
		charger_preview_etape(chargements[0],true, undefined, callback);
	});
}

function update_previews_dimensions() {
	if (modification_etape) {
		modification_etape.find('.preview_etape').css({minHeight: $('.preview_vide').height()});

		var valeurs = modification_etape.find('[name="form_options"]').serializeObject();
		var section_preview_etape = modification_etape.find('.preview_etape');
		var nom_fonction = modification_etape.data('nom_fonction');
		alimenter_options_preview(valeurs, section_preview_etape, nom_fonction);
	}

	$('.photo_tranche img').height(parseInt($('#Dimension_y').val()) * zoom);

	$('.ui-draggable').draggable('destroy');
	$('.ui-resizable').resizable('destroy');

	selecteur_cellules_preview='.wizard.preview_etape div.image_etape';

	chargements=$.map($(selecteur_cellules_preview),function(element) {
		return parseInt($(element).data('etape')) || 'final';
	});

	chargement_courant=0;
	charger_preview_etape(chargements[0],true,undefined /*<-- Parametrage */,function(image) {
		var dialogue=image.d();
		var num_etape=dialogue.data('etape');

		if (modification_etape) {
			if (dialogue.data('etape') === modification_etape.data('etape'))
				recuperer_et_alimenter_options_preview(num_etape);
		}
	});
}

function charger_couleurs_frequentes() {
	$.ajax({ // Couleurs utilisées dans l'ensemble des étapes de la conception de tranche
		url: '/couleurs_frequentes/'+['index'].join('/'),
		type: 'post',
		dataType:'json',
		success:function(data) {
			var template = $('.couleur_frequente.template');
			$('.couleur_frequente').not(template).remove();
			$.each(data, function(i, couleur) {
				var nouvel_element =
					template
						.clone(true)
						.removeClass('template')
						.click(function() {
							colorpicker.val('#'+$(this).val()).trigger('change');
							$('#selecteur_couleur').tabs( "option", "active", 0);
						});
				affecter_couleur_input(nouvel_element, couleur);
				$('#couleurs_frequentes').append(nouvel_element);
			});
		}
	});
}

function affecter_couleur_input(input_couleur, couleur) {
	var r=couleur.substring(0,2),
		g=couleur.substring(2,4),
		b=couleur.substring(4,6);
	var couleur_foncee=parseInt(r,16)
					  *parseInt(g,16)
					  *parseInt(b,16) < 100*100*100;
	input_couleur
		.css({backgroundColor:'#'+couleur,
			  color:couleur_foncee ? '#ffffff' : '#000000'})
		.val(couleur.toUpperCase());

}


function callback_test_picked_color() {
	var nom_option=current_color_input.attr('name').replace(REGEX_OPTION,'$1');
	var nom_fonction=current_color_input.closest('.ui-dialog').data('nom_fonction');

	tester_options_preview([nom_option]);
	var form_options=current_color_input.d().find('[name="form_options"]');
	var couleur = colorpicker.val();
	switch (nom_fonction) {
		case 'Remplir':
			$('.preview_vide').css({backgroundColor: couleur});
		break;
		case 'Degrade':
			if (current_color_input.attr('name').indexOf('Couleur_debut') !== -1)
				coloriser_rectangle_degrade(form_options.d().find('.rectangle_degrade'),couleur,null,form_options.valeur('Sens').val());
			else
				coloriser_rectangle_degrade(form_options.d().find('.rectangle_degrade'),null,couleur,form_options.valeur('Sens').val());
		break;
		case 'DegradeTrancheAgrafee':
			coloriser_rectangles_degrades(couleur.replace(/#/g,''));
		break;
		case 'TexteMyFonts':
			load_myfonts_preview(true,true,true);
		break;
		case 'Rectangle':
			coloriser_rectangle_preview(couleur,
										form_options.valeur('Rempli').val()==='Oui');
		break;
		case 'Arc_cercle':
			dessiner($('.preview_vide .arc_position'), 'Arc_cercle', form_options);
		break;
		case 'Polygone':
			dessiner($('.preview_vide .polygone_position'), 'Polygone', form_options);
		break;
	}
}

var isMyFontsError = false;
function load_myfonts_preview(preview1, preview2, preview3, callback) {
	var dialogue=$('.wizard.preview_etape.modif').d();
	var form_options=dialogue.find('[name="form_options"]');
	var selectors=['.image_position'];
	if (preview1)
		selectors.push('.proprietes_texte .apercu_myfonts');
	if (preview2)
		selectors.push('.finition_texte_genere .apercu_myfonts');
	if (preview3)
		selectors.push('body>.apercu_myfonts');
	var apercus=$(selectors.join(','));
	var images=apercus.find('img');
	if (images.length === 0) {
		apercus.html($('<img>'));
		images=apercus.find('img');
	}
	images.addClass('loading');

	$.each(images,function() {
		var url_appel='/viewer_myfonts/'+"index";
		$.each($(['URL','Couleur_texte','Couleur_fond','Largeur','Chaine','Demi_hauteur']),function(i,nom_option) {
			url_appel+="/"+form_options.valeur(nom_option).val();
		});
		if ($(this).parent().parent().is('body') || $(this).closest('.image_position').length !== 0) // Preview globale donc avec rotation
			url_appel+="/"+form_options.valeur('Rotation').val();
		else
			url_appel+='/0.01';

		url_appel+='/'+dimensions.x;

		$(this)
			.attr({src:url_appel})
			.off('load')
			.load(function() {
				$(this).removeClass('loading').removeClass('cache');
				if ($(this).closest('.finition_texte_genere').length > 0) {
					var section_active_integration=$(this).closest('.ui-accordion-content-active').length > 0;
					if (section_active_integration)
						placer_extension_largeur_preview();
				}
				if (callback !== undefined) {
					callback();
				}
			});
		$(this).error(function() {
			if (!isMyFontsError) {
				jqueryui_alert_from_d($('#wizard-erreur-image-myfonts'), function() {
					isMyFontsError = false;
				});
			}
			isMyFontsError=true;
		});
	});
}

var largeur_physique_preview_initiale=null;

function placer_extension_largeur_preview() {
	var dialogue=$('.wizard.preview_etape.modif').d();
	var image_integration=dialogue.find('.finition_texte_genere img');
	largeur_physique_preview_initiale=image_integration.width();

	dialogue.find('.finition_texte_genere .extension_largeur').removeClass('cache')
			.css({marginLeft:(image_integration.width())+'px',
				  height:image_integration.height()+'px',
				  left:''})
			.draggable({
				axis: 'x',
				stop:function() {
					tester_options_preview(['Largeur']);
					load_myfonts_preview(false,true,false);
				}
			});
}

function wizard_charger_liste_pays() {
	chargement_listes=true;
	var wizard_pays=$('#'+id_wizard_courant+' [name="wizard_pays"]');
	wizard_pays.html($('<option>').text('Chargement...'));

	$.ajax({
		url: '/numerosdispos/'+['index'].join('/'),
		dataType:'json',
		type: 'post',
		success:function(data) {
			wizard_pays.html('');
			$.each(data.pays, function(i, pays) {
				wizard_pays
					.append($('<option>')
						.val(i)
						.html(pays)
					);
			});
			wizard_pays.val(get_option_wizard(id_wizard_courant, 'wizard_pays') || 'fr');

			$('#'+id_wizard_courant+' [name="wizard_pays"]').change(function() {
				wizard_charger_liste_magazines($(this).val());
			});

			wizard_charger_liste_magazines(wizard_pays.val());
		}
	});
}


function wizard_charger_liste_magazines(pays_sel) {
	chargement_listes=true;
	var wizard_magazine=$('#'+id_wizard_courant+' [name="wizard_magazine"]');
	wizard_magazine.html($('<option>').text('Chargement...'));

	pays=pays_sel;

	$.ajax({
		url: '/numerosdispos/'+['index',pays].join('/'),
		type:'post',
		dataType: 'json',
		success:function(data) {
			wizard_magazine.html('');
			$.each(data.magazines, function(i, magazine) {
				wizard_magazine
					.append($('<option>')
						.val(i)
						.html(magazine)
					);
			});
			if (get_option_wizard(id_wizard_courant, 'wizard_magazine') !== undefined)
				wizard_magazine.val(get_option_wizard(id_wizard_courant, 'wizard_magazine'));

			wizard_magazine.change(function() {
				chargement_listes=true;
				wizard_charger_liste_numeros($(this).val());
			});

			wizard_charger_liste_numeros(wizard_magazine.val());
		}
	});
}

function wizard_charger_liste_numeros(publicationcode_sel) {
	chargement_listes=true;
	var wizard_numero=$('#'+id_wizard_courant+' [name="wizard_numero"]');
	wizard_numero.html($('<option>').text('Chargement...'));

	pays = publicationcode_sel.split('/')[0];
	magazine = publicationcode_sel.split('/')[1];

	charger_liste_numeros(pays, magazine,function(data) {
		numeros_dispos=data.numeros_dispos;
		var tranches_pretes=data.tranches_pretes;

		wizard_numero.html('');
		$.each(numeros_dispos, function(numero_dispo) {
			if (numero_dispo !== 'Aucun') {
				var option=$('<option>').val(numero_dispo).html(numero_dispo);
				var est_dispo=tranches_pretes[numero_dispo] !== undefined;
				if (est_dispo) {
					var classe='';
					switch(tranches_pretes[numero_dispo]) {
						case 'par_moi':
							classe = 'cree_par_moi';
						break;
						case 'en_cours':
							classe = 'en_cours';
						break;
						default:
							classe='tranche_prete';
					}
					option
						.addClass(classe)
						.attr({title: classe.replace(/_/g, ' ')});
				}
				wizard_numero.append(option);
			}
		});
		if (get_option_wizard(id_wizard_courant, 'wizard_numero') !== undefined)
			wizard_numero.val(get_option_wizard(id_wizard_courant, 'wizard_numero'));
		chargement_listes=false;
	});
}

function creer_prochain_modele_tranche(tranches_a_creer, i_tranche_a_creer, image_tranches_multiples, id_fichier_image_multiple, pos_image) {

	var tranche = tranches_a_creer[i_tranche_a_creer];
	dimensions = tranche.dimensions;

	creer_modele_tranche(tranche.pays, tranche.magazine, tranche.numero, false, function () {
		var element_pos = tranche.element.position();
		var x1 = element_pos.left - pos_image.left,
			y1 = element_pos.top - pos_image.top;
		var x2 = x1 + tranche.element.width(),
			y2 = y1 + tranche.element.height();

		rogner_image(
			image_tranches_multiples, id_fichier_image_multiple, 'photo_multiple', 'photos',
			tranche.pays, tranche.magazine, tranche.numero,
			x1, x2, y1, y2, null,
			function (nom_fichier_rogne) {
				var match_photo_principale = ('/' + nom_fichier_rogne).match(REGEX_FICHIER_PHOTO);
				if (match_photo_principale) {
					nom_photo_principale = match_photo_principale[1];
					maj_photo_principale();
				}
				else {
					jqueryui_alert('Photo de tranche invalide : ' + nom_fichier_rogne, 'Création de modéle');
				}

				$('#photos_tranches_creees').append(
					$('.photo_tranche_creee.template')
						.clone(true)
						.removeClass('template')
						.text([tranche.pays, tranche.magazine].join('/') + tranche.numero)
				);

				i_tranche_a_creer++;
				if (i_tranche_a_creer < tranches_a_creer.length) {
					creer_prochain_modele_tranche(tranches_a_creer, i_tranche_a_creer, image_tranches_multiples, id_fichier_image_multiple, pos_image);
				}
				else {
					$('#wizard-decouper-photo').removeClass('invisible');
					$('#wizard-confirmation-photo-multiple').find('.fin_chargement, .chargement').toggleClass('cache');
				}
			}
		);
	});
}

function creer_modele_tranche(pays, magazine, numero, with_user, callback) {
	$.ajax({
		url: '/creer_modele_wizard/'+['index',pays,magazine,numero,with_user].join('/'),
		type: 'post',
		dataType: 'json',
		success: function(data) {
			id_modele = data.id_modele;
			if (dimensions.x) {
				// Mise à jour de la fonction Dimensions avec les valeurs entrées
				var parametrage_dimensions =  'Dimension_x='+dimensions.x +'&Dimension_y='+dimensions.y;
				$.ajax({
					url: '/update_wizard/'+['index',-1,parametrage_dimensions,with_user].join('/'),
					type: 'post',
					success: callback
				});
			}
			else {
				callback()
			}
		}
	});
}

function rogner_image(image, nom, source, destination, pays_destination, magazine_destination, numero_destination, x1, x2, y1, y2, numero_image, callback) {
	x1 = parseFloat(100 * x1 / image.width());
	x2 = parseFloat(100 * x2 / image.width());
	y1 = parseFloat(100 * y1 / image.height());
	y2 = parseFloat(100 * y2 / image.height());

	callback = callback || function() {};

	$.ajax({
		url: '/rogner_image/' + ['index', pays_destination, magazine_destination, numero_image || 'null', numero_destination,
											  nom, source, destination, x1, x2, y1, y2].join('/'),
		type: 'post',
		success: callback
	});
}

function charger_liste_numeros(pays_sel,magazine_sel, callback) {
	$.ajax({
		url: '/numerosdispos/'+['index',pays_sel,magazine_sel].join('/'),
		type: 'post',
		dataType: 'json',
		success: callback
	});
}

function get_option_wizard(id_wizard, nom_option) {
	var options_wizard = wizard_options[id_wizard];
	if (options_wizard === undefined || options_wizard === null)
		return undefined;
	return options_wizard[nom_option] || undefined;
}

function set_option_wizard(id_wizard, nom_option, valeur) {
	wizard_options[id_wizard][nom_option] = valeur;
}


function toFloat2Decimals(floatVal) {
	return String(floatVal).replace(/([0-9]+)(\.[0-9]{0,2})?.*/g,'$1$2');
}

function init_action_bar() {
	$.each($('#action_bar img.action'), function() {
		var nom=$(this).attr('name');
		$(this).attr({src:'images/'+nom+'.png'});

		$(this).click(function() {
			var nom=$(this).attr('name');
			switch(nom) {
				case 'home':
					location.replace(base_url);
				break;
				case 'info':
					launch_wizard('wizard-info-conception', {
						modal:true,
						first: true,
						closeable: true
					});
				break;
				case 'dimensions':
					launch_wizard('wizard-dimensions-modifier', {
						modal:true,
						first: true,
						closeable: true
					});
				break;
				case 'photo':
					$('#wizard-images')
						.addClass('photo_principale')
						.removeClass('autres_photos photos_texte');
					launch_wizard('wizard-images', {
						modal:true,
						first: true,
						width: 600
					});
				break;
				case 'corbeille':
					jqueryui_alert_from_d($('#wizard-confirmation-desactivation-modele'), function() {
						$.ajax({
							url: '/desactiver_modele/'+['index'].join('/'),
							type: 'post',
							success:function() {
								location.replace(base_url);
							}
						});
					});
				break;
				case 'clone':
					wizard_goto($('#'+id_wizard_courant), 'wizard-proposition-clonage');
				break;
				case 'valider':
					launch_wizard('wizard-confirmation-validation-modele', {
						modal:true,
						first: true,
						closeable: true
					});
				break;
			}

		});
	});
}

function afficher_photo_tranche(callback) {
	callback = callback || function() {};
	if (nom_photo_principale) {
		var image = $('<img>').height(parseInt($('#Dimension_y').val()) * zoom);
		$('#photo_tranche_courante').html(image);
		var selecteur_depuis_photo = $('#selecteur_couleur #depuis_photo');
		image.attr({src:edges_url+'/'+pays+'/photos/'+nom_photo_principale});
		image.load(function() {
			$(this).css({display:'inline'});
			$('.dialog-preview-etape.finale').width(Math.max(LARGEUR_DIALOG_TRANCHE_FINALE,
													dimensions.x * zoom+$(this).width() + 14));
			jqueryui_clear_message('aucune-image-de-tranche');
			selecteur_depuis_photo.find('[name="description_selection_couleur"]').toggle(true);
			selecteur_depuis_photo.find('[name="pas_de_photo_tranche"]').toggle(false);
			callback();
		});
		image.error(function() {
			$(this).css({display:'none'});
			selecteur_depuis_photo.find('[name="description_selection_couleur"]').toggle(false);
			selecteur_depuis_photo.find('[name="pas_de_photo_tranche"]').toggle(true);
			callback();
		});
	}
	else {
		$.ajax({
			url: '/photo_principale/'+['index'].join('/'),
			type: 'post',
			success:function(nom_photo) {
				if (nom_photo && nom_photo !== 'null') {
					nom_photo_principale = nom_photo;
					afficher_photo_tranche(callback);
				}
				else {
					callback();
				}
			}
		});
	}
}

function maj_photo_principale() {
	if (nom_photo_principale === null) {
		return;
	}
	$.ajax({
		url: '/update_photo/'+['index', nom_photo_principale].join('/'),
		type: 'post',
		success:function() {
			afficher_photo_tranche();
		}
	});
}

function lister_images_gallerie(type_images) {
	$.ajax({
		url: '/listerg/'+['index',type_images,pays,magazine].join('/'),
		dataType:'json',
		type: 'post',
		success: function(data) {
			afficher_galerie(type_images, data);
		}
	});
}

function afficher_galerie(type_images, data, container) {
	if (data.erreur) {
		jqueryui_alert('Le répertoire d\'images '+data.erreur+' n\'existe pas',
					   'Erreur interne');
	}
	else {
		container = container || $('#wizard-images form .selectionner_photo');
		var ul=container.find('ul.gallery');
		ul.find('li:not(.template)').remove();
		if (data.length === 0) {
			container.find('.pas_d_image').removeClass('cache');
		}
		else {
			var sous_repertoire = null;
			switch(type_images) {
				case 'Source':
					sous_repertoire = 'elements';
				break;
				case 'Photos':
					sous_repertoire = 'photos';
				break;
			}
			container.find('.pas_d_image').addClass('cache');
			container.find('ul.gallery li:not(.template) img').remove();
			$.each(data, function(i, nom_fichier) {
				var li=ul.find('li.template').clone(true).removeClass('template');
				li.find('em').html(nom_fichier.replace(/[^\.]+\./g,''));
				li.find('img').prop({src:edges_url+'/'+pays+'/'+sous_repertoire+'/'+nom_fichier,
									 title:nom_fichier});
				li.find('input').val(nom_fichier);
				li.find('.filename').text(nom_fichier).attr({title: nom_fichier});
				ul.append(li);
			});
			container.find('ul.gallery li img').removeClass('selected')
										  .unbind('click')
										  .click(function() {
				var selected = !$(this).hasClass('selected');

				container.find('ul.gallery li img').removeClass('selected');
				container.find('#to-wizard-resize').toggleClass('cache', !selected);

				if (selected) {
					$(this).addClass('selected');
					$('#wizard-images [name="selected"]').val($(this).attr('src'));

					var destination_rognage = container.attr('name') === 'section_photo' ? 'elements' : 'photos';
					$('#wizard-resize [name="destination"]').val(destination_rognage);
					$('#wizard-resize img').attr({src:$(this).attr('src')});
				}
			});
			if (type_images === 'Photos' && nom_photo_principale !== null) {
				container.find('ul.gallery li img[src$="'+nom_photo_principale+'"]').click();
			}
		}
		ul.removeClass('cache');
		container.find('.chargement_images').addClass('cache');
	}
}

function surveiller_selection_jrac($viewport) {
	var crop_inconsistent_element=$(this).d().find('.error.crop_inconsistent');
	if ($viewport.observator.crop_consistent())
		crop_inconsistent_element.addClass('cache');
	else
		crop_inconsistent_element.removeClass('cache');
}

function templatedToVal(templatedString) {
	$.each(TEMPLATES,function(nom, regex) {
		var matches = (templatedString+'').match(regex);
		if (matches !== null) {
			templatedString+='';
			switch(nom) {
				case 'numero':
					templatedString=templatedString.replace(regex, numero);
				break;
				case 'numero[]':
					var spl=(numero+'').split('');
					for (var i=0;i<matches.length;i++) {
						var caractere=matches[i].replace(regex,'$1');
						if (!isNaN(caractere) && parseInt(caractere) >= 0 && parseInt(caractere) < spl.length)
							templatedString=templatedString.replace(matches[i],spl[parseInt(caractere)]);
					}
				break;
				case 'largeur':
				case 'hauteur':
					var axe = nom === 'largeur' ? 'x' : 'y';
					if (matches[2] || matches[3]) {
						var operation = matches[2] || matches[3];
						var autre_nombre= matches[1] || matches[4];
						switch(operation) {
							case '*':
								templatedString= dimensions[axe]*autre_nombre;
							break;
						}
					}
					else
						templatedString=templatedString.replace(regex, dimensions[axe]);
				break;
				case 'caracteres_speciaux':
					templatedString=templatedString.replace(/°/,'?');
				break;

			}
		}
	});
	return templatedString;
}

function verifier_peut_creer_numero_selectionne(wizard) {
	return ! wizard.find('[name="wizard_numero"]').find('option:selected').is('.tranche_prete,.cree_par_moi,.en_cours');
}

function verifier_peut_modifier_numero_selectionne(wizard) {
	return ! wizard.find('[name="wizard_numero"]').find('option:selected').is('.en_cours');
}

function afficher_erreur_image_inexistante() {
	var src = $(this).attr('src');
	var nom_image = src.substring(src.lastIndexOf('/') + 1, src.length);
	jqueryui_alert('L\'image ' + nom_image + ' n\'existe pas');
}

function hex2rgb(hex) {
	if (hex.length !== 6){
		return [0,0,0];
	}
	var rgb=[];
	for (var i=0;i<3;i++){
		rgb[i] = parseInt((hex.substring(2*i,2*(i+1))+'').replace(/[^a-f0-9]/gi, ''),16);
	}
	return rgb;
}

function rgb2hex(r, g, b) {
	var hex = "";
	var rgb = [r, g, b];
	for (var i = 0; i < 3; i++) {
		var tmp = parseInt(rgb[i], 10).toString(16);
		if (tmp.length < 2)
			hex += "0" + tmp;
		else
			hex += tmp;
	}
	return hex.toUpperCase();
}
