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
  'kingdom' => 'royaume',
  'subkingdom' => 'sous-royaume',
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
function alg_extrait_classif($liste, &$der) {
  $tbl = [];
  $der = false;
  foreach($liste as $cl) {
    if ($cl->{"dwc:taxonRank"} == 'empire') {
      continue; // on n'utilise pas
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
    $tbl[] = "Étymologie : ''" . preg_replace("/[.][ ]*$/", "", $found->{"nameOrigin"}) . "''";
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
  $tbl = alg_extrait_classif($res->classification, $der);
  $struct['rangs'] = array_reverse($tbl);
  
  // taxons de rang inférieur
  if ($der !== false) {
    
  }
  
  // la charte
  $struct['regne'] = "algue"; //alg_regne($regne);
  
  return true;
}

/*
curl 'https://api2.algaebase.org/v1.3/taxonomy/7007' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0' -H 'Accept: application/json, text/javascript; q=0.01' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'abapikey: HZw6jFIcUsPgrA0XAe6twUEFRKt6TWY2' -H 'Origin: https://www.algaebase.org' -H 'Connection: keep-alive' -H 'Referer: https://www.algaebase.org/' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-site' -H 'TE: trailers'

retourne higherTaxa et lowerTaxa

Idée : faire une recherche générique → obtenir heigher+lower + rang.
Si espèce → récupérer infos espèce (code existant).

curl 'https://api2.algaebase.org/v1.3/species/list?dwcscientificname=Zosteraceae' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0' -H 'Accept: application/json, text/javascript; q=0.01' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate, br' -H 'abapikey: rv7K46QPyKxJx2ma6XxwPpC5FLPZTBhR' -H 'Origin: https://www.algaebase.org' -H 'Connection: keep-alive' -H 'Referer: https://www.algaebase.org/' -H 'Sec-Fetch-Dest: empty' -H 'Sec-Fetch-Mode: cors' -H 'Sec-Fetch-Site: same-site' -H 'TE: trailers'
*/


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
    logs("AlgaeBase: situation non gérée, merci de prévenir Hexasoft en indiquant le nom du taxon");
    return false;
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
  $tbl = alg_extrait_classif($res->higherTaxa, $nop);
  $found = array_pop($tbl); // le dernier est le taxon étudié
  // on insert la classification supérieure
  $struct['rangs'] = array_reverse($tbl);
  // les éléments de gestion de classification
  $struct['regne'] = "algue"; //alg_regne($regne);
  $struct['classification'] = 'AlgaeBASE';
  $struct['classification-taxobox'] = 'AlgaeBASE';
  // le taxon lui-même
  $struct['taxon'] = $found;
  $struct['liens']['algaebase'] = $found;
  // les sous-taxons
  $tbl = alg_extrait_classif($res->lowerTaxa, $nop);
    if (!empty($tbl)) {
    $struct['sous-taxons']['liste'] = $tbl;
    $struct['sous-taxons']['source'] = "AlgaeBASE";
  }

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
    return "{{AlgaeBASE espèce | $id | $nom | consulté le=$cdate}}";
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

