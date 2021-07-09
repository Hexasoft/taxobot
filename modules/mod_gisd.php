<?php

/*
  Module pour gisd (non classification)
*/

// déclaration du module
function m_gisd_init() {
  return declare_module("gisd", false, false, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_gisd_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $t = str_replace(" ", "-", $taxon);
  $url = "http://www.iucngisd.org/gisd/speciesname/$t";
  $ret = get_data($url);
  if ($ret === false) {
    logs("GISD: echec de récupération réseau");
    return false;
  }
  $tst = strpos($ret, "not present yet in our archive.");
  if ($tst !== false) {
    logs("GISD: taxon non trouvé");
    return false;
  }

  // pas de gestion d'auteurs, ou même de nom
  $struct['liens']['gsid']['code'] = $t;
  $struct['liens']['gsid']['nom'] = $taxon;
  
  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_gisd_ext($struct) {
  $cdate = dates_recupere();
  if (!isset($struct['liens']['gisd'])) {
    return false;
  }
  $data = $struct['liens']['gisd'];
  if (isset($data['id'])) {
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    return "{{GISD | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
  } elseif (isset($data['code'])) {
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    return "{{GISD nom | " . $data['code'] . " | " . $cible . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_gisd_liens($struct) {
  if (isset($struct['liens']['gisd']['id'])) {
    return "<a href='http://www.iucngisd.org/gisd/species.php?sc=" . $struct['liens']['gisd']['id'] .
           "'>GISD</a>";
  } else {
    return false;
  }
}

