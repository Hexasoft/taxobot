<?php

/*
  Module pour col (non classification)
*/

// déclaration du module
function m_col_init() {
  return declare_module("col", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_col_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $url = "https://api.catalogueoflife.org/dataset/2296/nameusage/search?facet=rank&facet=issue&facet=status&facet=nomStatus&facet=nameType&facet=field&limit=50&offset=0&q=" .
          urlencode($taxon) . "&sortBy=taxonomic&status=_NOT_NULL&type=EXACT";
  $ret = get_data($url);
  if ($ret === false) {
    logs("CoL: echec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("CoL: Echec de décodage des données");
    return false;
  }
  
  if (!isset($res->result[0])) {
    logs("CoL: taxon non trouvé");
    return false;
  }
  
  $struct['liens']['col']['id'] = $res->result[0]->id;
  $struct['liens']['col']['nom'] = $res->result[0]->usage->name->scientificName;
  if (isset($res->result[0]->usage->name->authorship)) {
    $struct['liens']['col']['auteur'] = $res->result[0]->usage->name->authorship;
  }
  foreach($res->result as $r) {
    if (!isset($r->usage->accepted)) {
      $struct['liens']['col']['id'] = $r->id;
      $struct['liens']['col']['nom'] = $r->usage->name->scientificName;
      if (isset($r->usage->name->authorship)) {
        $struct['liens']['col']['auteur'] = $r->usage->name->authorship;
      }
      break;
    }
  
    if (isset($r->usage->name->status) and ($r->usage->name->status == "accepted")) {
      $struct['liens']['col']['id'] = $r->id;
      $struct['liens']['col']['nom'] = $r->usage->name->scientificName;
      if (isset($r->usage->name->authorship)) {
        $struct['liens']['col']['auteur'] = $r->usage->name->authorship;
      }
    }
  }
  
  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_col_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['col']['id'])) {
    $data = $struct['liens']['col'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{CatalogueofLife | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_col_liens($struct) {
  if (isset($struct['liens']['col']['id'])) {
    return "<a href='https://www.catalogueoflife.org/data/taxon/" . $struct['liens']['col']['id'] .
           "'>CoL</a>";
  } else {
    return false;
  }
}

// génération (le cas échéant) de contenus de fin d'article (catégories, portails…)
function m_col_fin($struct) {
  return false;
}

