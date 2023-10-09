<?php

/*
  Module pour mdd (non classification)
*/

// déclaration du module
function m_ncbi_init() {
  return declare_module("ncbi", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_ncbi_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // on récupère la page de recherche
  $nom = str_replace(" ", "%20", $taxon);
  $url = "https://www.ncbi.nlm.nih.gov/taxonomy?term=" . $nom . "%5BScientific%20Name%5D";
  $ret = get_data($url);
  if ($ret === false) {
    logs("NCBI: problème réseau");
    return false;
  }
  // parcours
  $tbl = explode("\n", $ret);
  $id = false;
  foreach($tbl as $l) {
    if (strpos($l, "ncbi_uid=") !== false) {
      $x = preg_replace('/^.*ncbi_uid=/', '', $l);
      $x = preg_replace('/.amp;.*$/', '', $x);
      $id = $x;
      break;
    }
  }
  
  if ($id !== false) {
    $el = [];
    $el['nom'] = $taxon;
    $el['id'] = $id;
    $el['rang'] = $struct['taxon']['rang'];
    $struct['liens']['ncbi'] = $el;
  } else {
    logs("NCBI: taxon non trouvé");
    return false;
  }
  
  if (!$classif) {
    return true;
  }
  // pas classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_ncbi_ext($struct) {
  if (isset($struct['liens']['ncbi']['id'])) {
    $data = $struct['liens']['ncbi'];
    $cdate = dates_recupere();
    $cible = wp_met_italiques($data['nom'], $data['rang'], $struct['regne'], false, true);
    return "{{NCBI | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_ncbi_liens($struct) {
  if (isset($struct['liens']['ncbi']['id'])) {
    $data = $struct['liens']['ncbi'];
    return "<a href='https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=" .
             $data['id'] . "'>NCBI</a>";
  } else {
    return false;
  }
}

