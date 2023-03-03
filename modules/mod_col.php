<?php

/*
  Module pour col (non classification)
*/

// déclaration du module
function m_col_init() {
  return declare_module("col", false, true, true);
}


// curl 'https://api.checklistbank.org/dataset/9880/nameusage/suggest?fuzzy=false&limit=25&q=Ecliptopera' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/110.0' -H 'Accept: application/json, text/plain, */*' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'Origin: https://www.catalogueoflife.org' -H 'Connection: keep-alive' -H 'Referer: https://www.catalogueoflife.org/' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: cross-site'



/*
  TODO : il faut récupérer le numéro du "dataset" qui visiblement évolue au cours du temps
         et ne contient pas les mêmes identifiants
*/


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_col_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $ret = get_data("https://www.catalogueoflife.org");
  if ($ret === false) {
    logs("CoL: Échec de récupération de la page d'accueil");
    return false;
  }
  
  // il faut récupérer le numéro du dataset
  $tbl = explode("\n", $ret);
  $dataset = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, "catalogueKey:") !== false) {
      $tmp = preg_replace("/^.*catalogueKey:[ ]*'/", "", $ligne);
      $tmp = preg_replace("/'.*$/", "", $tmp);
      if (is_numeric($tmp)) {
        $dataset = $tmp;
        break;
      }
    }
  }
  if (!$dataset) {
    logs("CoL: Identifiant du 'dataset' non trouvé");
    return false;
  }

  $url = "https://api.checklistbank.org/dataset/$dataset/nameusage/search?" .
         "facet=rank&facet=issue&facet=status&facet=nomStatus&facet=nameType&facet=field&limit=50&offset=0&q=" .
          str_replace(" ", "%20", $taxon) . "&sortBy=taxonomic&status=_NOT_NULL&type=EXACT";
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

