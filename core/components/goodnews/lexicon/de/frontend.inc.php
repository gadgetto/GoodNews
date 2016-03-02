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
 * GoodNews frontend
 *
 * @package goodnews
 * @subpackage lexicon
 * @language de
 */

$_lang['goodnews.user_err_save']                    = 'Es trat ein Fehler beim Speichern des Benutzers auf.';
$_lang['goodnews.user_err_nf_email']                = 'Es wurde kein Benutzer mit dieser Email Adresse gefunden.';
$_lang['goodnews.profile_err_nf']                   = 'Profil nicht gefunden!';
$_lang['goodnews.profile_err_save']                 = 'Bei der Aktualisierung des Profils trat ein unbekannter Fehler auf.';
$_lang['goodnews.profile_err_unsubscribe']          = 'Bei der Beendigung des Abonnements trat ein unbekannter Fehler auf.';
$_lang['goodnews.profile_updated']                  = 'Profil aktualisiert.';
$_lang['goodnews.profile_unsubscription']           = 'Das Abonnement wurde beendet.';

$_lang['goodnews.email_no_recipient']               = 'Es wurde kein Empfänger für die Email Adresse angegeben.';
$_lang['goodnews.email_not_sent']                   = 'Beim Senden der Email auf ist ein Fehler aufgetreten.';

$_lang['goodnews.validator_form_error']             = 'Bei der Formularvalidierung ist eine Fehler aufgetreten. Bitte überprüfen Sie die eingegebenen Daten.';
$_lang['goodnews.validator_field_required']         = 'Dieses Feld ist erforderlich.';
$_lang['goodnews.validator_field_not_empty']        = 'Dieses Feld muss leer sein.';
$_lang['goodnews.validator_email_multiple']         = 'Diese Email Adresse wird mehrfach verwendet. Wenn Sie der Inhaber dieser Email Adresse sind, kontaktieren Sie bitte den Site Administrator.';
$_lang['goodnews.validator_email_taken']            = 'Die Email Adresse wird bereits verwendet. Geben Sie bitte eine andere Email Adresse an.';
$_lang['goodnews.validator_email_invalid']          = 'Bitte geben Sie eine gültige Email Adresse ein.';
$_lang['goodnews.validator_email_invalid_domain']   = 'Ihre Email Adresse enthält keinen gültigen Domainnamen.';
$_lang['goodnews.validator_username_taken']         = 'Der Benutzername wird bereits verwendet. Geben Sie bitte eine anderen Benutzernamen an.';
$_lang['goodnews.validator_password_dont_match']    = 'Die Passwörter stimmen nicht überein.';
$_lang['goodnews.validator_password_not_confirmed'] = 'Bitte bestätigen Sie Ihr Passwort.';
$_lang['goodnews.validator_min_length']             = 'Dieses Feld darf nicht weniger als [[+length]] Zeichen enthalten.';
$_lang['goodnews.validator_max_length']             = 'Dieses Feld darf nicht mehr als [[+length]] Zeichen enthalten.';
$_lang['goodnews.validator_min_value']              = 'Dieses Feld darf nicht kleiner als [[+value]] sein.';
$_lang['goodnews.validator_max_value']              = 'Dieses Feld darf nicht größer als [[+value]] sein.';
$_lang['goodnews.validator_contains']               = 'Dieses Feld muss irgendwo den Wert [[+value]] enthalten.';
$_lang['goodnews.validator_not_number']             = 'Dieses Feld muss eine gültige Zahl enthalten.';
$_lang['goodnews.validator_range_invalid']          = 'Ungültige Bereichsangabe.';
$_lang['goodnews.validator_range']                  = 'Der angegebene Wert muss zwischen [[+min]] und [[+max]] liegen.';
$_lang['goodnews.validator_not_date']               = 'Dieses Feld muss ein gültiges Datum enthalten.';
$_lang['goodnews.validator_not_lowercase']          = 'Dieses Feld darf nur Großbuchstaben enthalten.';
$_lang['goodnews.validator_not_uppercase']          = 'Dieses Feld darf nur Kleinbuchstaben enthalten.';
$_lang['goodnews.validator_not_regexp']             = 'Dieses Feld enthält einen ungültigen Wert.';

$_lang['goodnews.spam_blocked']                     = 'Ihre Registrierung wurde von einem Spamfilter blockiert: ';
$_lang['goodnews.spam_marked']                      = ' - als Spam markiert.';

$_lang['goodnews.activation_email_subject']         = 'Vielen Dank für Ihr Interesse an unsererm Newsletterservice. Bitte bestätigen Sie die Aktivierung!';
$_lang['goodnews.subscription_email_subject']       = 'Vielen Dank für Ihr Interesse an unsererm Newsletterservice. Ihr Abonnement war erfolgreich!';
$_lang['goodnews.resubscription_email_subject']     = 'Erneuerung Ihres Newsletter-Abonnements!';
$_lang['goodnews.requestlinks_email_subject']       = 'Ihre angeforderten Links zur Aktualisierung oder Kündigung Ihres Abonnements.';

$_lang['goodnews.requestlinks_success']             = 'Die angeforderten Links wurden an die angegebene Email Adresse gesendet.';

$_lang['goodnews.email']                            = 'Email';
$_lang['goodnews.username']                         = 'Benutzername';
$_lang['goodnews.password']                         = 'Passwort';
$_lang['goodnews.password_confirm']                 = 'Passwort bestätigen';
$_lang['goodnews.fullname']                         = 'Vor- und Nachname';
$_lang['goodnews.address']                          = 'Adresse';
$_lang['goodnews.city']                             = 'Stadt';
$_lang['goodnews.zip']                              = 'PLZ';
$_lang['goodnews.state']                            = 'Staat/Bundesland';
$_lang['goodnews.country']                          = 'Land';
$_lang['goodnews.mobilephone']                      = 'Mobiltelefon';
$_lang['goodnews.phone']                            = 'Telefon';
$_lang['goodnews.fax']                              = 'Fax';
$_lang['goodnews.website']                          = 'Webseite';
$_lang['goodnews.update_profile']                   = 'Profil aktualisieren';
$_lang['goodnews.subscribe']                        = 'Abonnieren';
$_lang['goodnews.register']                         = 'Registrieren';
