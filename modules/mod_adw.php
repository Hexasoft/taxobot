<?php

/*
  Module pour adw (non classification)
*/

// déclaration du module
function m_adw_init() {
  return declare_module("adw", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_adw_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $url = "https://animaldiversity.org/accounts/";
  $cible = str_replace(" ", "_", $taxon);
  $url .= $cible . "/classification/";
  
  $ret = get_data($url);
  if ($ret === false) {
    logs("ADW: echec de récupération réseau");
    return false;
  }
  
  // non trouvé ?
  $tmp = strpos($ret, ": Not Found<");
  if ($tmp !== false) {
    logs("ADW: taxon non trouvé");
    return false;
  }
  
  $struct['liens']['adw']['nom'] = $taxon; // la flemme de cercher le nom local
  $struct['liens']['adw']['id'] = $cible;
  
  if (!$classif == true) {
    return true;
  }
  // ADW ne peut être une source de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_adw_ext($struct) {
  $cdate = dates_recupere();
  if (!isset($struct['liens']['adw'])) {
    return false;
  }
  $data = $struct['liens']['adw'];
  if (isset($data['nom'])) {
    $txt = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
  } else {
    $txt = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
  }
  if (isset($data['id'])) {
    return "{{ADW | " . $data['id'] . " | " . $txt . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_adw_liens($struct) {
  if (isset($struct['liens']['adw']['id'])) {
    return "<a href='https://animaldiversity.org/accounts/" . $struct['liens']['adw']['id'] .
           "'>ADW</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_adw_fin($struct) {
  return false;
}

