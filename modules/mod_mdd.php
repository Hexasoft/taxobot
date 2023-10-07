<?php

/*
  Module pour mdd (non classification)
*/

// déclaration du module
function m_mdd_init() {
  return declare_module("mdd", false, true, ['animal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_mdd_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $taxon2 = str_replace(" ", "_", $taxon);
  $tmp = explode(" ", $taxon);
  if (count($tmp) != 2) {
    logs("MDD: ne s'applique qu'aux espèces");
    return false;
  }
  // on récupère la page de recherche
  $url = "https://www.mammaldiversity.org/explore.html";
  $ret = get_data($url);
  if ($ret === false) {
    logs("MDD: problème réseau");
    return false;
  }
  // parcours
  $tbl = explode("\n", $ret);
  $id = false;
  $trouve = false;
  foreach($tbl as $l) {
    if (strpos($l, "speciesID") !== false) {
      $x = preg_replace('/^.*value="/', '', $l);
      $x = preg_replace('/".*$/', '', $x);
      $id = $x;
      continue;
    }
    if (strpos($l, $taxon2) !== false) {
      $trouve = true;
      break;
    }
  }
  
  if ($trouve) {
    $el = [];
    $el['nom'] = $taxon;
    $el['id'] = $id;
    // TODO : ajouter d'autres choses, mais il faut récupérer la page cible
    $struct['liens']['mdd'] = $el;
  } else {
    logs("MDD: taxon non trouvé");
    return false;
  }
  
  if (!$classif) {
    return true;
  }
  // pas classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_mdd_ext($struct) {
  if (isset($struct['liens']['mdd']['id'])) {
    $data = $struct['liens']['mdd'];
    $cdate = dates_recupere();
    $tmp = explode(" ", trim($data['nom']));
    $pre = implode(" | ", $tmp);
    return "{{MDD | $pre | " . $data['id'] . " | ''" . $data['nom'] . "'' | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_mdd_liens($struct) {
  if (isset($struct['liens']['mdd']['id'])) {
    $data = $struct['liens']['mdd'];
    return "<a href='https://www.mammaldiversity.org/explore.html#species-id=" .
             $struct['liens']['mdd']['id'] . "'>MDD</a>";
  } else {
    return false;
  }
}

