<?php

/*
  Module de classification GBIF
*/


$gbif_markers = [
  "dom." => "DOMAIN",
  "superreg." => "SUPERKINGDOM",
  "reg." => "KINGDOM",
  "subreg." => "SUBKINGDOM",
  "infrareg." => "INFRAKINGDOM",
  "superphyl." => "SUPERPHYLUM",
  "phyl." => "PHYLUM",
  "subphyl." => "SUBPHYLUM",
  "infraphyl." => "INFRAPHYLUM",
  "supercl." => "SUPERCLASS",
  "cl." => "CLASS",
  "subcl." => "SUBCLASS",
  "infracl." => "INFRACLASS",
  "parvcl." => "PARVCLASS",
  "superleg." => "SUPERLEGION",
  "leg." => "LEGION",
  "subleg." => "SUBLEGION",
  "infraleg." => "INFRALEGION",
  "supercohort" => "SUPERCOHORT",
  "cohort" => "COHORT",
  "subcohort" => "SUBCOHORT",
  "infracohort" => "INFRACOHORT",
  "magnord." => "MAGNORDER",
  "superord." => "SUPERORDER",
  "grandord." => "GRANDORDER",
  "ord." => "ORDER",
  "subord." => "SUBORDER",
  "infraord." => "INFRAORDER",
  "parvord." => "PARVORDER",
  "superfam." => "SUPERFAMILY",
  "fam." => "FAMILY",
  "subfam." => "SUBFAMILY",
  "infrafam." => "INFRAFAMILY",
  "supertrib." => "SUPERTRIBE",
  "trib." => "TRIBE",
  "subtrib." => "SUBTRIBE",
  "infratrib." => "INFRATRIBE",
  "supragen." => "SUPRAGENERIC_NAME",
  "gen." => "GENUS",
  "subgen." => "SUBGENUS",
  "infragen." => "INFRAGENUS",
  "sect." => "SECTION",
  "subsect." => "SUBSECTION",
  "ser." => "SERIES",
  "subser." => "SUBSERIES",
  "infrageneric" => "INFRAGENERIC_NAME",
  "agg." => "SPECIES_AGGREGATE",
  "sp." => "SPECIES",
  "infrasp." => "INFRASPECIFIC_NAME",
  "grex" => "GREX",
  "subsp." => "SUBSPECIES",
  "convar." => "CONVARIETY",
  "infrasubsp." => "INFRASUBSPECIFIC_NAME",
  "prol." => "PROLES",
  "race" => "RACE",
  "natio" => "NATIO",
  "ab." => "ABERRATION",
  "morph" => "MORPH",
  "var." => "VARIETY",
  "subvar." => "SUBVARIETY",
  "f." => "FORM",
  "subf." => "SUBFORM",
  "pv." => "PATHOVAR",
  "biovar" => "BIOVAR",
  "chemovar" => "CHEMOVAR",
  "morphovar" => "MORPHOVAR",
  "phagovar" => "PHAGOVAR",
  "serovar" => "SEROVAR",
  "chemoform" => "CHEMOFORM",
  "f.sp." => "FORMA_SPECIALIS",
  "cv." => "CULTIVAR",
  "strain" => "STRAIN",
];

