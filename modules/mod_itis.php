<?php

/*
  Module pour itis (non classification)
*/

// déclaration du module
function m_itis_init() {
  // gère la classification, les liens externes, tous les domaines, priorité juste en dessous de GBIF
  return declare_module("itis", true, true, true, 998);
}

$itis_regnes = [
  'Animalia' => 'animal',
  'Archaea' => 'archaea',
  'Bacteria' => 'bactérie',
  'Fungi' => 'champignon',
  'Plantae' => 'végétal',
  'Protozoa' => 'protiste',
  'Chromista' => 'algue',
];
// retourne la conversion de "regne" de ITIS
function itis_cherche_regne($regne) {
  global $itis_regnes;
  if (!isset($itis_regnes[$regne])) {
    return 'neutre';
  }
  return $itis_regnes[$regne];
}

$itis_wp = [
  'Clade' => 'clade',
  'Type' => 'type',
  'Group' => 'groupe',
  'Unspecified' => 'non-classé',
  'Subform' => 'sous-forme',
  'Form' => 'forme',
  'Variety' => 'variété',
  'Pathovar' => 'pathovar',
  'Cultivar' => 'cultivar',
  'Subspecies' => 'sous-espèce',
  'Hybrid' => 'hybride',
  'Species' => 'espèce',
  'Subserie' => 'sous-série',
  'Serie' => 'série',
  'Subsection' => 'sous-section',
  'Section' => 'section',
  'Subgenus' => 'sous-genre',
  'Genus' => 'genre',
  'Subtribe' => 'sous-tribu',
  'Tribe' => 'tribu',
  'Supertribe' => 'super-tribu',
  'Infratribe' => 'infra-tribu',
  'Subfamily' => 'sous-famille',
  'Family' => 'famille',
  'null2' => 'épifamille',
  'Superfamily' => 'super-famille',
  'Microorder' => 'micro-ordre',
  'Infraorder' => 'infra-ordre',
  'Suborder' => 'sous-ordre',
  'Order' => 'ordre',
  'Superorder' => 'super-ordre',
  'Subcohort' => 'sous-cohorte',
  'Cohort' => 'cohorte',
  'Supercohort' => 'super-cohorte',
  'null1' => 'subter-classe',
  'Infraclass' => 'infra-classe',
  'Subclass' => 'sous-classe',
  'Class' => 'classe',
  'Superclass' => 'super-classe',
  'Microphylum' => 'micro-embranchement',
  'Infraphylum' => 'infra-embranchement',
  'Subphylum' => 'sous-embranchement',
  'Phylum' => 'embranchement',
  'Superphylum' => 'super-embranchement',
  'Infradivision' => 'infra-division',
  'Subdivision' => 'sous-division',
  'Division' => 'division',
  'Superdivision' => 'super-division',
  'Infrakingdom' => 'infra-règne',
  'null' => 'rameau',
  'Subkingdom' => 'sous-règne',
  'Kingdom' => 'règne',
  'Superkingdom' => 'super-règne',
  'Subdomain' => 'sous-domaine',
  'Domain' => 'domaine',
  'Superdomain' => 'super-domaine',
  'Empire' => 'empire',
];
// Étaient définis deux fois (volontaire ?)
//'Kingdom' => 'royaume',
// 'Subkingdom' => 'sous-royaume',

// retourne le rang de ITIS
function itis_cherche_rang($rang) {
  global $itis_wp;

  if (!isset($itis_wp[$rang])) {
    return 'NOTFOUND';
  }
  return $itis_wp[$rang];
}

