<?php

/**
 * Module - Animal Diversity Web (ADW)
 *
 * Ce module permet de récupérer des informations sur un taxon à partir du site ADW.
 * Il génère des liens externes et des liens internes pour le site, mais il ne génère aucune classification.
 *
 */

/**
 * Déclare le module ADW en spécifiant ses fonctionnalités.
 * 
 * @return array|bool declare_module : voir modules.php
 */
function m_adw_init() {
    return declare_module(
        "adw",
        false,
        true,
        true
    );
}

/**
 * Extrait les informations et les stocke dans une structure de données.
 *
 * @param array &$struct : cgstructure de données à remplir avec les informations.
 * @param bool $classif : indique si la classification du taxon doit être gérée.
 * @return bool True si les informations ont été récupérées avec succès, false sinon.
 * 
 */
function m_adw_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $url = "https://animaldiversity.org/accounts/";
  $cible = str_replace(" ", "_", $taxon);
  $url .= $cible . "/";
  //$url .= $cible . "/classification/";

  // Échec récupération
  $ret = get_data($url);
  if ($ret === false) {
    logs("ADW: échec de récupération réseau");
    return false;
  }
  
  // Taxon non trouvé
  $tmp = strpos($ret, ": Not Found<");
  if ($tmp !== false) {
    logs("ADW: taxon non trouvé");
    return false;
  }
  
  $lines = explode("\n", $ret);
  $citation = '';
  $nom = '';
  $auteurs = '';
  $premier_auteur = '';

  /** Extraction de la citation (auteur[s] et date) et formatage
   * @var string $citation : ligne "To cite this page: (...)" d'où on récupère des données pour le modèle ADW
   * @var $nb_auteurs : le formatage de $auteurs dépend du nombre d'auteur[s]
   * @var string $auteurs : auteur[s]
   * formats : 1 auteur = $premier_auteur ; 2 = $premier_auteur ($pamatches) + deuxième auteur ($almatches) ; 3 ou plus = $premier_auteur et al.
   * @var $date : YYYY ($dmatches)
   */
  foreach($lines as $idx => $line) {
    if (strpos($line, "To cite this page:") !== false) {
      if (isset($lines[$idx+1])) {
        $nextLine = $lines[$idx+1];
        $citation = trim($nextLine);
        // Premier auteur
        preg_match('/(?P<nom>\p{L}+),\s(?P<prenom1>[A-Z]\.)(?P<prenom2> [A-Z]\.)?/', $citation, $pamatches);
        $premier_auteur = $pamatches['nom'] . ', ' . $pamatches['prenom1'];
        if (!empty($pamatches['prenom2'])) {
            $premier_auteur .= ' ' . $pamatches['prenom2'];
        }
        // Autre[s] auteur[s]
        preg_match_all('/(and )?(?P<prenom1>[A-Z]\.)(?P<prenom2> [A-Z]\.)? (?P<nom>\p{L}+)/', $citation, $almatches);
        $nb_auteurs = count($almatches[1]);
        if ($nb_auteurs == 0) {
          $auteurs = $premier_auteur;
        } else {
          if ($nb_auteurs >= 2) {
            $auteurs = $premier_auteur . ' {{et al.}}';
          } elseif ($nb_auteurs == 1) {
            $auteurs = $premier_auteur . ' et ' . $almatches['nom'][1] . ' ' . $almatches['prenom1'][1];
            if (!empty($almatches['prenom2'])) {
              $auteurs .= ' ' . $pamatches['prenom2'];
            }
          } else {
            $auteurs =  $premier_auteur;
          }
        }
      }
      // Date
        preg_match('/([1-9][0-9][0-9][0-9])[.].*$/', $citation, $dmatches);
        $date = isset($dmatches[1]) ? $dmatches[1] : '';
        break;
    }  
  }
  
  // Stockage des données extraites
  if (!empty($citation)) {
    $struct['liens']['adw']['citation'] = $auteurs;
  }
  if (!empty($date)) {
    $struct['liens']['adw']['date'] = $date;
  } else {
    $struct['liens']['adw']['nom'] = $taxon; // on utilise le nom issu de la classification
  }
  $struct['liens']['adw']['id'] = $cible;

  if (!$classif == true) { // syntaxe ? vers ($classif !== true) ou (!$classif) ?
    return true;
  }

  // ADW ne peut être une source de classification
  return false;
}

/**
 * Génère le modèle ADW de la section "Voir aussi"
 * @param array $struct : correspond aux données récupérées par m_adw_infos()
 * @return adw : retourne le modèle généré à partir des informations sur un taxon, la citation (date et auteur) de la page ADW, etc.
 */
function m_adw_ext($struct) {
  $cdate = dates_recupere(); // Récupération de la date actuelle depuis outils.php
  if (!isset($struct['liens']['adw']['id'])) { // Vérification de la présence de l'identifiant ADW dans la structure donnée
    return false;
  }
  $data = $struct['liens']['adw'];
  $nomPageTaxon = $data['id'];
  $description = '';
  if (isset($data['nom'])) {
    $description = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
  } else {
    $description = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
  }

  $auteur = isset($data['citation']) ? $data['citation'] : '';
  $date = isset($data['date']) ? $data['date'] : '';
  
  $adw = "{{ADW | $nomPageTaxon | $description | consulté le=$cdate"; // Construction du modèle ADW et de ses paramètres, si non vides.
  
  /**
  * Fonctionnalité qui pourrait être proposée (cmd/web), générée par outils.php (par défaut ~ le nom du module, "adw")
  * ou correspondre à une concaténation "ADW" + nom[x] issu de $pamatches (premier auteur) ou "ADW" + $cdate
  */
  // if (!empty($ancre)) { $adw .= " | ancre=$ancre"; }
 
  if (!empty($auteur)) {
    $adw .= " | auteur=$auteur";
  }
  if (!empty($date)) {
    $adw .= " | date=$date";
  }
  
  $adw .= " }}"; // Fermeture du modèle ADW
  
  return $adw; // Retourne le modèle ADW généré
} 

// Génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_adw_liens($struct) {
  if (isset($struct['liens']['adw']['id'])) {
    return "<a href='https://animaldiversity.org/accounts/" . $struct['liens']['adw']['id'] .
           "'>ADW</a>";
  } else {
    return false;
  }
}
