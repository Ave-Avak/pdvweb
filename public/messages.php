<?php
/**
 * public/messages.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Boîte de réception (liste des conversations).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$conversations = MessagePrive::listerConversations($idMembre);
$bloques = MessagePrive::listerBloques($idMembre);

$titre = 'Messagerie';
require_once VIEWS_PATH . '/auth/messages.php';