// récupère et retourne les données sur un taxon
function itis_data_taxon($tsn, $auteur=false) {
  $result = [];

  // le rang
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getTaxonomicRankNameFromTSN?tsn=" .
       $tsn;
  $ret = file_get_contents($url);
  $_res = get_xml($ret);
  if ($_res !== null) {
    $res = json_decode(json_encode($_res), true);
    if (isset($res['return']['rankName'])) {
      $result['rang'] = itis_cherche_rang($res['return']['rankName']);
    } else {
      logs("ITIS: rang du taxon non trouvé (2)");
      return false;
    }
  } else {
    logs("ITIS: rang du taxon non trouvé");
    return false;
  }
  // le nom
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getScientificNameFromTSN?tsn=" .
       $tsn;
  $ret = file_get_contents($url);
  $_res = get_xml($ret);
  if ($_res !== null) {
    $res = json_decode(json_encode($_res), true);
    if (isset($res['return']['combinedName'])) {
      $result['nom'] = $res['return']['combinedName'];
    } else {
      logs("ITIS: nom du taxon non trouvé (2)");
      return false;
    }
  } else {
    logs("ITIS: nom du taxon non trouvé");
    return false;
  }
  // l'auteur si demandé
  if (!$auteur) {
    return $result;
  }
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getTaxonAuthorshipFromTSN?tsn=" .
       $tsn;
  $ret = file_get_contents($url);
  $_res = get_xml($ret);
  if ($_res !== null) {
    $res = json_decode(json_encode($_res), true);
    if (isset($res['return']['authorship']) and !empty($res['return']['authorship'])) {
      $result['auteur'] = $res['return']['authorship'];
    } else {
      logs("ITIS: auteur du taxon non trouvé  (2)");
      unset($result['auteur']);
    }
  } else {
    logs("ITIS: auteur du taxon non trouvé chez ITIS");
    unset($result['auteur']);
  }
  return $result;
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_itis_infos(&$struct, $classif) {
  $suivre_synonymes = get_config('suivre-synonymes');
  $taxon = $struct['taxon']['nom'];

  get_data("https://www.itis.gov");

  $url = "https://www.itis.gov/ITISWebService/services/ITISService/searchByScientificName?srchKey=" .
       urlencode($taxon);
  $ret = get_data($url);
  $_res = get_xml($ret);

  if ($_res === null) {
    logs("ITIS: echec de récupération ou réponse invalide");
    return false;
  }
  $res = json_decode(json_encode($_res), true);

  $r = $res['return']['scientificNames'];

  if (!isset($r)) {
      logs("ITIS: taxon non trouvé");
      return false;
  }

  if (count($r) > 1) {
    // Si scientificNames contient plusieurs éléments, on choisit le taxon renseigné
    foreach ($r as $sn) {
      if (isset($sn['combinedName']) && $sn['combinedName'] == $taxon) {
          $r = $sn;
          break;
      }
    }
  }

  // Règne si classif
  if ($classif && isset($r['kingdom'])) {
    $kingdom = $r['kingdom'];
    $struct['regne'] = itis_cherche_regne($kingdom);
  }

  // ID, nom, auteur
  $struct['liens']['itis']['id'] = $r['tsn'];
  $struct['liens']['itis']['nom'] = isset($r['combinedName']) ? $r['combinedName'] : $taxon;
  if (isset($r['author']) and !empty($r['author'])) {
    $struct['liens']['itis']['auteur'] = $r['author'];
  }

  // on regarde si c'est un synonyme
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getAcceptedNamesFromTSN?tsn=" .
       $struct['liens']['itis']['id'];
  $ret = file_get_contents($url);
  $_res = get_xml($ret);
  if ($_res === null) {
    logs("ITIS: echec de AcceptedNames");
    return !$classif; // pas grave si seulement liens externes
  }
  $res = json_decode(json_encode($_res), true);
  if (isset($res['return']['acceptedNames']['acceptedName']) and
      !empty($res['return']['acceptedNames']['acceptedName'])) {
    // c'est un synonyme : si demandé on se relance (seulement si !$ext)
    if (!$classif) {
      $struct['liens']['itis']['synonyme'] = true;
      $struct['liens']['itis']['nom-synonyme'] = $res['return']['acceptedNames']['acceptedName'];
      $struct['liens']['itis']['id-synonyme'] = $res['return']['acceptedNames']['acceptedTsn'];
      return true;
    }
    if ($suivre_synonymes) {
      // on note la redirection
      $struct['redirection']['nom'] = $struct['taxon']['nom'];
      // on change le taxon
      $struct['taxon']['nom'] = $res['return']['acceptedNames']['acceptedName'];
      // on se relance sur le nouveau nom
      return m_itis_infos($struct, !$classif);
    }
  }
  if (!$classif) {
    return true;
  }
  // note : il faudrait fixer taxon/nom à partir des données de $ret[], mais ici c'est inutile
  //        car ITIS n'accepte pas les recherches approximatives

  // récupération des informations de classification
  $ret = itis_data_taxon($struct['liens']['itis']['id'], true);
  if ($ret === false) {
    logs("ITIS: echec de récupération d'informations sur le taxon");
    return false;
  }

  if (isset($ret['auteur'])) {
    $struct['taxon']['auteur'] = $ret['auteur'];
  }
  $struct['taxon']['rang'] = $ret['rang'];
  $struct['classification'] = 'ITIS';
  $struct['classification-taxobox'] = 'itis';
  // le "règne"

  // classification
  $liste = [];
  $curTsn = $struct['liens']['itis']['id'];
  while (true) {
    // on cherche le parent
    $url = "https://www.itis.gov/ITISWebService/services/ITISService/getParentTSNFromTSN?tsn=" .
       $curTsn;
    $ret = file_get_contents($url);
    $res = get_xml($ret);
    if ($res === null) {
      break; // fin de séquence
    }
    if (!isset($res->return->parentTsn)) {
      break; // idem
    }
    if (empty($res->return->parentTsn)) {
      break;
    }
    // infos sur ce taxon
    $cur = itis_data_taxon($res->return->parentTsn);
    if ($cur === false) {
      logs("ITIS: echec de récupération d'un taxon intermédiaire");
      break;
    }
    // si kingdom on cherche le "règne" WP (charte)
    if (($cur['rang'] == 'royaume') or ($cur['rang'] == 'règne')) {
      // on ne l'ajoute pas
    } else {
      // on l'ajoute (mais pas le règne)
      $liste[] = $cur;
    }

    // on monte
    $curTsn = $res->return->parentTsn;
  }
  $struct['rangs'] = $liste;

  // sous-taxons
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getHierarchyDownFromTSN?tsn=" .
       $struct['liens']['itis']['id'];
  $ret = file_get_contents($url);
  $_res = get_xml($ret);
  if ($_res !== null) {
    $liste = [];
    $res = json_decode(json_encode($_res), true);
    if (isset($res['return']['hierarchyList'])) {
      if (is_array($res['return']['hierarchyList'])) {
        $tbl = $res['return']['hierarchyList'];
      } else {
        $tbl = [ $res['return']['hierarchyList'] ];
      }
      foreach($tbl as $sub) {
        if (isset($sub['taxonName']) and !empty($sub['taxonName'])) {
          $tmp = [];
          $tmp['nom'] = $sub['taxonName'];
          if (isset($sub['author']) and !empty($sub['author'])) {
            $tmp['auteur'] = $sub['author'];
          }
          if (isset($sub['rankName']) and !empty($sub['rankName'])) {
            $tmp['rang'] = cherche_rang($sub['rankName'], "ITIS");
          }
          $liste[] = $tmp;
        }
      }
    }
    if (!empty($liste)) {   
      $struct['sous-taxons']['liste'] = $liste;
      $struct['sous-taxons']['source'] = 'ITIS';
    }
  }

  // synonymes
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getSynonymNamesFromTSN?tsn=" .
       $struct['liens']['itis']['id'];
  $ret = file_get_contents($url);
  $res = get_xml($ret);
  if ($res !== null) {
    $liste = [];
    if (isset($res->return->synonyms)) {
      if (is_array($res->return->synonyms)) {
        $tbl = $res->return->synonyms;
      } else {
        $tbl = [ $res->return->synonyms ];
      }
      foreach($tbl as $syn) {
        if (isset($syn->sciName)) {
          $tmp = [];
          $tmp['nom'] = $syn->sciName;
          if (isset($syn->author)) {
            $tmp['auteur'] = $syn->author;
          }
          $liste[] = $tmp;
        }
      }
    }
    if (!empty($liste)) {
      $struct['synonymes']['liste'] = $liste;
      $struct['synonymes']['source'] = 'ITIS';
    }
  }

  // noms en français
  $url = "https://www.itis.gov/ITISWebService/services/ITISService/getCommonNamesFromTSN?tsn=" .
       $struct['liens']['itis']['id'];
  $ret = file_get_contents($url);
  $res = get_xml($ret);
  if ($res !== null) {
    $liste = [];
    if (isset($res->return->commonNames)) {
      if (is_array($res->return->commonNames)) {
        $tbl = $res->return->commonNames;
      } else {
        $tbl = [ $res->return->commonNames ];
      }
      foreach($tbl as $name) {
        if (isset($name->language) and ($name->language == "French") and isset($name->commonName)) {
          $liste[] = $name->commonName;
        }
      }
    }
    if (!empty($liste)) {
      $struct['vernaculaire']['ITIS'] = $liste;
    }
  }

  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_itis_ext($struct) {
  $cdate = dates_recupere();

  if (isset($struct['liens']['itis']['id'])) {
    $data = $struct['liens']['itis'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    if (isset($data['synonyme']) and $data['synonyme']) {
      return "{{ITIS | " . $data['id'] . " | " . $cible . " | nv | " . "consulté le=$cdate }}";
    } else {
      return "{{ITIS | " . $data['id'] . " | " . $cible . " | " . "consulté le=$cdate }}";
    }
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_itis_liens($struct) {
  if (isset($struct['liens']['itis']['id'])) {
    return "<a href='https://www.cbif.gc.ca/acp/fra/siti/regarder?tsn=" .
           $struct['liens']['itis']['id'] . "'>ITIS</a>";
  } else {
    return false;
  }
}