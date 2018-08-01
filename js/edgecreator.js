jQuery.fn.getElementsWithData = function (key, val) {
	var data = [];
	this.each(function (i, element) {
		if (typeof(val) == 'undefined' || $(element).data(key) == val)
			data.push($(element)[0]);
	});
	return $(data);
};

jQuery.fn.getData = function (key) {
	var data = [];
	this.each(function (i, element) {
		data.push($(element).data(key));
	});
	return data;
};

var pays;
var magazine;
var numero;

var zoom = 2;
var numeros_dispos;

var chargements = [];
var chargement_courant;

function reload_numero(numero, est_externe, visu, callback) {
	est_externe = est_externe || false;
	visu = visu === undefined ? true : visu;
	callback = callback || function () {};

	chargements = [];
	chargements.push(numero);
	chargement_courant = 0;
	charger_previews_numeros(chargements[chargement_courant], visu, est_externe, callback);
}

function charger_previews_numeros(numero, est_visu, est_externe, callback) {
	var parametrage = {};
	var zoom_utilise = est_visu ? zoom : 1.5;
	callback = callback || function () {};

	$('#chargement').html('Chargement de la preview de la tranche');
	charger_image('numero', urls.viewer_wizard + ['index', 0, pays, magazine, numero, zoom_utilise, 'all', URLEncode(JSON.stringify(parametrage)), (est_visu ? 'false' : 'save'), 'false', est_externe].join('/'), numero, callback);
}

function charger_preview_etape(etapes_preview, est_visu, parametrage, callback) {
	if (parametrage == undefined)
		parametrage = '_';
	var zoom_utilise = est_visu ? zoom : 1.5;
	if (etapes_preview === '')
		etapes_preview = -1;
	var fond_noir = 'false';
	if ((typeof(etapes_preview) == 'string' && etapes_preview.indexOf(',') == -1)
		|| typeof(etapes_preview) == 'number') {
		$('#chargement').html('Chargement de la preview de l\'&eacute;tape ' + etapes_preview);
		fond_noir = ($('#fond_noir_' + etapes_preview)
			&& $('#fond_noir_' + etapes_preview).hasClass('fond_noir_active')) ? 'true' : 'false';
		etapes_preview = [etapes_preview];
	}
	else {
		$('#chargement').html('Chargement de la preview de la tranche');
		if (typeof(etapes_preview) == 'string')
			etapes_preview = etapes_preview.split(/,/g);
	}
	charger_image('etape', urls.viewer_wizard + ['etape', zoom_utilise, etapes_preview.join("-"), parametrage, (est_visu ? 'false' : 'save'), fond_noir, 'false'].join('/'), etapes_preview.join("-"), callback);
}

var selecteur_cellules_preview = null;

function charger_image(type_chargement, src, num, callback) {
	callback = callback || function () {};
	var est_visu = src.indexOf('/save') === -1;
	var est_etape_ouverte = modification_etape && modification_etape.data('etape') == num;

	var image = $('<img>')
		.addClass('image_preview' + (est_etape_ouverte ? ' cache' : ''))
		.toggleClass('save', !est_visu)
		.data(type_chargement, num);
	src += '/' + Math.random();

	if (!est_visu) {
		switch (privilege) {
			case 'Admin':
				break;
			case 'Edition':
				break;
			default:
				jqueryui_alert('Vous ne poss&eacute;dez pas les droits n&eacute;cessaires pour cette action');
				return;
				break;
		}
	}
	if (type_chargement == 'etape' && num !== null) {
		var etapes_corresp = $(selecteur_cellules_preview).getElementsWithData('etape', num);
		if (etapes_corresp.length == 0) {// Numéro d'étape non trouvé
			jqueryui_alert("Num&eacute;ro d'&eacute;tape non trouv&eacute; lors du chargement de la preview : " + num, "Erreur");
			charger_image_suivante(null, callback, type_chargement, est_visu);
		}
		else {
			etapes_corresp.html(image);
		}
	}
	else {
		$(selecteur_cellules_preview).getElementsWithData('numero', num).html(image);
	}
	image.load(function () {
		if (!est_visu && chargement_courant >= chargements.length) {
			switch (privilege) {
				case 'Edition':
					if (type_chargement === 'etape')
						jqueryui_alert('Votre proposition de mod&egrave;le a ete envoy&eacute;e au webmaster pour validation. Merci !');
					else
						jqueryui_alert('Vos propositions de mod&egrave;les ont ete envoy&eacute;es au webmaster pour validation. Merci !');
					break;
			}
		}
		charger_image_suivante($(this), callback, type_chargement, est_visu);
		callback(image);
	});

	image.error(function () {
		var num_etape = chargements[chargement_courant];
		if (num_etape != 'all') { // Si erreur sur l'étape finale c'est qu'il y a eu erreur sur une étape intermédiaire ; on ne l'affiche pas de nouveau
			$('#wizard-erreur-generation-image').find('[name="etape"]').text(num_etape);
			$('#wizard-erreur-generation-image').find('iframe').attr({src: $(this).attr('src') + '/debug'});
			jqueryui_alert_from_d($('#wizard-erreur-generation-image'));
		}
	});
	image.attr({'src': src});
}

