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

var selecteur_cellules_preview = null;

var etapes_valides = [];

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

function jquery_connexion() {
	$("#wizard-login-form").dialog({
		width: 500,
		modal: false,
		open: function() {
			$("#login-form").keypress(function(e) {
				if (e.keyCode === $.ui.keyCode.ENTER) {
					$('#login-form').submit();
				}
			});
		},
		buttons: [{
			text: "Connexion",
			type: "submit",
			form: "login-form",
			click: function() {
				$('#login-form').submit();
			}
		}]
	});

	$('#login-form').submit(function () {
		$.ajax({
			url: base_url + 'index.php/edgecreatorg/login',
			type: 'post',
			data: 'user=' + $('#username').val() + '&pass=' + $('#password').val() + "&mode_expert=" + $('#mode_expert').prop('checked'),
			success: function (data) {
				if (data.indexOf("Erreur") === 0)
					$("#wizard-login-form").find('.erreurs').html(data);
				else {
					location.replace(base_url);
				}
			}
		});
		return false;
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
