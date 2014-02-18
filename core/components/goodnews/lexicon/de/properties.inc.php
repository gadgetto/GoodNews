<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * GoodNews properties
 *
 * @package goodnews
 * @subpackage lexicon
 * @language de
 */

// GoodNewsSubscription snippet
$_lang['prop_goodnewssubscription.activation_desc']             = 'Legt fest, ob eine Aktivierung für ein Abonnement benötigt wird (Double Opt-In). Falls ja, wird der Abonnent nicht als aktiv gekennzeichnet, bis er seinen Account aktiviert hat.';
$_lang['prop_goodnewssubscription.activationttl_desc']          = 'Zeit in Minuten, nach der das Aktivierungsmail ungültig wird.';
$_lang['prop_goodnewssubscription.activationemail_desc']        = 'Wenn gesetzt, wird das Aktivierungsmail an diese E-Mail Adresse gesendet, statt an die des neuen Abonnenten.';
$_lang['prop_goodnewssubscription.activationemailsubject_desc'] = 'Der Betreff des Aktivierungsmails.';
$_lang['prop_goodnewssubscription.activationemailtpl_desc']     = 'Das Template des Aktivierungsmails.';
$_lang['prop_goodnewssubscription.activationemailtplalt_desc']  = 'Plain-Text Alternative für das Aktivierungsmail Template.';
$_lang['prop_goodnewssubscription.activationemailtpltype_desc'] = 'Der Templatetyp für das Aktivierungsmail.';
$_lang['prop_goodnewssubscription.activationresourceid_desc']   = 'Die ID jener Ressource, in der das GoodNewsConfirmSubscription Snippet für die Aktivierung verwendet wird.';
$_lang['prop_goodnewssubscription.submittedresourceid_desc']    = 'Weiterleiten des Abonnenten zur Ressource mit dieser ID nach dem Absenden des Formulars.';
$_lang['prop_goodnewssubscription.errtpl_desc']                 = 'Das Template zur Fehlerausgabe in Feldern.';
$_lang['prop_goodnewssubscription.useextended_desc']            = 'Übertrage alle Nicht-Profil Felder des Formulars in erweiterte Felder des MODX Benutzer-Profils. Dies kann für die Speicherung erweiterter Benutzerdaten verwendet werden.';
$_lang['prop_goodnewssubscription.excludeextended_desc']        = 'Eine kommaseparierte Liste von Feldern, die nicht als erweiterte Felder gespeichert werden sollen.';
$_lang['prop_goodnewssubscription.emailfield_desc']             = 'Name des Feldes für die Email Adresse des neuen Abonnenten.';
$_lang['prop_goodnewssubscription.persistparams_desc']          = 'Ein JSON Objekt an Parametern, die über den Registrierungsprozess hinaus gespeichert werden sollen. Dies ist nützlich, wenn Sie die Weiterleitung des GoodNewsConfirmSubscription Snippets nutzen um auf eine andere Seite umzuzuleiten (z.B. Einkaufswagen).';
$_lang['prop_goodnewssubscription.prehooks_desc']               = 'Eine kommaseparierte Liste von Scripten, die vor der Validierung ausgeführt werden sollen. Sollte ein Script nicht validieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein.';
$_lang['prop_goodnewssubscription.posthooks_desc']              = 'Eine kommaseparierte Liste von Scripten, die nach der Registrierung des Nutzers ausgeführt werden sollen. Sollte ein Script nicht validieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein.';
$_lang['prop_goodnewssubscription.redirectback_desc']           = '';
$_lang['prop_goodnewssubscription.redirectbackparams_desc']     = '';
$_lang['prop_goodnewssubscription.submitvar_desc']              = 'Prüfvariable: Wenn leer oder nicht gesetzt, wird das Snippet alle POST-Variablen im Formular verwenden.';
$_lang['prop_goodnewssubscription.successmsg_desc']             = 'Falls Sie nicht mittels des submittedResourceId Parameters weiterleiten, wird diese Nachricht angezeigt.';
$_lang['prop_goodnewssubscription.usergroups_desc']             = 'Eine kommaseparierte Liste von MODX Benutzergruppennamen oder IDs um neue Abonnenten automatisch zu diesen hinzuzufügen.';
$_lang['prop_goodnewssubscription.usergroupsfield_desc']        = 'Name des Feldes wo die MODX Benutzergruppe(n) angeführt sind um Abonnenten automatisch diesen hinzuzufügen. Wird nur verwendet, wenn dieser Wert nicht leer ist.';
$_lang['prop_goodnewssubscription.validate_desc']               = 'Eine kommaseparierte Liste von Feldern zur Validierung im Format feldname:validator (z.B.: fullname:required,email:required). Die Felder können auch verkettet werden (z.B.: email:email:required).';
$_lang['prop_goodnewssubscription.grpfieldsettpl_desc']         = 'Das Template, welches für ein GoodNews Gruppen-Formularfieldset verwendet wird.';
$_lang['prop_goodnewssubscription.grpnametpl_desc']             = 'Das Template, welches für einen GoodNews Gruppen-Namen verwendet wird (ohne Formulafeld).';
$_lang['prop_goodnewssubscription.grpfieldtpl_desc']            = 'Das Template, welches für ein GoodNews Gruppen Checkbox-Formularfeld verwendet wird.';
$_lang['prop_goodnewssubscription.grpfieldhiddentpl_desc']      = 'Das Template, welches für ein verstecktes GoodNews Gruppen Input-Formularfeld verwendet wird.';
$_lang['prop_goodnewssubscription.catfieldtpl_desc']            = 'Das Template, welches für ein GoodNews Kategorien Checkbox-Formularfeld verwendet wird.';
$_lang['prop_goodnewssubscription.catfieldhiddentpl_desc']      = 'Das Template, welches für ein verstecktes GoodNews Kategorien Input-Formularfeld verwendet wird.';
$_lang['prop_goodnewssubscription.groupsonly_desc']             = 'Wenn auf JA gesetzt, werden nur GoodNews Gruppen für das Abonnement verwendet.';
$_lang['prop_goodnewssubscription.includegroups_desc']          = 'Eine kommaseparierte Liste von GoodNews Gruppen IDs, die ausschließlich verwendet werden.';
$_lang['prop_goodnewssubscription.defaultgroups_desc']          = 'Eine kommaseparierte Liste von GoodNews Gruppen IDs, die zwingend verwendet werden. Abonnenten werden automatisch diesen Gruppen zugewiesen. Formularfelder sind versteckt.';
$_lang['prop_goodnewssubscription.defaultcategories_desc']      = 'Eine kommaseparierte Liste von GoodNews Kategorien IDs, die zwingend verwendet werden. Abonnenten werden automatisch diesen Gruppen zugewiesen. Formularfelder sind versteckt.';
$_lang['prop_goodnewssubscription.sort_desc']                   = 'Name des Feldes nach dem die Mailing-Gruppen und Kategorien sortiert werden sollen.';
$_lang['prop_goodnewssubscription.dir_desc']                    = 'Richtung nach der die Mailing-Gruppen und Kategorien sortiert werden sollen.';
$_lang['prop_goodnewssubscription.grpcatplaceholder_desc']      = 'Name des Platzhalters welcher alle GoodNews Gruppen und Kategorien Formular-Felder enthält.';
$_lang['prop_goodnewssubscription.placeholderprefix_desc']      = 'Ein Prefix, welches für alle Platzhalter des Snippets verwendet werden soll.';

