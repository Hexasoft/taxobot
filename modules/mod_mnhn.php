<?php

/**
 * Module - Muséum national d'histoire naturelle (MNHN) - Paris
 *
 * Ce module permet de récupérer des informations sur un taxon à partir du site MNHN.
 * Il génère des liens externes et des liens internes pour le site, mais il ne génère aucune classification.
 *
 */

/**
 * Déclare le module mnhn en spécifiant ses fonctionnalités.
 * 
 * @return array|bool declare_module : voir modules.php
 */
function m_mnhn_init() {
    return declare_module(
        "mnhn",
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
function m_mnhn_infos(&$struct, $classif) {
    $rang = $struct['taxon']['rang'];

    // Vérifiez si le rang est ni "espece" ni "genre" ou absent
    if (!isset($rang) && $rang != 'espece' && $rang != 'genre') {
        return false;
    }

    $taxon = strtolower($struct['taxon']['nom']);
    $url = "https://science.mnhn.fr/taxon/";
    $cible = str_replace(" ", "/_", $taxon);
    // Construction de l'URL en fonction du rang
    if ($rang == 'espèce') {
        $url .= "species/";
    } elseif ($rang == 'genre') {
        $url .= "genus/";
    } else { return false; }

    $url .= $cible;

    // Échec récupération
    $ret = get_data($url);
    if ($ret === false) {
        logs("MNHN: échec de récupération réseau");
        return false;
    }

    // Taxon non trouvé
    $tmp = strpos($ret, '<h1>Etat HTTP 404 - Not Found</h1>');
    if ($tmp !== false) {
        logs("MNHN: taxon non trouvé");
        return false;
  }
  $struct['liens']['mnhn']['url'] = $url;
  $struct['liens']['mnhn']['id'] = $cible;
  $struct['liens']['mnhn']['rang'] = $rang;
  $struct['liens']['mnhn']['nom'] = $taxon;

  if (!$classif == true) { // syntaxe ? vers ($classif !== true) ou (!$classif) ?
    return true;
  }

  return false;
}


/**
 * Génère le modèle mnhn de la section "Voir aussi"
 * @param array $struct : correspond aux données récupérées par m_mnhn_infos()
 * @return mnhn : retourne le modèle généré à partir des informations sur un taxon.
 */
function m_mnhn_ext($struct) {
  $cdate = dates_recupere(); // Récupération de la date actuelle depuis outils.php
  if (!isset($struct['liens']['mnhn']['id'])) { // Vérification de la présence de l'identifiant mnhn dans la structure donnée
    return false;
  }
  $rang = $struct['liens']['mnhn']['rang'];
  $id = $struct['liens']['mnhn']['id'];

  if (isset($data['nom'])) {
    $description = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
  } else {
    $description = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
  }
  $mnhn = "{{MNHN | $rang | $id | $description | consulté le = $cdate}}";
  return $mnhn; // Retourne le modèle mnhn généré
} 

// Génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_mnhn_liens($struct) {
  if (isset($struct['liens']['mnhn']['id'])) {
    return "<a href='" . $struct['liens']['mnhn']['url'] .
           "'>MNHN</a>";
  } else {
    return false;
  }
}