function charger_image_suivante(image, callback, type_chargement, est_visu) {
	chargement_courant++;

	if ($(selecteur_cellules_preview).length === 2 && chargement_courant === 1)
		$(selecteur_cellules_preview).last().html(image.clone(false));

	$('#chargement').html('');
	$('#erreurs').html('');
	if (chargement_courant < chargements.length) {
		if (type_chargement === 'etape')
			charger_preview_etape(chargements[chargement_courant], est_visu, undefined, callback);
		else
			charger_previews_numeros(chargements[chargement_courant], est_visu);
	}
	else {
		chargement_courant = 0;
		chargements = [];
		if (type_chargement == 'numero')
			$('#numero_preview_debut').data('numero', null);
	}
}

var types_options = [];
types_options['Actif'] = 'actif';

var etapes_valides = [];

var nb_lignes = null;

var image_ajouter = $('<img>', {
	title: 'Ajouter une etape',
	src: base_url + 'images/ajouter.png'
})
	.addClass('ajouter_etape');
var image_supprimer = $('<img>', {
	title: 'Supprimer l\'etape',
	src: base_url + 'images/supprimer.png'
})
	.addClass('supprimer_etape');

var num_etape_avant_nouvelle = null;

function charger_etape_ligne(etape, tr, est_nouvelle) {
	est_nouvelle = typeof(est_nouvelle) != 'undefined';
	var est_ligne_header = tr.children('th').length > 0;
	var balise_cellule = est_ligne_header ? 'th' : 'td';
	var num_etape = etape.Ordre;
	var cellule = null;
	if (num_etape == -1) { // cellule deja existante
		cellule = tr.children(balise_cellule + ':nth-child(' + 3 + ')');
	}
	else {
		var num_etape_precedente = parseInt(num_etape - .5);
		cellule = $('<' + balise_cellule + '>');
		if (num_etape != parseInt(num_etape)) {// Nouvelle etape
			tr.children(balise_cellule + ':nth-child(' + ($('[name="entete_etape_' + num_etape_precedente + '"]').first().prevAll().length + 1) + ')')
				.after(cellule);
		}
		else
			tr.append(cellule);
	}
	cellule.data('etape', num_etape);
	switch (tr.prevAll().length) {
		case 0:
		case nb_lignes - 1:// Ligne des etapes
			var nom_fonction = etape.Nom_fonction;

			if (privilege != 'Affichage')
				cellule.html(image_supprimer.clone(true));

			cellule.addClass('lien_etape' + (est_nouvelle ? ' nouvelle' : ''))
				.attr({'name': 'entete_etape_' + num_etape})
				.data('etape', num_etape)
				.append($('<span>').addClass('numero_etape')
					.attr({'title': 'Cliquez pour developper l\'etape ' + num_etape})
					.html(num_etape == -1
						? 'Dimensions'
						: (est_nouvelle ? 'Nouvelle &eacute;tape' : 'Etape ' + num_etape)))
				.append($('<br>'))
				.append($('<img>', {
					'height': 18, 'src': base_url + 'images/fonctions/' + nom_fonction + '.png',
					'title': nom_fonction, 'alt': nom_fonction
				}).addClass('logo_option'));

			if (privilege != 'Affichage')
				cellule.append(image_ajouter.clone(true));

			break;
		case 1:
		case nb_lignes - 2 :// Ligne des options, vide
			cellule.addClass('etape_active')
				.append($('<a>', {'href': 'javascript:void(0)'}));
			break;
		default:
			if (est_dans_intervalle(tr.data('numero'), etape.Numero_debut + '~' + etape.Numero_fin))
				cellule.addClass('num_checked');
			break;
	}
}