// GoodNewsConfirmSubscription snippet
$_lang['prop_goodnewsconfirmsubscription.errorpage_desc']       = 'Wenn gesetzt, wird der Nutzer zu einer definierten Fehlerseite geleitet, wenn dieser versucht nach der Aktivierung diese Seite erneut aufzurufen.';

// GoodNewsUpdateProfile snippet
$_lang['prop_goodnewsupdateprofile.errtpl_desc']                = 'Das Template zur Fehlerausgabe in Feldern.';
$_lang['prop_goodnewsupdateprofile.useextended_desc']           = 'Übertrage alle Nicht-Profil Felder des Formulars in erweiterte Felder des MODX Benutzer-Profils. Dies kann für die Speicherung erweiterter Benutzerdaten verwendet werden.';
$_lang['prop_goodnewsupdateprofile.excludeextended_desc']       = 'Eine kommaseparierte Liste von Feldern, die nicht als extended-fields übernommen werden sollen.';
$_lang['prop_goodnewsupdateprofile.emailfield_desc']            = 'Der Feldname für das E-mail Feld im Formular.';
$_lang['prop_goodnewsupdateprofile.prehooks_desc']              = 'Skripte, die vor der Validierung des Formulars ausgeführt werden sollen. Dies kann eine kommaseparierte Liste von Hooks sein. Sollte eine nicht vailidieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein, welches dann ausgeführt wird.';
$_lang['prop_goodnewsupdateprofile.posthooks_desc']             = 'Skripte, die nach der Validierung des Formulars ausgeführt werden sollen. Dies kann eine kommaseparierte Liste von Hooks sein. Sollte eine nicht vailidieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein, welches dann ausgeführt wird.';
$_lang['prop_goodnewsupdateprofile.sendunauthorizedpage_desc']  = 'Wenn ein Benutzer anhand der übergebenen SID aus dem Email nicht identifiziert wird, wird dieser zur Unauthorized Seite weitergeleitet.';
$_lang['prop_goodnewsupdateprofile.reloadonsuccess_desc']       = 'Falls gesetzt, leitet die Seite an sich selbst mit einem GET-Parameter weiter um doppelte Postbacks zu vermeiden. Falls nicht gesetzt, wird lediglich ein Erfolg-Platzhalter angezeigt.';
$_lang['prop_goodnewsupdateprofile.submitvar_desc']             = 'Prüfvariable: Wenn leer oder nicht gesetzt, wird das Snippet alle POST-Variablen im Formular verwenden.';
$_lang['prop_goodnewsupdateprofile.successkey_desc']            = 'Name des Schlüssels, der im Falle eines erfolgreichen Updates als URL Parameter mit dem Wert true übergeben wird.';
$_lang['prop_goodnewsupdateprofile.successmsg_desc']            = 'Die Nachricht, die vom Processor zurück gegeben wird wenn das Update erfolgreich war.';
$_lang['prop_goodnewsupdateprofile.validate_desc']              = 'Eine kommaseparierte Liste von Feldern zur Validierung im Format feldname:validator (z.B.: fullname:required,email:required). Die Felder können auch verkettet werden (z.B.: email:email:required).';
$_lang['prop_goodnewsupdateprofile.grpfieldsettpl_desc']        = 'Das Template, welches für ein GoodNews Gruppen-Formularfieldset verwendet wird.';
$_lang['prop_goodnewsupdateprofile.grpnametpl_desc']            = 'Das Template, welches für einen GoodNews Gruppen-Namen verwendet wird (ohne Formulafeld).';
$_lang['prop_goodnewsupdateprofile.grpfieldtpl_desc']           = 'Das Template, welches für ein GoodNews Gruppen Checkbox-Formularfeld verwendet wird.';
$_lang['prop_goodnewsupdateprofile.grpfieldhiddentpl_desc']     = 'Das Template, welches für ein verstecktes GoodNews Gruppen Input-Formularfeld verwendet wird.';
$_lang['prop_goodnewsupdateprofile.catfieldtpl_desc']           = 'Das Template, welches für ein GoodNews Kategorien Checkbox-Formularfeld verwendet wird.';
$_lang['prop_goodnewsupdateprofile.catfieldhiddentpl_desc']     = 'Das Template, welches für ein verstecktes GoodNews Kategorien Input-Formularfeld verwendet wird.';
$_lang['prop_goodnewsupdateprofile.groupsonly_desc']            = 'Wenn auf JA gesetzt, werden nur GoodNews Gruppen für das Abonnement verwendet.';
$_lang['prop_goodnewsupdateprofile.includegroups_desc']         = 'Eine kommaseparierte Liste von GoodNews Gruppen IDs, die ausschließlich verwendet werden.';
$_lang['prop_goodnewsupdateprofile.defaultgroups_desc']         = 'Eine kommaseparierte Liste von GoodNews Gruppen IDs, die zwingend verwendet werden. Abonnenten werden automatisch diesen Gruppen zugewiesen. Formularfelder sind versteckt.';
$_lang['prop_goodnewsupdateprofile.defaultcategories_desc']     = 'Eine kommaseparierte Liste von GoodNews Kategorien IDs, die zwingend verwendet werden. Abonnenten werden automatisch diesen Gruppen zugewiesen. Formularfelder sind versteckt.';
$_lang['prop_goodnewsupdateprofile.sort_desc']                  = 'Name des Feldes nach dem die Mailing-Gruppen und Kategorien sortiert werden sollen.';
$_lang['prop_goodnewsupdateprofile.dir_desc']                   = 'Richtung nach der die Mailing-Gruppen und Kategorien sortiert werden sollen.';
$_lang['prop_goodnewsupdateprofile.grpcatplaceholder_desc']     = 'Name des Platzhalters welcher alle GoodNews Gruppen und Kategorien Formular-Felder enthält.';
$_lang['prop_goodnewsupdateprofile.placeholderprefix_desc']     = 'Ein Prefix, welches für alle Platzhalter des Snippets verwendet werden soll.';