$gbif_wp = [
  'CLADE' => 'clade',
  'TYPE' => 'type',
  'GROUP' => 'groupe',
  'UNRANKED' => 'non-classé',
  'SUBFORM' => 'sous-forme',
  'FORM' => 'forme',
  'VARIETY' => 'variété',
  'PATHOVAR' => 'pathovar',
  'CULTIVAR' => 'cultivar',
  'SUBSPECIES' => 'sous-espèce',
  'HYBRID' => 'hybride',
  'SPECIES' => 'espèce',
  'SUBSERIE' => 'sous-série',
  'SERIE' => 'série',
  'SUBSECTION' => 'sous-section',
  'SECTION' => 'section',
  'SUBGENUS' => 'sous-genre',
  'GENUS' => 'genre',
  'SUBTRIBE' => 'sous-tribu',
  'TRIBE' => 'tribu',
  'SUPERTRIBE' => 'super-tribu',
  'INFRATRIBE' => 'infra-tribu',
  'SUBFAMILY' => 'sous-famille',
  'FAMILY' => 'famille',
  'SUPERFAMILY' => 'super-famille',
  'MICROORDER' => 'micro-ordre',
  'INFRAORDER' => 'infra-ordre',
  'SUBORDER' => 'sous-ordre',
  'ORDER' => 'ordre',
  'SUPERORDER' => 'super-ordre',
  'SUBCOHORT' => 'sous-cohorte',
  'COHORT' => 'cohorte',
  'SUPERCOHORT' => 'super-cohorte',
  'PARVCLASS' => 'subter-classe',
  'INFRACLASS' => 'infra-classe',
  'SUBCLASS' => 'sous-classe',
  'CLASS' => 'classe',
  'SUPERCLASS' => 'super-classe',
  'MICROPHYLUM' => 'micro-embranchement',
  'INFRAPHYLUM' => 'infra-embranchement',
  'SUBPHYLUM' => 'sous-embranchement',
  'PHYLUM' => 'embranchement',
  'SUPERPHYLUM' => 'super-embranchement',
  'INFRADIVISION' => 'infra-division',
  'SUBDIVISION' => 'sous-division',
  'DIVISION' => 'division',
  'INFRAKINGDOM' => 'infra-règne',
  'SUBKINGDOM' => 'sous-règne',
  'KINGDOM' => 'règne',
  'SUBDOMAIN' => 'sous-domaine',
  'DOMAIN' => 'domaine',
  'SUPERDOMAIN' => 'super-domaine',
  'EMPIRE' => 'empire',
  'KINGDOM' => 'royaume',
  'SUBKINGDOM' => 'sous-royaume',
  'NOTFOUND' => 'NOTFOUND',
];

// liste des "règnes" et traduction selon WP
$gbif_regnes = [
  'Animalia' => 'animal',
  'Archaea' => 'archaea',
  'Bacteria' => 'bactérie',
  'Fungi' => 'champignon',
  'Plantae' => 'végétal',
  'Viruses' => 'virus',
  'Incertae sedis' => 'neutre',
  'Protozoa' => 'protiste',
  'Chromista' => 'algue',
];

// retourne le nom du modèle Bioref associé à GBIF (théoriquement peut dépendre du rang)
function gbif_bioref($rang=null) {
  return "GBIF";
}
// retourne le nom de la classification pour Taxobox début
function gbif_classif() {
  return "GBIF";
}

// retourne le règne à partir de la donnée GBIF
function gbif_cherche_regne($regne) {
  global $gbif_regnes;
  if (!isset($gbif_regnes[$regne])) {
    return 'neutre';
  }
  return $gbif_regnes[$regne];
}

// retourne le rang WP associé
function gbif_cherche_rang($rang) {
  global $gbif_wp;
  
  if (!isset($gbif_wp[$rang])) {
    return 'NOTFOUND';
  }
  return $gbif_wp[$rang];
}

// retourne le rang GBIF à partir du "marqueur" (un autre nom)
function gbif_marqueur_rang($marker) {
  global $gbif_markers;
  if (!isset($gbif_markers[$marker])) {
    return 'NOTFOUND';
  }
  return $gbif_markers[$marker];
}

// données dédiées à un taxon
function gbif_taxon_info($id, $name="<ndef>", $deja=0) {
  $url = "https://api.gbif.org/v1/species/$id/name";
  $ret = get_data($url);
  // erreur CURL
  
  if ($ret === false) {
    if ($deja >= 3) {
      logs("GBIF: erreur réseau (id=$id, name=$name)");
      return false;
    } else {
      sleep(2);
      return gbif_taxon_info($id, $name, $deja+1);
    }
  }
  $cur = json_decode($ret);
  if ($cur === null) {
    logs("GBIF: erreur de décodage des informations GBIF");
    return false;
  }
  
  $result = [];
  if (isset($cur->canonicalNameWithMarker)) {
    $result['nom'] = $cur->canonicalNameWithMarker;
  } elseif (isset($cur->canonicalName)) {
    $result['nom'] = $cur->canonicalName;
  }
  $tmp = $cur->canonicalNameComplete;
  $lng = strlen($result['nom']);
  $result['auteur'] = substr($tmp, $lng+1);
  if (isset($cur->rank)) {
    $result['rang'] = gbif_cherche_rang($cur->rank);
  } else if (isset($cur->rankMarker)) {
    $buf = gbif_marqueur_rang($cur->rankMarker);
    if ($buf != 'NOTFOUND') {
      $result['rang'] = gbif_cherche_rang($buf);
    }
  }

  return $result;
}


// déclaration du module
function m_gbif_init() {
  // gère la classification, les liens externes, accepte tous les domaines,
  // priorité max, et classification par défaut
  return declare_module("gbif", true, true, true, 999, true);
}

