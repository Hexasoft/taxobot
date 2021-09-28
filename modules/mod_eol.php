<?php

/*
  Module pour EoL (non classification)
*/

// déclaration du module
function m_eol_init() {
  return declare_module("eol", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_eol_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $url = "https://eol.org/fr/autocomplete/" . urlencode($taxon);
  $ret = get_data($url);
  if ($ret === false) {
    logs("EoL: echec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("EoL: Echec de décodage des données");
    return false;
  }
  if (empty($res)) {
    logs("EoL: taxon non trouvé");
    return false;
  }
  // on parcours les entrées
  $found = false;
  foreach($res as $el) {
    if (isset($el->name) and ($el->name == $taxon)) {
      $found = $el;
      break;
    }
  }
  if ($found === false) {
    logs("EoL: taxon non trouvé");
    return false;
  }
  
  $struct['liens']['eol']['id'] = $found->id;
  $struct['liens']['eol']['nom'] = $found->name;
  
  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_eol_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['eol']['id'])) {
    $data = $struct['liens']['eol'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{EOL | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_eol_liens($struct) {
  if (isset($struct['liens']['eol']['id'])) {
    return "<a href='https://eol.org/fr/pages/" . $struct['liens']['eol']['id'] .
           "'>EoL</a>";
  } else {
    return false;
  }
}

