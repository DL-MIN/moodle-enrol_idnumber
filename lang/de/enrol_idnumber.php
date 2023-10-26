<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language pack 'de'.
 *
 * @package    enrol_idnumber
 * @author     Lars Thoms <lars.thoms@uni-hamburg.de>
 * @copyright  2023 Universität Hamburg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']       = 'Einschreibung mit Kurs-ID';
$string['pluginname_desc']  =
        'Das Einschreibeplugin mittels Kurs-ID synchronisiert die Profilfelder der Nutzer/innen mit denen der Kursteilnehmer/innen.';
$string['privacy:metadata'] = 'Das Plugin \'Einschreibung mit Kurs-ID\' speichert keine personenbezogenen Daten.';

// Settings.
$string['defaultrole']                 = 'Standardrolle';
$string['defaultrole_desc']            = 'Standardrolle, mit der Nutzer/innen bei diesem Plugin zugewiesen werden.';
$string['profilefield']                = 'Profilfeld';
$string['profilefield_desc']           = 'Das benutzerdefinierte Profilfeld, das für die Suche nach Kurs-IDs verwendet wird.';
$string['profilefield_separator']      = 'Profilfeld Trennzeichen';
$string['profilefield_separator_desc'] = 'Das Trennzeichen, das zur Aufteilung der angegebenen Listenelemente verwendet wird.';
$string['no_custom_field']             =
        'Es scheint kein benutzerdefiniertes Profilfeld zu geben. <a href="{$a}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Bearbeiten" role="img" aria-label="Bearbeiten"></i></a>';
$string['no_profile_field_selected']   = 'In den Einstellungen des Plugins enrol_idnumber wurde kein Profilfeld ausgewählt.';

// Instance settings.
$string['status']               = 'Einschreibungen mit Kurs-ID aktivieren';
$string['status_desc']          =
        'Erlauben Sie den Kurszugang für intern eingeschriebene Nutzer/innen. Diese Option sollte in den meisten Fällen aktiviert bleiben.';
$string['status_help']          =
        'Diese Einstellung legt fest, ob Nutzer/innen automatisch eingeschrieben werden können, wenn das ausgewählte Profilfeld die Kurs-ID enthält.';
$string['idnumbercourse_set']   =
        '<div class="alert alert-info" role="alert" data-aria-autofocus="true">{$a->idnumber} <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Bearbeiten" role="img" aria-label="Bearbeiten"></i></a></div>';
$string['idnumbercourse_unset'] =
        '<div class="alert alert-warning" role="alert" data-aria-autofocus="true">Dieser Kurs hat keine Kurs-ID, die für diese Einschreibemethode erforderlich ist. <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Bearbeiten" role="img" aria-label="Bearbeiten"></i></a></div>';
$string['idnumbercourse_error'] = 'Einschreibungen können nicht ohne Angabe der Kurs-ID aktiviert werden!';
$string['startdatecourse_set']  =
        '<div class="alert alert-info" role="alert" data-aria-autofocus="true">{$a->startdate} <a href="{$a->settings}" target="_blank"><i class="icon fa fa-cog fa-fw iconsmall" title="Bearbeiten" role="img" aria-label="Bearbeiten"></i></a></div>';

// Tasks.
$string['sync_enrolments_task'] = 'Einschreibungen mit Kurs-ID synchronisieren';
