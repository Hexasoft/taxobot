<?php

/*
  Module pour algaebase (non classification)
*/

$alg_rangs = [
  'clade' => 'clade',
  'type' => 'type',
  'group' => 'groupe',
  'unspecified' => 'non-classé',
  'subform' => 'sous-forme',
  'form' => 'forme',
  'forma' => 'forme',
  'variety' => 'variété',
  'pathovar' => 'pathovar',
  'cultivar' => 'cultivar',
  'subspecies' => 'sous-espèce',
  'hybrid' => 'hybride',
  'species' => 'espèce',
  'subserie' => 'sous-série',
  'serie' => 'série',
  'subsection' => 'sous-section',
  'section' => 'section',
  'subgenus' => 'sous-genre',
  'genus' => 'genre',
  'subtribe' => 'sous-tribu',
  'tribe' => 'tribu',
  'supertribe' => 'super-tribu',
  'infratribe' => 'infra-tribu',
  'subfamily' => 'sous-famille',
  'family' => 'famille',
  'null2' => 'épifamille',
  'superfamily' => 'super-famille',
  'microorder' => 'micro-ordre',
  'infraorder' => 'infra-ordre',
  'suborder' => 'sous-ordre',
  'order' => 'ordre',
  'superorder' => 'super-ordre',
  'subcohort' => 'sous-cohorte',
  'cohort' => 'cohorte',
  'supercohort' => 'super-cohorte',
  'subterclass' => 'subter-classe',
  'infraclass' => 'infra-classe',
  'subclass' => 'sous-classe',
  'class' => 'classe',
  'superclass' => 'super-classe',
  'megaclass' => 'super-classe',
  'microphylum' => 'micro-embranchement',
  'infraphylum' => 'infra-embranchement',
  'subphylum' => 'sous-embranchement',
  'phylum' => 'embranchement',
  'superphylum' => 'super-embranchement',
  'infradivision' => 'infra-division',
  'subdivision' => 'sous-division',
  'division' => 'division',
  'subphylum subdivision' => 'sous-division',
  'phylum division' => 'division',
  'superdivision' => 'super-division',
  'infrakingdom' => 'infra-règne',
  'null' => 'rameau',
  'subkingdom' => 'sous-règne',
  'kingdom' => 'règne',
  'superkingdom' => 'super-règne',
  'subdomain' => 'sous-domaine',
  'domain' => 'domaine',
  'superdomain' => 'super-domaine',
  'empire' => 'empire',
  'kingdom' => 'règne',
  'subkingdom' => 'sous-règne',
  'unknown' => 'non-classé',
];

function alg_rang($_rang) {
  global $alg_rangs;
  
  $rang = mb_strtolower($_rang);
  
  if (isset($alg_rangs[$rang])) {
    return $alg_rangs[$rang];
  }
  return "NOTFOUND-$rang";
}

// retourne une clé API
function alg_apikey() {
  // cookie
  $ret = get_data("https://www.algaebase.org/search/");
  // API key
  $url = "https://api2.algaebase.org/auth/";
  $header = [
    "Accept: */*",
    "Origin: https://www.algaebase.org",
    "DNT: 1",
    "Referer: https://www.algaebase.org/",
    "Sec-Fetch-Dest: empty",
    "Sec-Fetch-Mode: cors",
    "Sec-Fetch-Site: same-site",
    "TE: trailers",
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec d'API");
    return false;
  }
  $key = trim($ret);
  if (empty($key)) {
    return false;
  }
  return $key;
}

// extraire une classification (à partir du JSON)
function alg_extrait_classif($liste, &$der, &$phylum, &$kingdom) {
  $tbl = [];
  $der = false;
  $phylum = "";
  $kingdom = "";
  foreach($liste as $cl) {
    if ($cl->{"dwc:taxonRank"} == 'empire') {
      continue; // on n'utilise pas
    }
    if (($cl->{"dwc:taxonRank"} == 'Kingdom') or ($cl->{"dwc:taxonRank"} == 'kingdom')) {
      $kingdom = $cl->{"dwc:scientificName"};
    }
    if (($cl->{"dwc:taxonRank"} == 'Phylum') or ($cl->{"dwc:taxonRank"} == 'phylum')) {
      $phylum = $cl->{"dwc:scientificName"};
    }
    $tmp = [];
    $tmp['rang'] = alg_rang($cl->{"dwc:taxonRank"});
    $nom = trim($cl->{"dwc:scientificName"});
    $tmp['id'] = $cl->{"dwc:taxonID"};
    if ($tmp['rang'] == ['genre']) {
      $der = $tmp['id'];
    }
    // $tmp['nom'] = trim(preg_replace("/" . $cl->{"dwc:taxonRank"} . "[ ]*/i", "", $nom));
    $tmp['nom'] = $nom;
    $tmp['auteur'] = $cl->{"dwc:scientificNameAuthorship"};
    if (isset($cl->{"dwc:namePublishedInYear"}) and !empty($cl->{"dwc:namePublishedInYear"})) {
      $tmp['auteur'] .= ", " . $cl->{"dwc:namePublishedInYear"};
    }
    $tmp['auteur'] = str_replace("& al.", "et al.", $tmp['auteur']);
    $tbl[] = $tmp;
  }
  return $tbl;
}