// récupère les données générales liées à GBIF. Si $classif=TRUE récupère aussi les données de classification
function m_gbif_infos(&$struct, $classif) {
  global $gbif_wp;
  // les options dont on a besoin
  $suivre_synonymes = get_config("suivre-synonymes");
  $taxon = $struct['taxon']['nom'];
  
  // on effectue l'appel
  $url = "https://api.gbif.org/v1/species?datasetKey=d7dddbf4-2cf0-4f39-9b2a-bb099caae36c&name=" .
         urlencode($taxon);
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    logs("Erreur réseau pour GBIF");
    return false;
  }
  
  $_cur = json_decode($ret);
  if ($_cur === null) {
    logs("Erreur de décodage des informations GBIF");
    return false;
  }

  if (empty($_cur->results)) {
    logs("Taxon non trouvé chez GBIF");
    return false;
  }
  
  // on parcours pour trouver le "bon" taxon
  $cur = false;
  foreach($_cur->results as $r) {
    if ($r->taxonomicStatus == "ACCEPTED") {
      $cur = $r;
      break;
    }
  }
  if ($cur === false) {
    logs("Taxon trouvé chez GBIF en 'ACCEPTED'");
    if (!$suivre_synonymes) {
      return false;
    }
  }
  
  // si false on cherche l'entrée cible (suivre_synonyme)
  if ($cur === false) {
    foreach($_cur->results as $r) {
      $cur = $r;
      break;
    }
  }
  
  // données lien externe
  $struct['liens']['gbif']['id'] = $cur->key;
  $tmp = gbif_taxon_info($cur->key);
  $struct['liens']['gbif']['auteur'] = $tmp['auteur']; // trim($cur->authorship);
  $struct['liens']['gbif']['nom'] = $tmp['nom']; // trim($cur->canonicalName);
  if (isset($tmp['rang'])) {
    $struct['liens']['gbif']['rang'] = $tmp['rang'];
  }
  
  // si le taxon est un synonyme, et qu'on demande à suivre les synonymes,
  // on reboucle
  if (isset($cur->acceptedKey) and ($cur->acceptedKey != $cur->key)) {
    if (!$classif) {
      $struct['liens']['gbif']['synonyme'] = true;
      return true;
    }
    if ($suivre_synonymes) {
      $tmp = gbif_taxon_info($cur->acceptedKey);
      if ($tmp === false) {
        logs("Echec de récupération du nom du synonyme GBIF");
        return false;
      }
      // on note la redirection
      $struct['redirection']['nom'] = $struct['taxon']['nom'];
      // on change le nom scientifique utilisé
      $struct['taxon']['nom'] = $tmp['nom'];
      // on se ré-appelle sur le nouveau nom
      return infos_gbif($struct, $classif);
    }
  }

  // juste les liens externes
  if (!$classif) {
    return true;
  }
  
  // données taxon
  //$result['taxon']['nom'] = trim($cur->canonicalName); // déjà présent (par définition)
  $struct['taxon']['auteur'] = $tmp['auteur']; // trim($cur->authorship);
  $struct['taxon']['rang'] = gbif_cherche_rang($cur->rank);
  // on remplace le nom par le nom retourné
  $struct['taxon']['nom'] = $cur->canonicalName;
  $taxon = $struct['taxon']['nom'];
  $struct['classification'] = 'GBIF';
  $struct['classification-taxobox'] = gbif_classif();
  
  // extraction de la classification
  $tbl = [];
  foreach($gbif_wp as $tmp => $nop) {
    $rr = strtolower($tmp);
    if (isset($cur->$rr)) {
      if ($tmp == 'KINGDOM') {
        $struct['regne'] = gbif_cherche_regne($cur->$rr);
      } else {
        $buf = gbif_cherche_rang($tmp);
        if ($buf != $struct['taxon']['rang']) {
          $x = [];
          $x['nom'] = $cur->$rr;
          $x['rang'] = $buf;
          $tbl[] = $x;
        }
      }
    }
  }
  $struct['rangs'] = $tbl;
  
  // si pas de "règne" trouvé : erreur
  if (!isset($struct['regne'])) {
    logs("GBIF: charte non trouvée");
    return false;
  }
  
  // basionyme ?
  if (isset($cur->basionymKey) and !empty($cur->basionymKey)) {
    $url = "https://api.gbif.org/v1/species/" .
         urlencode($cur->basionymKey);
    $ret2 = get_data($url);
    // erreur CURL
    if ($ret2 !== false) {
      $cur2 = json_decode($ret2);
      if ($cur2 !== null) {
        if (isset($cur2->canonicalName)) {
          $struct['basionyme']['nom'] = trim($cur2->canonicalName);
          $struct['basionyme']['auteur'] = trim($cur2->authorship);
          $struct['basionyme']['source'] = gbif_bioref();
        }
      }
    }
  }
  
  // si présent, les sous-taxons
  if (isset($cur->numDescendants) and ($cur->numDescendants > 0)) {
    $encore = true;
    $offset = 0;
    $liste = [];
    while ($encore) {
      $url = "https://api.gbif.org/v1/species/" . $struct['liens']['gbif']['id'] . "/children?offset=$offset";
      $ret = get_data($url);
      // erreur CURL
      if ($ret === false) {
        goto nop;
      }
      $cur = json_decode($ret);
      if ($cur === null) {
        goto nop;
      }
      foreach($cur->results as $c) {
        $tmp = [];
        if ($c->rank == "UNRANKED") {
          continue;
        }
        $key = $c->key;
        if (!isset($c->canonicalName)) {
          continue;
        }
        $x = gbif_taxon_info($key, $c->canonicalName);
        if ($x === false) {
          continue;
        }
        $tmp['nom'] = $x['nom'];
        $tmp['auteur'] = $x['auteur'];
        if (isset($x['rang'])) {
          $tmp['rang'] = $x['rang'];
        }
        $liste[] = $tmp;
      }
      if ($cur->endOfRecords) {
        $encore = false;
      } else {
        $offset += 20;
      }
    }
    if (!empty($liste)) {
      $struct['sous-taxons']['liste'] = $liste;
      $struct['sous-taxons']['source'] = gbif_bioref();
    }
  }
