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
 * Portuguese language strings for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Ações';
$string['active_delegations'] = 'Delegações ativas';
$string['add_delegation'] = 'Adicionar conta delegada';
$string['allowopenended'] = 'Permitir delegações sem data de fim';
$string['allowopenended_desc'] = 'Quando desativado, cada delegação tem de ter uma data de fim.';
$string['bulk_deleted_success'] = 'As delegações selecionadas foram removidas com sucesso.';
$string['confirm_bulk_delete'] = 'Tem a certeza de que pretende remover todas as delegações selecionadas?';
$string['confirm_delete'] = 'Tem a certeza de que pretende remover esta delegação?';
$string['create_delegations'] = 'Criar novas delegações';
$string['delegateaccount:create'] = 'Criar delegações de contas';
$string['delegateaccount:manage'] = 'Gerir delegações de contas';
$string['delegateaccount:revoke'] = 'Revogar delegações de contas';
$string['delegateaccount:update'] = 'Atualizar delegações de contas';
$string['delegateaccount:use'] = 'Iniciar sessão como uma conta delegada';
$string['delegateaccount:view'] = 'Ver delegações de contas';
$string['delegateaccount:viewactivity'] = 'Ver atividade de contas delegadas';
$string['delegated_accounts_for'] = 'Contas delegadas de {$a}';
$string['delegateduser'] = 'Conta delegada';
$string['delegatedusers'] = 'Contas delegadas';
$string['delegatedusers_help'] = 'Selecione as contas de destino. As pessoas selecionadas acima poderão iniciar sessão e agir como estas contas.';
$string['delegation_count'] = 'Contas atuais ou agendadas';
$string['delegation_end'] = 'O acesso termina';
$string['delegation_no_end'] = 'Sem data de fim';
$string['delegation_start'] = 'O acesso começa';
$string['delegation_status'] = 'Estado';
$string['delegation_status_active'] = 'Ativa';
$string['delegation_status_expired'] = 'Expirada';
$string['delegation_status_revoked'] = 'Revogada';
$string['delegation_status_scheduled'] = 'Agendada';
$string['delegationnotificationsubject'] = 'Acesso a conta delegada concedido';
$string['delegations_created_success'] = 'As delegações foram criadas com sucesso.';
$string['delete_selected'] = 'Remover selecionadas';
$string['deleted_success'] = 'A delegação foi removida com sucesso.';
$string['error_already_exists'] = 'Esta delegação já existe.';
$string['error_alreadyloggedinas'] = 'Tem de voltar à sua conta original antes de usar uma conta delegada.';
$string['error_ineligibleuser'] = 'Utilizadores eliminados ou suspensos não podem participar numa delegação.';
$string['error_invalidtemplateplaceholder'] = 'O modelo de notificação contém um marcador não suportado: {$a}.';
$string['error_invaliduser'] = 'Um ou mais utilizadores selecionados já não existem.';
$string['error_maxbulkoperations'] = 'Uma ação em massa não pode afetar mais de {$a} registos de delegação.';
$string['error_maxdelegations'] = 'Um utilizador autorizado não pode ter mais de {$a} contas delegadas atuais ou agendadas.';
$string['error_maximumduration'] = 'Uma delegação não pode durar mais de {$a} dias.';
$string['error_openendednotallowed'] = 'Não são permitidas delegações sem data de fim.';
$string['error_privilegedtarget'] = 'As contas de administrador do site não podem ser delegadas.';
$string['error_unauthorized'] = 'Não tem permissão para aceder a esta conta delegada.';
$string['eventdelegationcreated'] = 'Delegação de conta criada';
$string['eventdelegationrevoked'] = 'Delegação de conta revogada';
$string['eventdelegationupdated'] = 'Delegação de conta atualizada';
$string['last_delegated_access'] = 'Último acesso delegado';
$string['manage_accounts'] = 'Gerir contas delegadas';
$string['manage_user_delegations'] = 'Gerir as contas delegadas deste utilizador';
$string['maxbulkoperations'] = 'Máximo de registos por ação em massa';
$string['maxbulkoperations_desc'] = 'Número máximo de registos de delegação que uma ação em massa pode criar ou revogar. Defina 0 para não limitar.';
$string['maxdelegationsperuser'] = 'Máximo de contas delegadas por utilizador';
$string['maxdelegationsperuser_desc'] = 'Número máximo de contas atuais ou agendadas a que um utilizador autorizado pode aceder. Defina 0 para não limitar.';
$string['maximumdurationdays'] = 'Duração máxima da delegação';
$string['maximumdurationdays_desc'] = 'Duração máxima em dias. Defina 0 para não limitar.';
$string['messageprovider:delegationnotification'] = 'Notificações de contas delegadas';
$string['no_delegated_access'] = 'Não foi registado nenhum acesso delegado';
$string['no_delegations'] = 'Ainda não foram criadas delegações de contas.';
$string['no_delegations_created'] = 'Não foram criadas novas delegações; os duplicados foram ignorados.';
$string['notificationaccessends'] = 'O acesso termina:';
$string['notificationaccessgranted'] = 'Foi-lhe concedido acesso a uma conta delegada.';
$string['notificationaccessstarts'] = 'O acesso começa:';
$string['notificationaccountaccess'] = 'Agora pode aceder a {$a->delegateduser} em {$a->sitefullname}.';
$string['notificationgreeting'] = 'Olá {$a},';
$string['notificationpolicy'] = 'Política de notificação';
$string['notificationpolicy_always'] = 'Notificar sempre';
$string['notificationpolicy_desc'] = 'Controla se o formulário de delegação pode escolher enviar uma notificação.';
$string['notificationpolicy_never'] = 'Nunca notificar';
$string['notificationpolicy_optional'] = 'Permitir que quem cria a delegação escolha';
$string['notificationrecipients'] = 'Destinatários da notificação';
$string['notificationrecipients_authorised'] = 'Apenas utilizador autorizado';
$string['notificationrecipients_both'] = 'Ambos os utilizadores';
$string['notificationrecipients_desc'] = 'Escolha quais utilizadores afetados recebem uma notificação de delegação.';
$string['notificationrecipients_target'] = 'Apenas conta delegada';
$string['notificationsubject'] = 'Assunto da notificação ({$a})';
$string['notificationsubject_desc'] = 'Assunto de texto simples usado para este idioma quando é enviada uma notificação de conta delegada.';
$string['notificationsupportmessage'] = 'Se não esperava este acesso, contacte a administração do site.';
$string['notificationtemplate'] = 'Modelo de notificação ({$a})';
$string['notificationtemplate_desc'] = 'Conteúdo HTML opcional que substitui a mensagem integrada do Moodle Mustache para este idioma. Deixe em branco para usar a mensagem integrada. Marcadores disponíveis: {$a->authoriseduser}, {$a->delegateduser}, {$a->actor}, {$a->timestart}, {$a->timeend}, {$a->sitefullname}.';
$string['notifyonrevocation'] = 'Notificar quando uma delegação for revogada';
$string['notifyonrevocation_desc'] = 'Envia a notificação configurada aos destinatários selecionados quando o acesso é revogado.';
$string['pluginname'] = 'Conta delegada';
$string['privacy:metadata:local_delegateaccount'] = 'Armazena as delegações de contas configuradas pela administração do site.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'A conta a que uma pessoa pode aceder.';
$string['privacy:metadata:local_delegateaccount:notificationmode'] = 'A decisão de notificação selecionada para a delegação.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'A pessoa que pode aceder a uma conta delegada.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'A data e hora em que a delegação foi criada.';
$string['privacy:metadata:local_delegateaccount:timeend'] = 'A data e hora em que a delegação expira.';
$string['privacy:metadata:local_delegateaccount:timemodified'] = 'A data e hora da última alteração da delegação.';
$string['privacy:metadata:local_delegateaccount:timerevoked'] = 'A data e hora em que a delegação foi revogada.';
$string['privacy:metadata:local_delegateaccount:timestart'] = 'A data e hora em que a delegação fica ativa.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'A pessoa administradora que criou a delegação.';
$string['privacy:metadata:local_delegateaccount:usermodified'] = 'A pessoa que alterou a delegação pela última vez.';
$string['privacy:metadata:local_delegateaccount:userrevoked'] = 'A pessoa que revogou a delegação.';
$string['privacy:path:delegations'] = 'Delegações de contas';
$string['privacy:role:creator'] = 'Criador da delegação';
$string['privacy:role:delegateduser'] = 'Conta delegada';
$string['privacy:role:modifier'] = 'Modificador da delegação';
$string['privacy:role:realuser'] = 'Utilizador autorizado';
$string['privacy:role:revoker'] = 'Revogador da delegação';
$string['protectprivilegedtargets'] = 'Proteger contas de administrador do site';
$string['protectprivilegedtargets_desc'] = 'Impede que contas de administrador do site sejam delegadas a outro utilizador.';
$string['realuser'] = 'Utilizador principal';
$string['realusers'] = 'Utilizadores principais';
$string['realusers_help'] = 'Selecione as pessoas que poderão iniciar sessão como outra conta. Pode pesquisar e selecionar vários utilizadores.';
$string['revoke_delegation'] = 'Revogar delegação';
$string['scheduled_delegations'] = 'Delegações agendadas';
$string['search_authorised_users'] = 'Pesquisar utilizadores autorizados';
$string['settings'] = 'Configurações de contas delegadas';
$string['settings_delegations'] = 'Controlos de delegação';
$string['settings_delegations_desc'] = 'Defina os limites aplicáveis quando o acesso a contas delegadas é criado ou revogado.';
$string['settings_notifications'] = 'Notificações';
$string['settings_notifications_desc'] = 'Escolha quando os utilizadores afetados são notificados e forneça um modelo para cada idioma instalado.';
$string['timecreated'] = 'Data de criação';
$string['usermenulimit'] = 'Contas delegadas mostradas no menu do utilizador';
$string['usermenulimit_desc'] = 'Número máximo de contas delegadas ativas mostradas no menu do utilizador. Defina 0 para mostrar todas.';