// GoodNewsUnSubscription snippet
$_lang['prop_goodnewsunsubscription.errtpl_desc']               = 'Das Template zur Fehlerausgabe in Feldern.';
$_lang['prop_goodnewsunsubscription.prehooks_desc']             = 'Skripte, die vor der Validierung des Formulars ausgeführt werden sollen. Dies kann eine kommaseparierte Liste von Hooks sein. Sollte eine nicht vailidieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein, welches dann ausgeführt wird.';
$_lang['prop_goodnewsunsubscription.posthooks_desc']            = 'Skripte, die nach der Validierung des Formulars ausgeführt werden sollen. Dies kann eine kommaseparierte Liste von Hooks sein. Sollte eine nicht vailidieren, werden weitere nicht ausgeführt. Ein Hook kann auch ein Snippetname sein, welches dann ausgeführt wird.';
$_lang['prop_goodnewsunsubscription.sendunauthorizedpage_desc'] = 'Wenn ein Benutzer anhand der übergebenen sid aus dem Email nicht identifiziert wird, wird dieser zur Unauthorized Seite weitergeleitet.';
$_lang['prop_goodnewsunsubscription.submitvar_desc']            = 'Prüfvariable: Wenn leer oder nicht gesetzt, wird das Snippet alle POST-Variablen im Formular verwenden.';
$_lang['prop_goodnewsunsubscription.successkey_desc']           = 'Name des Schlüssels, der im Falle einer erfolgreichen Auflösung des Abos als URL Parameter mit dem Wert true übergeben wird.';
$_lang['prop_goodnewsunsubscription.removeuserdata_desc']       = 'Wenn gesetzt, werden im Falle einer Auflösung des Abonnements durch den Benutzer sämtliche Benutzer-Daten aus der MODX Datenbank entfernt. Anderenfalls werden nur GoodNews-relevante Daten entfernt und der Benutzer wird deaktivert.';
$_lang['prop_goodnewsunsubscription.placeholderprefix_desc']    = 'Ein Prefix, welches für alle Platzhalter des Snippets verwendet werden soll.';