nop:

  // si présent, les noms vernaculaires en français
  $offset = 0;
  $encore = true;
  $liste = [];
  while ($encore) {
    $url = "https://api.gbif.org/v1/species/" . $struct['liens']['gbif']['id'] . "/vernacularNames?offset=$offset";
    $ret = get_data($url);
    // erreur CURL
    if ($ret === false) {
      break;
    }
    $cur = json_decode($ret);
    if ($cur === null) {
      break;
    }
    foreach($cur->results as $c) {
      if ($c->language == "fra") {
        $liste[] = $c->vernacularName;
      }
    }
    if ($cur->endOfRecords) {
      $encore = false;
    } else {
      $offset += 20;
    }
  }
  if (!empty($liste)) {
    $struct['vernaculaire'][gbif_bioref()] = $liste;
  }
nop2:

  // synonymes
  $url = "https://api.gbif.org/v1/species/" . $struct['liens']['gbif']['id'] . "/synonyms";
  $ret = get_data($url);
  // erreur CURL
  if ($ret === false) {
    goto nop3;
  }
  $cur = json_decode($ret);
  if ($cur === null) {
    goto nop3;
  }
  $tmp = [];
  $fff = null;
  foreach($cur->results as $c) {
    $blob = gbif_taxon_info($c->key);
    if ($blob === false) {
      continue;
    }
    $el = [];
    $el['nom'] = $blob['nom'];
    $el['auteur'] = $blob['auteur'];
    if (isset($el['rang'])) {
      $el['rang'] = $blob['rang'];
    }
    $tmp[] = $el;
  }
  // Note : on ne boucle pas s'il y a plus de réponse (20, c'est déjà énorme)
  if (!empty($tmp)) {
    $struct['synonymes']['liste'] = $tmp;
    $struct['synonymes']['source'] = gbif_bioref();
  }
nop3:

  return true;
}

// retourne les liens externes liés à GBIF (si présents)
function m_gbif_ext($struct) {
  $cdate = dates_recupere();
  
  if (isset($struct['liens']['gbif']['id'])) {
    $data = $struct['liens']['gbif'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    if (isset($data['synonyme']) and $data['synonyme']) {
      return "{{GBIF | " . $data['id'] . " | " . $cible . " | nv | consulté le=$cdate }}";
    } else {
      return "{{GBIF | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}";
    }
  } else {
    return false;
  }
}

// retourne les liens HTTP directs liés à GBIF (si présents)
function m_gbif_liens($struct) {
  if (isset($struct['liens']['gbif']['id'])) {
    return "<a href='https://www.gbif.org/species/" .
           $struct['liens']['gbif']['id'] . "'>GBIF</a>";
  } else {
    return false;
  }
}

