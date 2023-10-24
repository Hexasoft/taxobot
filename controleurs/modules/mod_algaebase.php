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
  $ret = get_data("https://www.algaebase.org/");
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
function m_algaebase_infos_genre(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $key = alg_apikey();
  if ($key === false) {
    logs("AlgaeBase: échec de récupération d'une clé API (espèce)");
    return false;
  }
  $url = "https://api2.algaebase.org/v1.3/genus?&genus=Stradnerlithus&offset=0&order=genus,false";
  $header = [
      "Accept: */*",
      "Origin: https://www.algaebase.org",
      "DNT: 1",
      "Referer: https://www.algaebase.org/search/genus/",
      "Sec-Fetch-Dest: empty",
      "Sec-Fetch-Mode: cors",
      "Sec-Fetch-Site: same-site",
      "TE: trailers",
      "abapikey: $key",
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBase: échec de la recherche (genre)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec d'analyse de la recherche (genre)");
    return false;
  }
  if (!isset($res->result) or !isset($res->_pagination->_total_number_of_results)) {
    logs("AlgaeBase: taxon non trouvé (pas de résultat) (genre)");
    return false;
  }
  
  // on parcours les résultats
  $found = false;
  foreach($res->result as $r) {
    if (!isset($r->{"dwc:taxonRank"})) {
      continue;
    }
    if ($r->{"dwc:taxonRank"} != "genus") {
      continue;
    }
    if (!isset($r->{"dwc:scientificName"})) {
      continue;
    }
    $tmp = $r->{"dwc:scientificName"};
    $ns = explode(" ", $tmp);
    if ($ns[0] != $taxon) {
      continue;
    }
    $found = $r;
    break;
    // Note : il faudrait faire une boucle sur l'offset
    /*
    if ($offset >= $res->_pagination->_total_number_of_results) {
      break;
    } else {
      $offset += 50;
    }
    */
  }
  if ($found === false) {
    logs("AlgaeBase: taxon non trouvé (genre)");
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
  if (isset($found->isFossil) and ($found->isFossil == "Y")) {
    $blob['eteint'] = true;
  }
  if (isset($found->{"dwc:isFossil"}) and ($found->{"dwc:isFossil"} == "Y")) {
    $blob['eteint'] = true;
  }
  $struct['liens']['algaebase'] = $blob;

  if (!$classif) {
    return true;
  }

  $taxonID = $found->{"dwc:acceptedNameUsageID"};
  
  // pour classification on met en place les infos
  $struct['taxon'] = $struct['liens']['algaebase'];
  // charge de la classif
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  
  // extraction de données
  if (isset($found->{"nameOrigin"}) and $found->{"nameOrigin"}) {
    $struct['etymologie'] = [ "source" => "AlgaeBASE",
                              "texte" => "''" . preg_replace("/[.][ ]*$/", "", $found->{"nameOrigin"}) . "''",
                            ];
  }
  // publication originale
  if (isset($found->{"dcterms:bibliographicCitation"}) and !empty($found->{"dcterms:bibliographicCitation"})) {
    $struct['originale'] = $found->{"dcterms:bibliographicCitation"};
    $struct['originale'] = str_replace(['<i>','</i>'], "''", $struct['originale']);
    if (isset($found->pdfs[0]->pdf_url)) {
      $struct['originale'] .= " ([" . $found->pdfs[0]->pdf_url . " PDF])";
    }
  }

  // on récupère la classification
  $url = "https://api2.algaebase.org/v1.3/genus/$taxonID";
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
    logs("AlgaeBase: échec de récupération de la classification (genre)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec de décodage de la classification (genre)");
    return false;
  }
  
  if (!isset($res->classification)) {
    logs("AlgaeBase: échec de d'extraction de la classification (genre)");
    return false;
  }
  $der = false;
  $phylum = "";
  $kingdom = "";
  $tbl = alg_extrait_classif($res->classification, $der, $phylum, $kingdom);
  $struct['rangs'] = array_reverse($tbl);
  $cID = false;
  // on supprime le genre (puisque c'est le taxon) mais on récupère son ID car c'est celui-là pour la classification inf.
  foreach($struct['rangs'] as $idx => $cont) {
    if ($cont['rang'] == 'genre') {
      $cID = $cont['id'];
      unset($struct['rangs'][$idx]);
    }
  }
  
  if ($cID !== false) {
    $url = "https://api2.algaebase.org/v1.3/taxonomy/$cID";
    $ret = get_data($url, $header);
    if ($ret === false) {
      logs("AlgaeBase: échec de récupération de la classification (genre) (2)");
    } else {
      $res = json_decode($ret);
      if ($res === false) {
        logs("AlgaeBase: échec de décodage de la classification (genre) (2)");
      }
    }
  }

  // les sous-taxons
  if (!isset($res->lowerTaxa)) {
    unset($tbl);
  } else {
    $tbl = alg_extrait_classif($res->lowerTaxa, $nop, $phylum, $kingdom);
  }
  if (!empty($tbl)) {
    $struct['sous-taxons']['liste'] = $tbl;
    $struct['sous-taxons']['source'] = "AlgaeBASE";
  }
  
  // la charte
  $struct['regne'] = alg_charte($phylum, $kingdom);
  if ($struct['regne'] != 'algue') {
    $struct['cacher-regne'] = true;
  }

  // on charge la page pour obtenir quelques informations
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$taxonID/detail";
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBases: échec de récupération des infos détaillées sur le taxon (> genre)");
    goto suitegenre;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBases: échec d'analyse des infos détaillées sur le taxon (> genre)");
    goto suitegenre;
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
  
suitegenre:
  return true;
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
    if (!isset($res->result) or !isset($res->_pagination->_total_number_of_results)) {
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
  
  // on charge la page d'info de l'espèce
  $url = "https://api2.algaebase.org/v1.3/species/" . $found->{"dwc:acceptedNameUsageID"};
  $ret = get_data($url, $header);
  if ($ret !== false) {
    $res = json_decode($ret);
    if ($res !== false) {
      $found = $res->details;
    }
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
  if (isset($found->isFossil) and ($found->isFossil == "Y")) {
    $blob['eteint'] = true;
  }
  if (isset($found->{"dwc:isFossil"}) and ($found->{"dwc:isFossil"} == "Y")) {
    $blob['eteint'] = true;
  }
  $struct['liens']['algaebase'] = $blob;

  if (!$classif) {
    return true;
  }
  $taxonID = $found->{"dwc:acceptedNameUsageID"};
  
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
    $struct['cacher-regne'] = true;
  }
  
  // on charge la page pour obtenir quelques informations
  $id = $struct['taxon']['id'];
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$id/detail";
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBases: échec de récupération des infos détaillées sur le taxon (> genre)");
    goto suiteespece;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBases: échec d'analyse des infos détaillées sur le taxon (> genre)");
    goto suiteespece;
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
  
suiteespece:
  return true;
}


// taxon supérieur au genre
function m_algaebase_info_sup(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $key = alg_apikey();
  if ($key === false) {
    logs("AlgaeBase: échec de récupération d'une clé API (> genre)");
    return false;
  }
  
  $url = "https://api2.algaebase.org/v1.3/taxonomy?searchTerm=$taxon&offset=0";
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
    logs("AlgaeBase: échec de la recherche (> genre)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec d'analyse de la recherche (> genre)");
    return false;
  }
  if (!isset($res->result) or !isset($res->_pagination->_total_number_of_results)) {
    logs("AlgaeBase: taxon non trouvé (pas de résultat) (> genre)");
    return false;
  }
  $found = false;
  foreach($res->result as $r) {
    if (!isset($r->{"dwc:scientificName"}) or ($r->{"dwc:scientificName"} != $taxon)) {
      continue;
    }
    $found = $r;
    break;
  }
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
  $blob['id'] = $found->{"dwc:taxonID"};
  if (isset($found->isFossil) and ($found->isFossil == "Y")) {
    $blob['eteint'] = true;
  }
  if (isset($found->{"dwc:isFossil"}) and ($found->{"dwc:isFossil"} == "Y")) {
    $blob['eteint'] = true;
  }
  $struct['liens']['algaebase'] = $blob;

  if (!$classif) {
    return true;
  }
  
  $taxonID = $found->{"dwc:taxonID"};
  
  // pour classification on met en place les infos
  $struct['taxon'] = $struct['liens']['algaebase'];
  // charge de la classif
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  
  // extraction de données
  if (isset($found->{"nameOrigin"}) and $found->{"nameOrigin"}) {
    $struct['etymologie'] = [ "source" => "AlgaeBASE",
                              "texte" => "''" . preg_replace("/[.][ ]*$/", "", $found->{"nameOrigin"}) . "''",
                            ];
  }
  // publication originale
  if (isset($found->{"dcterms:bibliographicCitation"}) and !empty($found->{"dcterms:bibliographicCitation"})) {
    $struct['originale'] = $found->{"dcterms:bibliographicCitation"};
    $struct['originale'] = str_replace(['<i>','</i>'], "''", $struct['originale']);
    if (isset($found->pdfs[0]->pdf_url)) {
      $struct['originale'] .= " ([" . $found->pdfs[0]->pdf_url . " PDF])";
    }
  }

  // on récupère la classification
  $idsup = $taxonID;  // on se base sur le taxon
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$idsup";
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
    logs("AlgaeBase: échec de récupération de la classification (> genre)");
    return false;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBase: échec de décodage de la classification (> genre)");
    return false;
  }
  
  if (!isset($res->higherTaxa)) {
    logs("AlgaeBase: échec d'extraction de la classification (> genre)");
    return false;
  }
  $der = false;
  $phylum = "";
  $kingdom = "";
  $tbl = alg_extrait_classif($res->higherTaxa, $der, $phylum, $kingdom);
  $struct['rangs'] = array_reverse($tbl);
  // on supprime le taxon en cours de "traitement"
  foreach($struct['rangs'] as $idx => $cont) {
    if ($cont['rang'] == $struct['taxon']['rang']) {
      unset($struct['rangs'][$idx]);
    }
  }
  
  // les sous-taxons
  if (!isset($res->lowerTaxa)) {
    unset($tbl);
  } else {
    $tbl = alg_extrait_classif($res->lowerTaxa, $nop, $phylum, $kingdom);
  }
  if (!empty($tbl)) {
    $struct['sous-taxons']['liste'] = $tbl;
    $struct['sous-taxons']['source'] = "AlgaeBASE";
  }
  
  // la charte
  $struct['regne'] = alg_charte($phylum, $kingdom);
  if ($struct['regne'] != 'algue') {
    $struct['cacher-regne'] = true;
  }

  // on charge la page pour obtenir quelques informations
  $url = "https://api2.algaebase.org/v1.3/taxonomy/$taxonID/detail";
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("AlgaeBases: échec de récupération des infos détaillées sur le taxon (> genre)");
    goto suitesup;
  }
  $res = json_decode($ret);
  if ($res === false) {
    logs("AlgaeBases: échec d'analyse des infos détaillées sur le taxon (> genre)");
    goto suitesup;
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
  
suitesup:
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
  
  // test : genre
  if (m_algaebase_infos_genre($struct, $classif)) {
    // trouvé comme genre
    return true;
  }
  
  // il faut faire une recherche spécifique pour taxons de rang supérieur
  return m_algaebase_info_sup($struct, $classif);

}

// génération des liens externes (modèles dans Voir aussi)
function m_algaebase_ext($struct) {
  if (isset($struct['liens']['algaebase']['id'])) {
    $data = $struct['liens']['algaebase'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    if (isset($data['eteint']) and $data['eteint']) {
      $eteint = " éteint=oui |";
    } else {
      $eteint = "";
    }
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
    return "{{AlgaeBASE$type | $id | $nom |$sup$eteint consulté le=$cdate}}";
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