// GoodNewsGetNewsletters snippet
$_lang['prop_goodnewsgetnewsletters.parent_desc']               = 'Die id des Mailing Containers, aus dem die Newsletter Dokumente bezogen werden. Wenn leer, wird automatisch die id des aktuellen Containers verwendet.';
$_lang['prop_goodnewsgetnewsletters.tpl_desc']                  = 'Name des Templates für die Ausgabe einer Newsletter Resource-Zeile. ANMERKUNG: wird kein Template angegeben, erfolgt die Ausgabe als Array Dump.';
$_lang['prop_goodnewsgetnewsletters.sortby_desc']               = 'Der Name des Feldes nach dem sortiert werden soll. Es kann auch ein JSON Objekt für die Sortierung nach mehreren Feldern verwendet werden, z.B. {"publishedon":"ASC","createdon":"DESC"}. Standard ist publishedon.';
$_lang['prop_goodnewsgetnewsletters.sortdir_desc']              = 'Richtung nach der die Newsletter Dokumente sortiert werden sollen.';
$_lang['prop_goodnewsgetnewsletters.includecontent_desc']       = 'Legt fest, ob das Inhaltsfeld von Newsletter Dokumenten in den Ergebnissen enthalten sein soll. Standard is NEIN';
$_lang['prop_goodnewsgetnewsletters.limit_desc']                = 'Limitiert die Anzahl der zurückgegebenen Newsletter Resourcen. Standard ist 0 = kein Limit.';
$_lang['prop_goodnewsgetnewsletters.offset_desc']               = 'An offset of resources returned by the criteria to skip.';
$_lang['prop_goodnewsgetnewsletters.totalvar_desc']             = 'Name des Platzhalters, der die Anzahl der zurückgelieferten Newsletter Dokumente enthält.';
$_lang['prop_goodnewsgetnewsletters.outputseparator_desc']      = 'Trenner für die Ausgabe der einzelnen Newsletter Templates.';
$_lang['prop_goodnewsgetnewsletters.toplaceholder_desc']        = 'Wenn gesetzt, wird das Ergebnis in diesem Platzhalter gespeichert und nicht direkt ausgegeben.';
$_lang['prop_goodnewsgetnewsletters.debug_desc']                = 'Wenn aktiviert, wird der SQL Query String an das MODX System Log gesendet. Standard ist NEIN.';

// List options
$_lang['opt_goodnews.chunk']    = 'Chunk';
$_lang['opt_goodnews.file']     = 'Datei';
$_lang['opt_goodnews.inline']   = 'Inline';
$_lang['opt_goodnews.embedded'] = 'Eingebettet';
$_lang['opt_goodnews.asc']      = 'Aufsteigend';
$_lang['opt_goodnews.desc']     = 'Absteigend';
