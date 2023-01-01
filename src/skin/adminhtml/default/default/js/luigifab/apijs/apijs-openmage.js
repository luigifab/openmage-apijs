/**
 * Created D/15/12/2013
 * Updated J/11/08/2022
 *
 * Copyright 2008-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-apijs
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

var apijsOpenMage = new (function () {

	"use strict";

	this.init = function () {

		var d = apijs.i18n.data;
		if (!d.frca) d.frca = {};
		// https://docs.google.com/spreadsheets/d/1UUpKZ-YAAlcfvGHYwt6aUM9io390j0-fIL0vMRh1pW0/edit?usp=sharing
		// auto start
		d.cs[250] = "Smazat soubor";
		d.cs[251] = "Opravdu chcete tento soubor smazat?[br]Pozor, tuto operaci nelze vrátit zpět.";
		d.cs[252] = "Chyba";
		d.cs[259] = "Vymazat všechny soubory";
		d.cs[260] = "Opravdu chcete smazat všechny soubory?[br]Pozor, tuto operaci nelze vrátit zpět.";
		d.cs[261] = "Potvrďte zaškrtnutím políčka:";
		d.de[250] = "Eine Datei löschen";
		d.de[251] = "Sind Sie sicher, dass Sie diese Datei löschen möchten?[br]Achtung, diese Aktion ist unrückgängig.";
		d.de[252] = "Fehler";
		d.de[253] = "Sie verfügen nicht über die notwendigen Rechte um diese Operation durchzuführen, bitte [a §]aktualisieren Sie die Seite[/a].";
		d.de[254] = "Es tut uns leid, diese Datei existiert nicht mehr, bitte [a §]aktualisieren Sie die Seite[/a].";
		d.de[259] = "Alle Daten löschen";
		d.de[261] = "Zur Bestätigung das Kontrollkästchen bestätigen:";
		d.el[252] = "Σφάλμα";
		d.en[250] = "Remove file";
		d.en[251] = "Are you sure you want to remove this file?[br]Be careful, you can't cancel this operation.";
		d.en[252] = "Error";
		d.en[253] = "You are not authorized to perform this operation, please [a §]refresh the page[/a].";
		d.en[254] = "Sorry, the file no longer exists, please [a §]refresh the page[/a].";
		d.en[255] = "Clear cache";
		d.en[256] = "Are you sure you want to clear the cache?[br]Be careful, you can't cancel this operation.";
		d.en[257] = "Rename file";
		d.en[258] = "Enter below the new name for the file.";
		d.en[259] = "Remove all files";
		d.en[260] = "Are you sure you want to remove all files?[br]Be careful, you can't cancel this operation.";
		d.en[261] = "To confirm, check the checkbox:";
		d.es[250] = "Borrar un archivo";
		d.es[251] = "¿Está usted seguro(a) de que desea eliminar este archivo?[br]Atención, pues no podrá cancelar esta operación.";
		d.es[253] = "No está autorizado-a para llevar a cabo esta operación, por favor [a §]actualice la página[/a].";
		d.es[254] = "Disculpe, pero el archivo ya no existe, por favor [a §]actualice la página[/a].";
		d.es[255] = "Vaciar la caché";
		d.es[256] = "¿Está usted seguro(a) de querer vaciar la caché?[br]Cuidado, esta operación no puede ser cancelada.";
		d.frca[251] = "Êtes-vous sûr(e) de vouloir supprimer ce fichier?[br]Attention, cette opération n'est pas annulable.";
		d.frca[256] = "Êtes-vous certain(e) de vouloir vider le cache?[br]Attention, cette opération n'est pas annulable.";
		d.frca[260] = "Êtes-vous sûr(e) de vouloir supprimer tous les fichiers?[br]Attention, cette opération n'est pas annulable.";
		d.fr[250] = "Supprimer le fichier";
		d.fr[251] = "Êtes-vous sûr(e) de vouloir supprimer ce fichier ?[br]Attention, cette opération n'est pas annulable.";
		d.fr[252] = "Erreur";
		d.fr[253] = "Vous n'êtes pas autorisé(e) à effectuer cette opération, veuillez [a §]actualiser la page[/a].";
		d.fr[254] = "Désolé, le fichier n'existe plus, veuillez [a §]actualiser la page[/a].";
		d.fr[255] = "Vider le cache";
		d.fr[256] = "Êtes-vous certain(e) de vouloir vider le cache ?[br]Attention, cette opération n'est pas annulable.";
		d.fr[257] = "Renommer le fichier";
		d.fr[258] = "Saisissez ci-dessous le nouveau nom pour ce fichier.";
		d.fr[259] = "Supprimer tous les fichiers";
		d.fr[260] = "Êtes-vous sûr(e) de vouloir supprimer tous les fichiers ?[br]Attention, cette opération n'est pas annulable.";
		d.fr[261] = "Pour confirmer, cochez la case :";
		d.hu[252] = "Hiba";
		d.it[250] = "Cancella i file";
		d.it[251] = "Sei sicura di voler eliminare il file?[br]Attenzione, questa operazione non può essere annullata.";
		d.it[252] = "Errore";
		d.it[253] = "Non siete autorizzati a eseguire questa operazione, vi preghiamo di [a §]ricaricare la pagina[/a].";
		d.it[254] = "Spiacenti, il file non esiste più, vi preghiamo di [a §]ricaricare la pagina[/a].";
		d.it[259] = "Cancella tutti i file";
		d.it[260] = "Sei sicura di voler cancellare tutti i file?[br]Attenzione, questa operazione non può essere annullata.";
		d.it[261] = "Per confermare, seleziona la casella:";
		d.ja[250] = "ファイルを削除";
		d.ja[252] = "エラー";
		d.nl[252] = "Fout";
		d.pl[250] = "Usuń plik";
		d.pl[251] = "Jesteś pewny, że chcesz usunąć ten plik?[br]Uwaga! Nie ma odwrotu od tej operacji.";
		d.pl[252] = "Błąd";
		d.pl[259] = "Usuń wszystkie pliki";
		d.pl[260] = "Jesteś pewny, że chcesz usunąć ten plik?[br]Uwaga! Nie ma odwrotu od tej operacji.";
		d.pl[261] = "Potwierdź twój wybór:";
		d.pt[250] = "Suprimir um ficheiro";
		d.pt[251] = "Tem certeza de que quer suprimir este ficheiro?[br]Cuidado, não pode cancelar esta operação.";
		d.pt[252] = "Erro";
		d.pt[253] = "Não é autorizado(a) para efetuar esta operação, por favor [a §]atualize a página[/a].";
		d.pt[254] = "Lamento, o ficheiro já não existe, por favor [a §]atualize a página[/a].";
		d.ro[252] = "Eroare";
		d.ru[250] = "Удалить файл";
		d.ru[251] = "Вы уверены, что хотите удалить этот файл?[br]Осторожно, вы не сможете отменить эту операцию.";
		d.ru[252] = "Ошибка";
		d.ru[253] = "Вы не авторизованы для выполнения этой операции, пожалуйста [a §]обновите страницу[/a].";
		d.ru[254] = "Извините, но файл не существует, пожалуйста [a §]обновите страницу[/a].";
		d.sk[252] = "Chyba";
		d.tr[252] = "Hata";
		d.tr[255] = "Önbelleği temizle";
		d.uk[252] = "Помилка";
		d.zh[252] = "错误信息";
		// auto end
	};

	this.error = function (data) {

		if ((typeof data == 'string') && (data.indexOf('<!DOCTYPE') < 0)) {
			if (apijs.dialog.has('upload'))
				apijs.upload.onError(false, data);
			else
				apijs.dialog.dialogInformation(apijs.i18n.translate(252), data, 'error');
		}
		else {
			apijs.dialog.remove('lock'); // obligatoire sinon demande de confirmation de quitter la page
			self.location.reload();
		}
	};

	this.sendFiles = function (title, action, onemax, allmax, exts, extra) {

		apijs.upload.sendFiles(title, action, 'myimage', onemax, allmax, exts, apijsOpenMage.updateForm);

		if (typeof extra == 'string') {
			var elem = document.createElement('p');
			elem.innerHTML = '<label><input type="checkbox" name="exclude" /> ' + extra + '</label>';
			apijs.dialog.t2.appendChild(elem);
		}
	};

	this.updateForm = function (data, indicator, elem) {

		// success-{json[result, bbcode]}
		if (data.indexOf('{') > -1) {
			data = JSON.parse(data.slice(data.indexOf('{')));
			if (data.bbcode)
				apijsOpenMage.error(data.bbcode);
		}

		// produit ou widget cms
		if (elem = document.getElementById('apijsGallery')) {

			if (data.filter = document.getElementById('apijsFilter'))
				data.filter = parseInt(data.filter.value, 10);

			elem.parentNode.innerHTML = data.html;
			elem.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (elem) {
				elem.checked = elem.hasAttribute('checked');
				if (elem.hasAttribute('onchange'))
					apijsOpenMage.checkVal(elem);
			});

			apijs.slideshow.init();
			if (!apijs.dialog.has('error')) // ferme sauf en cas d'erreur
				apijs.dialog.actionClose();

			if (data.filter > 0) {
				elem = document.getElementById('apijsFilter');
				elem.value = data.filter;
				elem.dispatchEvent(new Event('change'));
			}

			if (indicator) {
				indicator.classList.remove('changed');
				varienWindowOnload(true);
			}
		}
		else {
			MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
		}
	};

	// product
	this.actionSave = function (action) {

		document.querySelector('body').classList.add('fabload');

		var xhr = new XMLHttpRequest(), indicator = document.querySelector('.tab-item-link.active');
		xhr.open('POST', action + '?isAjax=true', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		xhr.onreadystatechange = function () {

			if (xhr.readyState === 4) {
				if ([0, 200].has(xhr.status)) {
					if (xhr.responseText.indexOf('success-') === 0)
						apijsOpenMage.updateForm(xhr.responseText, indicator);
					else
						apijsOpenMage.error(xhr.responseText);
				}
				else {
					apijsOpenMage.error(xhr.status);
				}
				document.querySelector('body').classList.remove('fabload');
			}
		};

		xhr.send(apijs.serialize(document.getElementById('product_edit_form'), 'apijs'));
	};

	this.removeAttachment = function (action) {
		apijs.dialog.dialogConfirmation(apijs.i18n.translate(250), apijs.i18n.translate(251), apijsOpenMage.actionRemoveAttachment, action);
	};

	this.removeAllAttachments = function (action) {
		apijs.dialog.dialogFormOptions(apijs.i18n.translate(259), apijs.i18n.translate(260) + '[p][label]' + apijs.i18n.translate(261) + ' [input type="checkbox"][/label]', action, apijsOpenMage.actionRemoveAllAttachments);
	};

	this.actionRemoveAttachment = function (args) {

		// args = action
		var xhr = new XMLHttpRequest(), indicator = document.querySelector('.tab-item-link.active');
		xhr.open('GET', args, true);

		xhr.onreadystatechange = function () {

			if (xhr.readyState === 4) {
				if ([0, 200].has(xhr.status)) {
					if (xhr.responseText.indexOf('success-') === 0)
						apijsOpenMage.updateForm(xhr.responseText, indicator);
					else
						apijsOpenMage.error(xhr.responseText);
				}
				else {
					apijsOpenMage.error(xhr.status);
				}
			}
		};

		xhr.send();
	};

	this.actionRemoveAllAttachments = function (action) {

		if (typeof action == 'boolean')
			return apijs.html('input').checked;

		apijsOpenMage.actionRemoveAttachment(action);
	};

	this.checkVal = function (root) {

		var elem = root;
		while ((elem.nodeName !== 'BODY') && !elem.querySelector('.val'))
			elem = elem.parentNode;

		if (elem = elem.querySelector('.val')) {
			if (root.checked)
				elem.setAttribute('disabled', 'disabled');
			else
				elem.removeAttribute('disabled');
		}
	};

	this.filter = function (root) {

		var word, text, show;
		if (typeof root == 'string') {
			show = root;
			root = document.getElementById('apijsFilter');
			if (((root.value === show) && (show !== 'all')) || ((root.value === 'all') && (show === 'all')))
				show = 'none';
			root.value = show;
		}
		if (root.nodeName === 'BUTTON') {
			text = root.getAttribute('data-text');
			root.setAttribute('data-text', root.textContent);
			root.textContent = text;
			root.setAttribute('data-state', (root.getAttribute('data-state') == '0') ? '1' : '0');
		}

		document.getElementById('apijsGallery').querySelectorAll('tbody tr[id]').forEach(function (line) {

			show = [];

			// pour chaque colonne (car toutes les colonnes peuvent avoir un filtre)
			// word = ce qu'on cherche dans la colonne courante
			// text = ce qu'il y a dans la cellule de la colonne de la ligne courante
			document.getElementById('apijsGallery').querySelectorAll('tr.filter th').forEach(function (col, idx) {

				col = col.querySelector('.filter');
				if (!col) {
					show.push(true);
				}
				else if (col.nodeName === 'SELECT') {
					word = Math.floor(parseInt(col.value, 10) / 100); // ce qu'on cherche
					text = Math.floor(parseInt(line.querySelectorAll('td')[idx].querySelector('input.position').value, 10) / 100); //dans quoi
					show.push((col.value === 'all') || (text == word));
				}
				else if (col.nodeName === 'BUTTON') {
					word = col.getAttribute('data-state') == '1'; // ce qu'on cherche
					text = line.querySelectorAll('td')[idx].querySelector('input.check:not(.def)').checked; // dans quoi on cherche
					if (col.hasAttribute('data-reverse')) text = !text;
					show.push(!word || (word && (word !== text)));
				}
			});

			// maintenant que chaque colonne de la ligne a été vérifiée
			// si aucune colonne indique qu'il ne faut pas afficher la ligne, on affiche la ligne
			line.setAttribute('style', (show.indexOf(false) > -1) ? 'display:none;' : '');
			line.removeAttribute('title');
		});

		// s'assure que le séparateur est visible
		if ((root.nodeName === 'SELECT') && !isNaN(root.value) && (root = document.querySelector('tr.grp' + root.value))) {
			var rect = root.getBoundingClientRect();
			if ((rect.top < 0) && (rect.bottom <= window.innerHeight))
				root.scrollIntoView();
		}
	};

	// wysiwyg
	this.renameMedia = function (elem) {

		elem = elem.parentNode.parentNode;
		elem.click();
		if (!elem.classList.contains('selected'))
			elem.click();

		var name = elem.querySelector('.filename').textContent.trim(),
		    text = '[p][label for="apijsinput"]' + apijs.i18n.translate(258) + '[/label][/p]' +
				'[input type="text" name="name" value="' + name + '" spellcheck="false" id="apijsinput"]';

		apijs.dialog.dialogFormOptions(apijs.i18n.translate(257), text, 'action.php', apijsOpenMage.actionRenameMedia, elem.id, 'editname');
		apijs.dialog.t1.querySelector('input').select();
	};

	this.actionRenameMedia = function (action, args) {

		// vérification de la nouvelle description
		if (typeof action == 'boolean') {
			return true;
		}
		// sauvegarde du nouveau nom
		else if (typeof action == 'string') {

			// args = id
			// copie de MediabrowserInstance.deleteFiles(); ou presque
			var xhr = new XMLHttpRequest();
			xhr.open('POST', MediabrowserInstance.renameFileUrl + '?isAjax=true', true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

			xhr.onreadystatechange = function () {

				if (xhr.readyState === 4) {
					if ([0, 200].has(xhr.status)) {
						try {
							MediabrowserInstance.onAjaxSuccess(xhr);
							MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
						}
						catch (e) {
							apijsOpenMage.error(e.message);
						}
					}
				}
			};

			xhr.send('form_key=' + FORM_KEY + '&file=' + encodeURIComponent(args) + '&name=' + encodeURIComponent(document.getElementById('apijsinput').value));
		}
	};

	this.removeMedia = function (elem) {

		elem = elem.parentNode.parentNode;
		elem.click();
		if (!elem.classList.contains('selected'))
			elem.click();

		apijs.dialog.dialogConfirmation(apijs.i18n.translate(250), apijs.i18n.translate(251), apijsOpenMage.actionRemoveMedia, elem.id);
	};

	this.actionRemoveMedia = function (args) {

		// args = id
		// copie de MediabrowserInstance.deleteFiles(); ou presque
		var xhr = new XMLHttpRequest();
		xhr.open('POST', MediabrowserInstance.deleteFilesUrl + '?isAjax=true', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		xhr.onreadystatechange = function () {

			if (xhr.readyState === 4) {
				if ([0, 200].has(xhr.status)) {
					try {
						MediabrowserInstance.onAjaxSuccess(xhr);
						MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
					}
					catch (e) {
						apijsOpenMage.error(e.message);
					}
				}
			}
		};

		xhr.send('form_key=' + FORM_KEY + '&file=' + encodeURIComponent(args));
	};

	this.overloadMediabrowser = function () {

		var elem, objs = [];
		if (typeof Mediabrowser == 'function')
			objs.push(Mediabrowser);
		if (typeof MediabrowserInstance == 'object')
			objs.push(MediabrowserInstance);

		objs.forEach(function (obj) {

			if (typeof obj.origHideElement != 'function') {

				obj.origHideElement = obj.hideElement;
				obj.hideElement = function (name) {
					this.origHideElement(name);
					if (name === 'loading-mask') {
						document.getElementById('loading-mask').classList.add('no-display');
						document.getElementById('contents').classList.remove('no-display');
						document.getElementById('contents-loader').classList.add('no-display');
						if (elem = document.querySelector('.main-col-inner .form-buttons'))
							elem.removeAttribute('style');
						if (elem = document.querySelector('.content-header-floating .form-buttons'))
							elem.removeAttribute('style');
						if (!apijs.dialog.has('error')) // ferme sauf en cas d'erreur
							apijs.dialog.actionClose();
					}
				};

				obj.origShowElement = obj.showElement;
				obj.showElement = function (name) {
					this.origShowElement(name);
					if (name === 'loading-mask') {
						document.getElementById('loading-mask').classList.add('no-display');
						document.getElementById('contents').classList.add('no-display');
						document.getElementById('contents-loader').classList.remove('no-display');
						if (elem = document.querySelector('.main-col-inner .form-buttons'))
							elem.style.visibility = 'hidden';
						if (elem = document.querySelector('.content-header-floating .form-buttons'))
							elem.style.visibility = 'hidden';
					}
				};

				obj.origDrawBreadcrumbs = obj.drawBreadcrumbs;
				obj.drawBreadcrumbs = function (node) {
					this.origDrawBreadcrumbs(node);
					if (!document.getElementById('breadcrumbs')) {
						node = this.tree.getNodeById('root');
						document.getElementById('content_header').insert({ after: '<ul class="breadcrumbs" id="breadcrumbs"><li><a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + node.id + '\');">' + node.text + '</a></li></ul>' });
					}
				};
			}
		});

		MediabrowserInstance.renameFileUrl   = MediabrowserInstance.deleteFilesUrl.replace(/[a-z_]+\/deleteFiles\//, 'apijs_wysiwyg/renameFile/');
		MediabrowserInstance.deleteFilesUrl  = MediabrowserInstance.deleteFilesUrl.replace(/[a-z_]+\/deleteFiles\//, 'apijs_wysiwyg/deleteFiles/');
		MediabrowserInstance.deleteFolderUrl = MediabrowserInstance.deleteFolderUrl.replace(/[a-z_]+\/deleteFolder\//, 'apijs_wysiwyg/deleteFolder/');
	};

	// cache
	this.clearCache = function (action) {
		apijs.dialog.dialogConfirmation(apijs.i18n.translate(255), apijs.i18n.translate(256), apijsOpenMage.actionClearCache, action);
	};

	this.actionClearCache = function (args) {
		apijs.dialog.remove('waiting', 'lock'); // obligatoire sinon demande de confirmation de quitter la page
		self.location.href = args;
	};

})();

if (typeof self.addEventListener == 'function')
	self.addEventListener('apijsload', apijsOpenMage.init.bind(apijsOpenMage));