// charte
function alg_charte($phylum, $kingdom) {
  if (($kingdom == 'Eubacteria') or ($kingdom == 'eubacteria')) {
    return "bactérie";
  }
  if (($phylum == 'Tracheophyta') or ($phylum == 'tracheophyta')) {
    return "végétal";
  }
  if (($kingdom == 'Protozoa') or ($kingdom == 'protozoa')) {
    return "protiste";
  }
  // pour le reste
  return "algue";
}

// déclaration du module
function m_algaebase_init() {
  return declare_module("algaebase", true, true,
        ['champignon','algue','végétal','algue','archaea','bactérie','protiste'], 990);
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_algaebase_infos_espece(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $key = alg_apikey();
  if ($key === false) {
    logs("AlgaeBase: échec de récupération d'une clé API (espèce)");
    return false;
  }
  $offset = 0;
  $found = false;
  while(true) {
    // recherche
    $url = "https://api2.algaebase.org/v1.3/species?&scientificname=" . str_replace(" ", "%20", $taxon) . "&offset=0";
    $header = [
      "Accept: */*",
      "Origin: https://www.algaebase.org",
      "DNT: 1",
      "Referer: https://www.algaebase.org/",
      "Sec-Fetch-Dest: empty",
      "Sec-Fetch-Mode: cors",
      "Sec-Fetch-Site: same-site",
      "TE: trailers",
      "abapikey: $key",
    ];
    $ret = get_data($url, $header);
    if ($ret === false) {
      logs("AlgaeBase: échec de la recherche");
      break;
    }
    $res = json_decode($ret);
    if ($res === false) {
      logs("AlgaeBase: échec d'analyse de la recherche");
      break;
    }
    if (!isset($res->result) or !isset(_pagination->_total_number_of_results)) {
      logs("AlgaeBase: taxon non trouvé (pas de résultat)");
      break;
    }
    // on parcours les résultats pour voir si on a le bon
    foreach($res->result as $r) {
      $nom = $r->{"dwc:scientificName"};
      $nom = str_replace(" " . $r->{"dwc:scientificNameAuthorship"}, "", $nom);
      // à faire : ajouter la détection de "non valide"
      // 1. choisir valide exact 2. si synonyme redir (selon) 3. invalide (lien ext)
      if ($nom == $taxon) {
        // trouvé
        $found = $r;
        break;
      }
    }
    // fin ?
    if ($offset >= $res->_pagination->_total_number_of_results) {
      break;
    } else {
      $offset += 50;
    }
  }

  // non trouvé
  if ($found === false) {
    logs("AlgaeBase: taxon non trouvé");
    return false;
  }
  
  // on prépare les infos de base
  $blob = [];
  $blob['auteur'] = $found->{"dwc:scientificNameAuthorship"};
  if (isset($found->{"dwc:namePublishedInYear"}) and !empty($found->{"dwc:namePublishedInYear"})) {
    $blob['auteur'] .= ", " . $found->{"dwc:namePublishedInYear"};
  }
  $blob['rang'] = alg_rang($found->{"dwc:taxonRank"});
  $blob['nom'] = $taxon;
  $blob['id'] = $found->{"dwc:acceptedNameUsageID"};
  $struct['liens']['algaebase'] = $blob;

  if (!$classif) {
    return true;
  }
  
  // pour classification on met en place les infos
  $struct['taxon'] = $struct['liens']['algaebase'];
  // charge de la classif
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  
  // publication originale
  if (isset($found->{"dcterms:bibliographicCitation"}) and !empty($found->{"dcterms:bibliographicCitation"})) {
    $struct['originale'] = $found->{"dcterms:bibliographicCitation"};
    $struct['originale'] = str_replace(['<i>','</i>'], "''", $struct['originale']);
    if (isset($found->pdfs[0]->pdf_url)) {
      $struct['originale'] .= " ([" . $found->pdfs[0]->pdf_url . " PDF])";
    }
  }
  // diverses infos
  $tbl = [];
  if (isset($found->{"dwc:isMarine"}) and $found->{"dwc:isMarine"}) {
    $tbl[] = "Cette espèce est marine";
  }
  if (isset($found->{"dwc:isFreshwater"}) and $found->{"dwc:isFreshwater"}) {
    $tbl[] = "Cette espèce vit en eau douce";
  }
  if (isset($found->{"dwc:isTerrestrial"}) and $found->{"dwc:isTerrestrial"}) {
    $tbl[] = "Cette espèce est terrestre";
  }
  if (isset($found->{"nameOrigin"}) and $found->{"nameOrigin"}) {
    $struct['etymologie'] = [ "source" => "AlgaeBASE",
                              "texte" => "''" . preg_replace("/[.][ ]*$/", "", $found->{"nameOrigin"}) . "''",
                            ];
  }
  if (!empty($tbl)) {
    $struct['description']['AlgaeBASE'] = $tbl;
  }
  
  // on récupère la classification
  $idgenre = $found->genusID;
  $url = "https://api2.algaebase.org/v1.3/genus/$idgenre";
  $header = [
    "Accept: application/json",
    "Origin: https://www.algaebase.org",
    "DNT: 1",
    "Referer: https://www.algaebase.org/",
    "Sec-Fetch-Dest: empty",
    "Sec-Fetch-Mode: cors",
    "Sec-Fetch-Site: same-site",
    "TE: trailers",
    "abapikey: $key",
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec de récupération de la classification");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec de décodage de la classification");
    return false;
  }
  
  if (!isset($res->classification)) {
    logs("AlgaeBase: échec de d'extraction de la classification");
    return false;
  }
  $der = false;
  $phylum = "";
  $kingdom = "";
  $tbl = alg_extrait_classif($res->classification, $der, $phylum, $kingdom);
  $struct['rangs'] = array_reverse($tbl);
  
  // taxons de rang inférieur
  if ($der !== false) {
    
  }
  
  // la charte
  $struct['regne'] = alg_charte($phylum, $kingdom);
  if ($struct['regne'] != 'algue') {
    $struct['regne-cache'] = true;
  }
  
  return true;
}


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_algaebase_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // on détermine le nombre de "mots" pour voir si espèce ou au dessus
  $nb = count(explode(" ", $taxon));

  if ($nb >= 2) {
    // recherche sur une espèce
    return m_algaebase_infos_espece($struct, $classif);
  }
  // clé API
  $key = alg_apikey();
  if ($key === false) {
    logs("AlgaeBase: échec de récupération d'une clé API");
    return false;
  }
  // on fait une recherche sur le terme : on n'accepte que les entrées > espèce
  $url = "https://api2.algaebase.org/v1.3/species/list?dwcscientificname=$taxon";
  $header = [
    "Accept: application/json",
    "Origin: https://www.algaebase.org",
    "DNT: 1",
    "Referer: https://www.algaebase.org/",
    "Sec-Fetch-Dest: empty",
    "Sec-Fetch-Mode: cors",
    "Sec-Fetch-Site: same-site",
    "TE: trailers",
    "abapikey: $key",
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec de recherche du taxon");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec d'interprétation de la recherche");
    return false;
  }
  if (is_string($res) and ($res == "Nothing Found")) {
    logs("AlgaeBase: taxon non trouvé");
    return false;
  }
  
  // on parcours pour retenir ceux qui collent
  $tbl = [];
  $nop = [ 'form', 'forma', 'subform', 'variety', 'pathovar', 'cultivar', 'subspecies', 'species' ];
  foreach($res->result as $t) {
    if (in_array($t->taxonRank, $nop)) {
      continue; // on l'ignore
    }
    if ($t->page != 'taxonomy') {
      continue; // seulement les sup. au genre (sinon on traitera plus loin)
    }
    
    $tmp = [];
    $tmp['id'] = $t->id;
    $tmp['rang'] = $t->taxonRank;
    $tmp['ns'] = $t->value;
    $tbl[] = $tmp;
  }
  if (empty($tbl)) {
    // c'est peut-être un genre
    foreach($res->result as $t) {
      if ($t->taxonRank != 'genus') {
        continue; // on l'ignore
      }
      if ($t->page != 'genus') {
        continue;
      }
      $tmp = [];
      $tmp['id'] = $t->id;
      $tmp['rang'] = $t->taxonRank;
      $tmp['ns'] = $t->value;
      $tbl[] = $tmp;
    }
    if (empty($tbl)) {
      logs("AlgaeBase: taxon non trouvé");
      return false;
    }
    // c'est un genre : il faut traiter différemment
    logs("AlgaeBase: taxon trouvé, mais les genres ne sont pas traités pour le moment");
    return false;
  }
  
  if (count($tbl) > 1) {
    $tbl2 = [];
    foreach($tbl as $idx => $cont) {
      if (strpos($cont['ns'], "$taxon ") !== false) {
        $tbl2[] = $cont;
      }
    }
    $tbl = $tbl2;
    if (count($tbl) > 1) {
      logs("AlgaeBase: situation non gérée, merci de prévenir Hexasoft en indiquant le nom du taxon");
      return false;
    }
    if (empty($tbl)) {
      logs("AlgaeBase: taxon non trouvé (après multiples résultats)");
      return false;
    }
  }

  // on récupère les rangs supérieurs et les sous-taxons
  $id = $tbl[0]['id'];
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$id";
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec de récupération de la classification du taxon");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec d'interprétation de la classification");
    return false;
  }
  
  // parcours des rangs supérieurs
  $nop = false;
  $tbl = alg_extrait_classif($res->higherTaxa, $nop, $phylum, $kingdom);
  $found = array_pop($tbl); // le dernier est le taxon étudié
  // on insert la classification supérieure
  $struct['rangs'] = array_reverse($tbl);
  // les éléments de gestion de classification
  $struct['regne'] = alg_charte($phylum, $kingdom);
  if ($struct['regne'] != 'algue') {
    $struct['regne-cache'] = true;
  } // la suppression de l'empire se fait au niveau du rendu
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  // le taxon lui-même
  $struct['taxon'] = $found;
  $struct['liens']['algaebase'] = $found;
  // les sous-taxons
  $tbl = alg_extrait_classif($res->lowerTaxa, $nop, $phylum, $kingdom);
    if (!empty($tbl)) {
    $struct['sous-taxons']['liste'] = $tbl;
    $struct['sous-taxons']['source'] = "AlgaeBASE";
  }
  
  // on charge la page pour obtenir quelques informations
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$id/detail";
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBases: échec de récupération des infos détaillées sur le taxon");
    goto suite;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBases: échec d'analyse des infos détaillées sur le taxon");
    goto suite;
  }
  // description ?
  if (isset($res->details->description) and !empty($res->details->description)) {
    $val = $res->details->description;
    if (isset($struct['description']['AlgaeBASE'])) {
      $struct['description']['AlgaeBASE'][] = "''" . $val . "''";
    } else {
      $struct['description']['AlgaeBASE'] = [ "''" . $val . "''" ];
    }
  }
  // publication originale
  if (isset($res->details->originalPublicationRef) and !empty($res->details->originalPublicationRef)) {
    $struct['originale'] = $res->details->originalPublicationRef;
    $struct['originale'] = str_replace(['<i>','</i>'], "''", $struct['originale']);
    // cas où le PDF est disponible ?
  }
  
suite:
  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_algaebase_ext($struct) {
  if (isset($struct['liens']['algaebase']['id'])) {
    $data = $struct['liens']['algaebase'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    $nom = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $nom .= " " . str_replace("et al.", "{{et al.}}", $data['auteur']);
    }
    $sup = "";
    if (($data['rang'] == 'espèce') or ($data['rang'] == 'sous-espèce') or
        ($data['rang'] == 'forme') or ($data['rang'] == 'variété') or
        ($data['rang'] == 'pathovar') or ($data['rang'] == 'cultivar')) {
      $type = ' espèce';
    } else if (($data['rang'] == 'genre') or ($data['rang'] == 'sous-genre')) {
      $type = ' genre';
    } else {
      $type = "";
      $sup = " " . $data['rang'] . " |";
    }
    return "{{AlgaeBASE$type | $id | $nom |$sup consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_algaebase_liens($struct) {
  if (isset($struct['liens']['algaebase']['id'])) {
    return "<a href='https://www.algaebase.org/search/species/detail/?species_id=" .
           $struct['liens']['algaebase']['id'] . "'>AlgaeBase</a>";
  } else {
    return false;
  }
}

