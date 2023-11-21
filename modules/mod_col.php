<?php

/*
  Module pour col (non classification)
*/

// déclaration du module
function m_col_init() {
  return declare_module("col", false, true, true);
}

// table des rangs (à compléter)
$m_col_ranks = [
  "family" => "famille",
  "superfamily" => "super-famille",
  "subfamily" => "sous-famille",
  "superclass" => "super-classe",
  "class" => "classe",
  "subclass" => "sous-classe",
  "genus" => "genre",
  "subgenus" => "sous-genre",
  "species" => "espèce",
  "superclass" => "super-classe",
  "megaclass" => "super-classe",
  "gigaclass" => "super-classe",
  "parvphylum" => "micro-embranchement",
  "infraphylum" => "infra-embranchement",
  "subphylum" => "sous-embranchement",
  "phylum" => "embranchement",
  "kingdom" => "règne",
];

// détermine le règne
function m_col_regne($classif) {
  foreach($classif as $el) {
    if (isset($el->name)) {
      if ($el->name == "Animalia") {
        return "animal";
      } else if ($el->name == "Plantae") {
        return "végétal";
      } else if ($el->name == "Fungi") {
        return "champignon";
      } else if ($el->name == "Archaea") {
        return "archaea";
      } else if ($el->name == "Chromista") {
        return "protiste";
      } else if ($el->name == "Protozoa") {
        return "protiste";
      } else if ($el->name == "Bacteria") {
        return "bactérie";
      }
    }
  }

  return null;
}

// conversion de rang CoL → WP
function m_col_rang($rang) {
  global $m_col_ranks;

  if (isset($m_col_ranks[$rang])) {
    return $m_col_ranks[$rang];
  }
  return "non classé";
}

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

  $trouve = false;
  $rbundle = [];
  $classif = false;
  // si on trouve directement (le bon nom, 'accepted')
  foreach($res->result as $r) {
    if (($r->usage->status == 'accepted') and ($r->usage->name->scientificName == $taxon)) {
      $bundle = [];
      $bundle['id'] = $r->id;
      $bundle['nom'] = $r->usage->name->scientificName;
      if (isset($r->usage->name->authorship)) {
        $bundle['auteur'] = $r->usage->name->authorship;
      }
      if (isset($r->usage->name->rank)) {
        $bundle['rang'] = m_col_rang($r->usage->name->rank);
      }
      $trouve = true;
      $rbundle[] = $bundle;
      $classif = $r->classification;
      break;
    }
  }

  // si non trouvé on tente de suivre les synonymes
  if (!$trouve) {
    foreach($res->result as $r) {
      if (($r->usage->name->scientificName == $taxon) and isset($r->usage->accepted) and
          ($r->usage->accepted->status == 'accepted')) {
        $bundle = [];
        $bundle['id'] = $r->usage->accepted->id;
        $bundle['nom'] = $r->usage->accepted->name->scientificName;
        if (isset($r->usage->accepted->name->authorship)) {
          $bundle['auteur'] = $r->usage->accepted->name->authorship;
        }
        if (isset($r->usage->name->rank)) {
          $bundle['rang'] = m_col_rang($r->usage->name->rank);
        }
        $bundle['syn'] = true;
        $trouve = true;
        $rbundle[] = $bundle;
        $classif = $r->classification;
      }
    }
  }

  // si rien trouvé et que 'inclure-invalides' on prend le premier qui a le bon nom scientifique
  if (!$trouve and get_config('inclure-invalides')) {
    foreach($res->result as $r) {
      if ($r->usage->name->scientificName == $taxon) {
        $bundle = [];
        $bundle['id'] = $r->id;
        $bundle['nom'] = $r->usage->name->scientificName;
        if (isset($r->usage->name->authorship)) {
          $bundle['auteur'] = $r->usage->name->authorship;
        }
        if (isset($r->usage->name->rank)) {
          $bundle['rang'] = m_col_rang($r->usage->name->rank);
        }
        $trouve = true;
        $rbundle[] = $bundle;
        $classif = $r->classification;
        break;
      }
    }
  }

  // si 'juste-ext' et qu'on a un un règne on le force
  if (isset($struct['juste-ext']) and $struct['juste-ext'] and ($classif !== false)) {
    $tmp = m_col_regne($classif);
    if ($tmp) {
      $struct['regne'] = $tmp;
    }
  }

  // retour
  if (!$trouve) {
    return false;
  }
  $struct['liens']['col'] = $rbundle;

  if (!$classif) {
    return true;
  }
  // on ne fait pas la classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_col_ext($struct) {
  $cdate = dates_recupere();
  if (!isset($struct['liens']['col'])) {
    return false;
  }
  if (!isset($struct['liens']['col'][0])) {
    // ce n'est pas une liste, on la met sous forme de liste
    $struct['liens']['col'][0] = $struct['liens']['col'];
  }

  $res = [];
  foreach($struct['liens']['col'] as $data) {
    if (!isset($data['id']) || strlen($data['id']) > 6) {
      continue;
    }
    $cible = wp_met_italiques($data['nom'],
         isset($data['rang'])?$data['rang']:$struct['taxon']['rang'],
         $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    if (isset($data['syn']) and $data['syn']) {
      $cible .= " <small>(synonymie)</small>";
    }
    $res[] = "{{CatalogueofLife | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
  }
  if (empty($res)) {
    return false;
  }
  return $res;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_col_liens($struct) {
  if (!isset($struct['liens']['col'])) {
    return false;
  }
  if (isset($struct['liens']['col']['id'])) {
    // ce n'est pas une liste, on la met sous forme de liste
    $struct['liens']['col'][0] = $struct['liens']['col'];
  }
  $res = [];
  foreach($struct['liens']['col'] as $data) {
    if (!isset($data['id']) || strlen($data['id']) > 6) {
      continue;
    }
    $res[] = "<a href='https://www.catalogueoflife.org/data/taxon/" . $data['id'] .
             "'>CoL</a>";
  }
  if (empty($res)) {
    return false;
  }
  return $res;
}