function charger_helper(nom_helper, nom_div, nom_fonction) {
	if (nom_fonction != null)
		$('#liste_possibilites_fonctions').prop('selectedIndex', $('#liste_possibilites_fonctions [title="' + nom_fonction + '"]').index());

	if (!$(nom_div))
		$('#helpers').append($('<div>', {'id': nom_div}));

	$.ajax({
		url: base_url + 'index.php/helper/index/' + nom_helper + '.html',
		type: 'post',
		data: 'nom_helper=' + nom_helper,
		failure: function () {
			jqueryui_alert('Page de helper introuvable : ' + nom_helper + '.html');
		},
		success: function (data) {
			var texte = data;
			var suivant_existe = texte.indexOf('...') != -1;
			var nom_fonction_fin = texte.match(/!([^!]+)!/g);
			var est_dernier = nom_fonction_fin != null;
			texte = texte.replace(/\\.\\.\\./g, '')
				.replace(/!([^!]+)!/g, '');
			var numero_helper = nom_helper.substring(nom_helper.length - 1, nom_helper.length);
			if (numero_helper > 1) {
				var lien_precedent = $('<a>');
				$(nom_div).html('')
					.append($('<br>'))
					.append(lien_precedent);
				lien_precedent.click(function () {
					var nom_helper_suivant = nom_helper.substring(0, nom_helper.length - 1) + (parseInt(numero_helper) - 1);
					charger_helper(nom_helper_suivant, nom_div, false, nom_fonction);
				});
			}
			else
				$(nom_div).html('');

			$(nom_div).append(texte)
				.data('numero_helper', numero_helper);
			if (suivant_existe) {
				var lien_suivant = $('<a>').html('Suivant &gt;&gt;');
				$(nom_div).append(lien_suivant);
				lien_suivant.click(function () {
					var nom_helper_suivant = nom_helper.substring(0, nom_helper.length - 1) + (parseInt(numero_helper) + 1);
					charger_helper(nom_helper_suivant, nom_div, nom_fonction);
				});
			}
			if (est_dernier) {
				var nouvelle_etape = {};
				nouvelle_etape['Nom_fonction'] = nom_fonction_fin[0].replace(/!/g, '');
				nouvelle_etape['Numero_debut'] = '';
				nouvelle_etape['Numero_fin'] = '';
				nouvelle_etape['Ordre'] = parseInt(num_etape_avant_nouvelle) + .5;
				$.each($('#table_numeros').find('tr'), function (i, tr) {
					charger_etape_ligne(nouvelle_etape, $(tr), true);
				});
			}
		}
	});
}

function jqueryui_alert_from_d(element, close_callback) {
	close_callback = close_callback || function () {
	};
	jqueryui_alert(element.children(), element.attr("title"), close_callback);
}

function jqueryui_alert(texte, titre, close_callback) {
	titre = titre || 'DucksManager EdgeCreator';
	close_callback = close_callback || function () {
	};
	var boite = $('<div>', {'title': titre});
	if (typeof(texte) == 'string')
		boite.append($('<p>').html(texte));
	else
		boite.append(texte);
	$('#body').append(boite);
	boite.dialog({
		width: 350,
		modal: true,
		buttons: {
			OK: function () {
				$(this).dialog("close");
			}
		},
		close: close_callback
	});
}

function logout() {
	$.ajax({
		url: base_url + 'index.php/edgecreatorg/logout',
		type: 'post',
		success: function () {
			location.replace(base_url);
		}
	});
}

function URLEncode(clearString) {
	var output = '';
	var x = 0;
	clearString = clearString.toString();
	var regex = /(^[a-zA-Z0-9_.]*)/;
	while (x < clearString.length) {
		var match = regex.exec(clearString.substr(x));
		if (match != null && match.length > 1 && match[1] != '') {
			output += match[1];
			x += match[1].length;
		} else {
			if (clearString[x] == ' ')
				output += '+';
			else {
				var charCode = clearString.charCodeAt(x);
				var hexVal = charCode.toString(16);
				output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
			}
			x++;
		}
	}
	return output;
}
