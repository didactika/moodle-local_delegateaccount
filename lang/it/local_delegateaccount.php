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
 * Italian language strings for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Azioni';
$string['active_delegations'] = 'Deleghe attive';
$string['activity_action'] = 'Azione';
$string['activity_component'] = 'Componente';
$string['activity_event'] = 'Evento';
$string['activity_filter_action'] = 'L\'azione contiene';
$string['activity_filter_component'] = 'Il componente contiene';
$string['activity_filter_datefrom'] = 'Da';
$string['activity_filter_dateto'] = 'Fino a';
$string['activity_filter_invalidperiod'] = 'La fine del periodo deve essere successiva al suo inizio.';
$string['activity_target'] = 'Destinazione';
$string['activity_time'] = 'Data e ora';
$string['add_delegation'] = 'Aggiungi account delegato';
$string['allowopenended'] = 'Consenti deleghe senza data di fine';
$string['allowopenended_desc'] = 'Se disabilitato, ogni delega deve avere una data di fine.';
$string['authoriseduser'] = 'Persona autorizzata';
$string['bulk_deleted_success'] = 'Le deleghe selezionate sono state rimosse correttamente.';
$string['confirm_bulk_delete'] = 'Rimuovere tutte le deleghe selezionate?';
$string['confirm_delete'] = 'Rimuovere questa delega?';
$string['confirm_revoke_bulk'] = 'Revocare le deleghe selezionate?';
$string['confirm_revoke_single'] = 'Revocare questa delega?';
$string['confirm_revoke_title'] = 'Conferma revoca';
$string['create_delegations'] = 'Creare nuove deleghe';
$string['delegateaccount:create'] = 'Creare deleghe degli account';
$string['delegateaccount:manage'] = 'Gestire le deleghe degli account';
$string['delegateaccount:revoke'] = 'Revocare deleghe degli account';
$string['delegateaccount:update'] = 'Aggiornare deleghe degli account';
$string['delegateaccount:use'] = 'Accedere come account delegato';
$string['delegateaccount:view'] = 'Visualizzare deleghe degli account';
$string['delegateaccount:viewactivity'] = 'Visualizzare l\'attività degli account delegati';
$string['delegated_accounts_for'] = 'Account delegati per {$a}';
$string['delegated_accounts_menu'] = 'Account delegati';
$string['delegated_activity_description'] = 'Questo rapporto mostra gli eventi registrati da Moodle mentre {$a->authoriseduser} utilizzava l\'account delegato {$a->delegateduser}, da {$a->timestart} a {$a->timeend}. L\'archivio dei log del sito rimane la fonte di riferimento.';
$string['delegated_activity_for'] = 'Attività delegata: {$a->authoriseduser} come {$a->delegateduser}';
$string['delegateduser'] = 'Account delegato';
$string['delegatedusers'] = 'Account delegati';
$string['delegatedusers_help'] = 'Selezionare gli account di destinazione. Le persone selezionate sopra potranno accedere e agire come questi account.';
$string['delegation_count'] = 'Account correnti o pianificati';
$string['delegation_created'] = 'Creata';
$string['delegation_created_by'] = 'Creata da';
$string['delegation_details'] = 'Dettagli della delega';
$string['delegation_end'] = 'L\'accesso termina';
$string['delegation_filter_none'] = 'Nessun record di delega';
$string['delegation_filter_user'] = 'Dati utente';
$string['delegation_modified'] = 'Ultima modifica';
$string['delegation_modified_by'] = 'Modificata da';
$string['delegation_no_end'] = 'Nessuna data di fine';
$string['delegation_revoked'] = 'Revocata';
$string['delegation_revoked_by'] = 'Revocata da';
$string['delegation_search'] = 'Cerca account delegati';
$string['delegation_start'] = 'L\'accesso inizia';
$string['delegation_status'] = 'Stato';
$string['delegation_status_active'] = 'Attiva';
$string['delegation_status_expired'] = 'Scaduta';
$string['delegation_status_revoked'] = 'Revocata';
$string['delegation_status_scheduled'] = 'Pianificata';
$string['delegation_unknown_user'] = 'Utente eliminato';
$string['delegation_updated_success'] = 'La delega è stata aggiornata correttamente.';
$string['delegationnotificationmode'] = 'Notificare le persone interessate';
$string['delegationnotificationmode_always'] = 'Inviare una notifica';
$string['delegationnotificationmode_desc'] = 'Scegliere se questa delega invia una notifica alle persone interessate.';
$string['delegationnotificationmode_help'] = 'Scegliere se questa operazione invia una notifica alle persone interessate. Questa opzione appare solo quando la politica del sito consente la scelta.';
$string['delegationnotificationmode_never'] = 'Non inviare una notifica';
$string['delegationnotificationsubject'] = 'Accesso all’account delegato concesso';
$string['delegations_created_success'] = 'Le deleghe sono state create correttamente.';
$string['delegations_revoked_success'] = 'Deleghe revocate: {$a}.';
$string['delegations_updated_success'] = 'Deleghe aggiornate: {$a}.';
$string['delegations_user_not_authorised'] = 'Questo utente non dispone più dell\'autorizzazione per usare gli account delegati. La cronologia delle deleghe resta disponibile per la consultazione, ma non è possibile crearne di nuove.';
$string['delete_selected'] = 'Rimuovi selezionate';
$string['deleted_success'] = 'La delega è stata rimossa correttamente.';
$string['edit_delegation'] = 'Modifica delega';
$string['edit_selected_delegations'] = 'Modifica selezionate';
$string['error_already_exists'] = 'Questa delega esiste già.';
$string['error_alreadyloggedinas'] = 'È necessario tornare all\'account originale prima di usare un account delegato.';
$string['error_ineligibleuser'] = 'Gli utenti eliminati o sospesi non possono partecipare a una delega.';
$string['error_invaliddelegations'] = 'Le deleghe selezionate non sono più attive o non appartengono a questo utente autorizzato.';
$string['error_invalidperiod'] = 'La data di fine deve essere successiva alla data di inizio.';
$string['error_invalidtemplateplaceholder'] = 'Il modello di notifica contiene un segnaposto non supportato: {$a}.';
$string['error_invaliduser'] = 'Uno o più utenti selezionati non esistono più.';
$string['error_maxbulkoperations'] = 'Un’azione massiva non può interessare più di {$a} record di delega.';
$string['error_maxdelegations'] = 'Un utente autorizzato non può avere più di {$a} account delegati correnti o pianificati.';
$string['error_maximumduration'] = 'Una delega non può durare più di {$a} giorni.';
$string['error_openendednotallowed'] = 'Le deleghe senza data di fine non sono consentite.';
$string['error_privilegedtarget'] = 'Gli account amministratore del sito non possono essere delegati.';
$string['error_unauthorised_realuser'] = 'Ogni utente autorizzato deve disporre attualmente dell\'autorizzazione per usare gli account delegati.';
$string['error_unauthorized'] = 'Non si dispone dell\'autorizzazione per accedere a questo account delegato.';
$string['eventdelegationcreated'] = 'Delega dell\'account creata';
$string['eventdelegationrevoked'] = 'Delega dell\'account revocata';
$string['eventdelegationupdated'] = 'Delega dell\'account aggiornata';
$string['last_delegated_access'] = 'Ultimo accesso delegato';
$string['manage_accounts'] = 'Gestire gli account delegati';
$string['manage_authorised_users'] = 'Utenti autorizzati';
$string['manage_authorised_users_description'] = 'Tutti gli utenti attivi che al momento dispongono dell\'autorizzazione per usare gli account delegati, inclusi quelli senza deleghe.';
$string['manage_historical_users'] = 'Utenti senza autorizzazione';
$string['manage_historical_users_description'] = 'Utenti che mantengono registrazioni di deleghe ma non dispongono più dell\'autorizzazione per usare gli account delegati. La loro cronologia e i rapporti di attività restano disponibili per la consultazione.';
$string['manage_user_delegations'] = 'Gestire gli account delegati di questo utente';
$string['maxbulkoperations'] = 'Numero massimo di record per azione massiva';
$string['maxbulkoperations_desc'] = 'Numero massimo di record di delega che un’azione massiva può creare o revocare. Imposta 0 per nessun limite.';
$string['maxdelegationsperuser'] = 'Numero massimo di account delegati per utente';
$string['maxdelegationsperuser_desc'] = 'Numero massimo di account correnti o pianificati accessibili da un utente autorizzato. Imposta 0 per nessun limite.';
$string['maximumdurationdays'] = 'Durata massima della delega';
$string['maximumdurationdays_desc'] = 'Durata massima in giorni. Imposta 0 per nessun limite.';
$string['messageprovider:delegationnotification'] = 'Notifiche degli account delegati';
$string['my_delegated_accounts'] = 'I miei account delegati';
$string['my_delegated_accounts_description'] = 'Questa pagina elenca tutti gli account attivi attualmente delegati all\'utente. Selezionare un account per avviare una sessione delegata protetta.';
$string['no_delegated_access'] = 'Nessun accesso delegato registrato';
$string['no_delegations'] = 'Non sono ancora state create deleghe di account.';
$string['no_delegations_created'] = 'Non sono state create nuove deleghe; i duplicati sono stati ignorati.';
$string['notificationaccessends'] = 'L’accesso termina:';
$string['notificationaccessgranted'] = 'Ti è stato concesso l’accesso a un account delegato.';
$string['notificationaccessstarts'] = 'L’accesso inizia:';
$string['notificationaccountaccess'] = 'Ora puoi accedere a {$a->delegateduser} in {$a->sitefullname}.';
$string['notificationgreeting'] = 'Ciao {$a},';
$string['notificationpolicy'] = 'Politica di notifica';
$string['notificationpolicy_always'] = 'Notifica sempre';
$string['notificationpolicy_desc'] = 'Controlla se il modulo di delega può scegliere di inviare una notifica.';
$string['notificationpolicy_never'] = 'Non notificare mai';
$string['notificationpolicy_optional'] = 'Consenti a chi crea la delega di scegliere';
$string['notificationrecipients'] = 'Destinatari della notifica';
$string['notificationrecipients_authorised'] = 'Solo utente autorizzato';
$string['notificationrecipients_both'] = 'Entrambi gli utenti';
$string['notificationrecipients_desc'] = 'Scegli quali utenti interessati ricevono una notifica di delega.';
$string['notificationrecipients_target'] = 'Solo account delegato';
$string['notificationsubject'] = 'Oggetto della notifica ({$a})';
$string['notificationsubject_desc'] = 'Oggetto in testo semplice usato per questa lingua quando viene inviata una notifica di account delegato.';
$string['notificationsupportmessage'] = 'Se non ti aspettavi questo accesso, contatta l’amministrazione del sito.';
$string['notificationtemplate'] = 'Modello di notifica ({$a})';
$string['notificationtemplate_desc'] = 'Contenuto HTML facoltativo che sostituisce il messaggio Moodle Mustache integrato per questa lingua. Lascia vuoto per usare il messaggio integrato. Segnaposto disponibili: {$a->authoriseduser}, {$a->delegateduser}, {$a->actor}, {$a->timestart}, {$a->timeend}, {$a->sitefullname}.';
$string['notifyonrevocation'] = 'Notifica quando una delega viene revocata';
$string['notifyonrevocation_desc'] = 'Invia la notifica configurata ai destinatari selezionati quando l’accesso viene revocato.';
$string['pluginname'] = 'Account delegato';
$string['privacy:metadata:local_delegateaccount'] = 'Memorizza le deleghe degli account configurate dall\'amministrazione del sito.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'L\'account a cui una persona può accedere.';
$string['privacy:metadata:local_delegateaccount:notificationmode'] = 'La scelta di notifica selezionata per la delega.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'La persona che può accedere a un account delegato.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'Data e ora di creazione della delega.';
$string['privacy:metadata:local_delegateaccount:timeend'] = 'La data e l\'ora di scadenza della delega.';
$string['privacy:metadata:local_delegateaccount:timemodified'] = 'La data e l\'ora dell\'ultima modifica della delega.';
$string['privacy:metadata:local_delegateaccount:timerevoked'] = 'La data e l\'ora di revoca della delega.';
$string['privacy:metadata:local_delegateaccount:timestart'] = 'La data e l\'ora di attivazione della delega.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'La persona amministratrice che ha creato la delega.';
$string['privacy:metadata:local_delegateaccount:usermodified'] = 'La persona che ha modificato per ultima la delega.';
$string['privacy:metadata:local_delegateaccount:userrevoked'] = 'La persona che ha revocato la delega.';
$string['privacy:path:delegations'] = 'Deleghe degli account';
$string['privacy:role:creator'] = 'Creatore della delega';
$string['privacy:role:delegateduser'] = 'Account delegato';
$string['privacy:role:modifier'] = 'Modificatore della delega';
$string['privacy:role:realuser'] = 'Utente autorizzato';
$string['privacy:role:revoker'] = 'Revocatore della delega';
$string['protectprivilegedtargets'] = 'Proteggi gli account amministratore del sito';
$string['protectprivilegedtargets_desc'] = 'Impedisce che gli account amministratore del sito siano delegati a un altro utente.';
$string['realuser'] = 'Utente principale';
$string['realusers'] = 'Utenti principali';
$string['realusers_help'] = 'Selezionare le persone che potranno accedere come un altro account. È possibile cercare e selezionare più utenti.';
$string['revoke_delegation'] = 'Revoca delega';
$string['revoke_selected'] = 'Revoca selezionate';
$string['scheduled_delegations'] = 'Deleghe pianificate';
$string['search_authorised_users'] = 'Cerca utenti autorizzati';
$string['select_all_delegations'] = 'Seleziona tutte le deleghe revocabili in questa pagina';
$string['select_delegation'] = 'Seleziona la delega di {$a}';
$string['settings'] = 'Impostazioni degli account delegati';
$string['settings_delegations'] = 'Controlli di delega';
$string['settings_delegations_desc'] = 'Definisci i limiti applicati quando l’accesso a un account delegato viene creato o revocato.';
$string['settings_notifications'] = 'Notifiche';
$string['settings_notifications_desc'] = 'Scegli quando notificare gli utenti interessati e fornisci un modello per ogni lingua installata.';
$string['timecreated'] = 'Data di creazione';
$string['use_delegated_account'] = 'Usa account delegato: {$a}';
$string['usermenulimit'] = 'Account delegati mostrati nel menu utente';
$string['usermenulimit_desc'] = 'Numero massimo di account delegati attivi mostrati nel menu utente. Imposta 0 per mostrarli tutti.';
$string['view_all_delegated_accounts'] = 'Visualizza tutti gli account delegati';
$string['view_delegated_activity'] = 'Visualizzare l\'attività delegata';
$string['view_delegation_details'] = 'Visualizzare i dettagli della delega';
