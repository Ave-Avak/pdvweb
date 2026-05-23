<?php
/**
 * public/mentions_legales.php
 * ---------------------------------------------------------------------
 * Page statique : mentions légales, politique de confidentialité, RGPD.
 *
 * Obligatoire en Belgique pour tout site collectant des données
 * personnelles (RGPD + loi belge sur la protection de la vie privée).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$titre = 'Mentions légales et confidentialité';

require_once VIEWS_PATH . '/mentions_legales.php';
