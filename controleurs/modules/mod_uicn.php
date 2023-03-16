<?php

/*
  Cherche les infos liées à l'UICN
*/


function m_uicn_init() {
  return declare_module("uicn", false, true, true);
}


$ch = [];
$ch['curl'] = NULL;
$ch['cookie'] = [];

function start_curl() {
  global $fichier_temp;
  global $ch;
  
  $ch['curl'] = curl_init();
  curl_setopt($ch['curl'], CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch['curl'], CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch['curl'], CURLOPT_MAXCONNECTS, 100);

}

function clean_curl() {
  global $ch;
  //unlink('cookie-get-uicn.dat');
  $ch['cookie'] = [];
}

function get_curl($url, $ref=null) {
  global $ch;

  curl_setopt($ch['curl'], CURLOPT_URL, $url);
  curl_setopt($ch['curl'], CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");

  /* Crappy hack to add extra cookies, should be cleaned up */
  $cookies = NULL;
  foreach ($ch['cookie'] as $name => $value) {
    if (empty($cookies)) {
      $cookies = "$name=$value";
    } else {
      $cookies .= "; $name=$value";
    }
  }
  if ($cookies != NULL) {
    curl_setopt($ch['curl'], CURLOPT_COOKIE, $cookies);
    echo "$cookies";
  }

  $head = array(
    'Expect:',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3',
    'DNT: 1',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1',
    'Pragma: no-cache',
    'Cache-Control: no-cache'
    );
  if ($ref !== null) {
    $head[] = "Referer: $ref";
  }
  curl_setopt($ch['curl'], CURLOPT_HTTPHEADER, $head);
  curl_setopt($ch['curl'], CURLOPT_ENCODING , "");
  curl_setopt($ch['curl'], CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($ch['curl'], CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch['curl'], CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch['curl'], CURLOPT_TIMEOUT, 30);
  curl_setopt($ch['curl'], CURLOPT_CONNECTTIMEOUT, 10);
  if (preg_match('`^https://`i', $url) ) {
    curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYPEER, TRUE); // + CURLOPT_CAINFO
    curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYHOST, 2); // default 2
  }

  $data = curl_exec($ch['curl']);
  
  return $data;
}

function get_curl_redirect($url) {
  global $ch;

  curl_setopt($ch['curl'], CURLOPT_URL, $url);
  curl_setopt($ch['curl'], CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");

  /* Crappy hack to add extra cookies, should be cleaned up */
  $cookies = NULL;
  foreach ($ch['cookie'] as $name => $value) {
    if (empty($cookies)) {
      $cookies = "$name=$value";
    } else {
      $cookies .= "; $name=$value";
    }
  }
  if ($cookies != NULL) {
    curl_setopt($ch['curl'], CURLOPT_COOKIE, $cookies);
    echo "$cookies";
  }

  curl_setopt($ch['curl'], CURLOPT_FOLLOWLOCATION, FALSE);
  curl_setopt($ch['curl'], CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch['curl'], CURLOPT_HEADER, true);
  curl_setopt($ch['curl'], CURLOPT_HTTPHEADER, array('Expect:'));
  curl_setopt($ch['curl'], CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch['curl'], CURLOPT_TIMEOUT, 30);
  curl_setopt($ch['curl'], CURLOPT_CONNECTTIMEOUT, 10);
  if (preg_match('`^https://`i', $url) ) {
    curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYPEER, TRUE); // + CURLOPT_CAINFO
    curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYHOST, 2); // default 2
  }

  $data = curl_exec($ch['curl']);
  $location = false;
  if (preg_match('~Location: (.*)~i', $data, $match)) {
    $location = trim($match[1]);
  }
  
  return $location;
}


function post_curl($url, $data) {
  global $ch;

  curl_setopt($ch['curl'], CURLOPT_URL, $url);
  curl_setopt($ch['curl'], CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");

  /* Crappy hack to add extra cookies, should be cleaned up */
  $cookies = NULL;
  foreach ($ch['cookie'] as $name => $value) {
    if (empty($cookies)) {
      $cookies = "$name=$value";
    } else {
      $cookies .= "; $name=$value";
    }
  }
  if ($cookies != NULL) {
    curl_setopt($ch['curl'], CURLOPT_COOKIE, $cookies);
  }
  curl_setopt($ch['curl'], CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($ch['curl'], CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch['curl'], CURLOPT_HTTPHEADER, array('Expect:',
                                                     'Content-Type: application/json'));
  curl_setopt($ch['curl'], CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch['curl'], CURLOPT_TIMEOUT, 30);
  curl_setopt($ch['curl'], CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch['curl'], CURLOPT_POST, 1);
  curl_setopt($ch['curl'], CURLOPT_POSTFIELDS, $data);

  $data = curl_exec($ch['curl']);

  return $data;
}

// argl
function curl_start2() {
  global $fichier_temp;
  global $ch;
  curl_close($ch['curl']);
  $ch['curl'] = curl_init();
  curl_setopt($ch['curl'], CURLOPT_COOKIEJAR, $fichier_temp);
  curl_setopt($ch['curl'], CURLOPT_COOKIEFILE, $fichier_temp);
  curl_setopt($ch['curl'], CURLOPT_MAXCONNECTS, 100);
  curl_setopt($ch['curl'], CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:87.0) Gecko/20100101 Firefox/87.0");
  curl_setopt($ch['curl'], CURLOPT_ENCODING , "");
  curl_setopt($ch['curl'], CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($ch['curl'], CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch['curl'], CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch['curl'], CURLOPT_HEADER, 1);
  curl_setopt($ch['curl'], CURLOPT_TIMEOUT, 30);
  curl_setopt($ch['curl'], CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYPEER, TRUE); // + CURLOPT_CAINFO
  curl_setopt($ch['curl'], CURLOPT_SSL_VERIFYHOST, 2); // default 2
}
function curl_get2($url, $cook=null, $header=true) {
  global $ch;
  curl_setopt($ch['curl'], CURLOPT_URL, $url);
  if (!$header) {
    curl_setopt($ch['curl'], CURLOPT_HEADER, 0);
  }
  if ($cook !== null) {
    curl_setopt($ch['curl'], CURLOPT_HTTPHEADER, $cook);
  }
  $data = curl_exec($ch['curl']);
  return $data;
}

// recherche pour un taxon supérieur
function m_uicn_infos_sup(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  init_outils();
  // pour obtenir un token de session
  $ret = get_data("https://www.iucnredlist.org/");
  
  // le CSRF
  $tmp = explode("\n", $ret);
  $code = false;
  foreach($tmp as $l) {
    $x = strpos($l, 'csrf-token');
    if ($x !== false) {
      $code = preg_replace('/^.*csrf-token" content="([^"]*).*$/', '${1}', $l);
      break;
    }
  }
  if ($code === false) {
    logs("UICN: impossible de trouver le CSRF");
    return false;
  }
  
  // on effectue une recherche
  $header = [
    'Accept: */*',
    'content-type: application/json',
    'X-CSRF-Token: ' . $code,
    'Referer: https://www.iucnredlist.org/',
    'DNT: 1',
  ];
  // table des rangs à tester
  $rangs = [
    'kingdomName' => '{"aggs":{"list":{"terms":{"field":"kingdomId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["kingdomName"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}',
    'phylumName' => '{"aggs":{"list":{"terms":{"field":"phylumId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["phylumName"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}',
    'className' => '{"aggs":{"list":{"terms":{"field":"classId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["className"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}',
    'orderName' => '{"aggs":{"list":{"terms":{"field":"orderId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["orderName"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}',
    'familyName' => '{"aggs":{"list":{"terms":{"field":"familyId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["familyName"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}',
    'genusName' => '{"aggs":{"list":{"terms":{"field":"genusId","size":100000,"min_doc_count":1},"aggs":{"nested":{"top_hits":{"size":1,"_source":{"includes":["*Id","*Name"]}}}}}},"query":{"bool":{"must":[{"multi_match":{"query":"' . $taxon . '","type":"phrase_prefix","fields":["genusName"],"lenient":true,"max_expansions":100}}],"filter":[{"term":{"isSpecies":true}}]}}}'
    ];

  foreach($rangs as $rang => $post) {
    $ret = post_data('https://www.iucnredlist.org/dosearch/assessments/_search?size=0&_source_excludes=ranges%2C%20legends&track_total_hits=true', $post, $header);
    $res = json_decode($ret);
    if ($res === null) {
      continue;
    }
    if (!isset($res->aggregations->list->buckets[0]->key)) {
      continue;
    }
    $found = false;
    foreach($res->aggregations->list->buckets as $buck) {
      $id = $buck->key;
      if (!isset($buck->nested->hits->hits[0]->_source->{$rang})) {
        continue;
      }
      $nom = $buck->nested->hits->hits[0]->_source->{$rang};
      if (isset($buck->nested->hits->hits[0]->_source->kingdomName)) {
        $king = $buck->nested->hits->hits[0]->_source->kingdomName;
      } else {
        $king = "animalia";
      }
      if (strcasecmp($taxon, $nom) != 0) {
        continue;
      } else {
        $found = true;
      }
    }
    $struct['liens']['uicn']['liste-id'] = $id;
    $struct['liens']['uicn']['liste-nom'] = $taxon;
    
    if (!$classif) {
      return true;
    }
    return false; // on ne fait pas la classification
  }
  
  return false;
}


function m_uicn_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // on compte le nombre de mots : si = 1 alors c'est pas une espèce
  $tmp = explode(" ", $taxon);
  if (count($tmp) == 1) {
    return m_uicn_infos_sup($struct, $classif);
  }

  /// TODO: ré-écrire le code avec les 'outils' et non les fonctions locales

  start_curl();
  // pour obtenir un token de session
  get_curl("https://www.iucnredlist.org/");
  $data = '{"stored_fields":["hasImage","hasPoints","hasRanges","image.id","image.url","image.urlThumb","image.credit","scopes.id","scopes.code","scopes.jsonDescription","kingdomName","className","commonName","scientificName","sisTaxonId","redListCategory.scaleCode","redListCategory.order","redListCategory.code","redListCategory.jsonDescription","populationTrend.id","populationTrend.code","populationTrend.jsonDescription"],"query":{"bool":{"must":[{"multi_match":{"query":"';
  $data .= $taxon;
  $data .= '","type":"phrase_prefix","fields":["commonName^12","commonNames^10","scientificName^8","keywords^4","synonyms^2","assessors","sisTaxonId","id"],"lenient":true,"max_expansions":100}}],"filter":{"bool":{"filter":[{"terms":{"scopes.code":["1"]}},{"terms":{"taxonLevel":["Species"]}}],"should":[]}},"should":[{"term":{"hasImage":{"value":true,"boost":6}}}]}},"sort":[{"_score":{"order":"desc"}}]}';

  $ret = post_curl("https://www.iucnredlist.org/dosearch/assessments/_search?size=60&_source=false&from=0", $data);
  $res = json_decode($ret);
  if ($res === null) {
    logs("UICN: erreur de décodage des informations UICN");
    return false;
  }
  if (!isset($res->hits->hits)) {
    logs("UICN: aucune information UICN trouvée");
    return false;
  }
  $id = null;
  $cat = null;
  foreach($res->hits->hits as $el) {
    if ($el->fields->scientificName[0] == $taxon) {
      $id = $el->fields->sisTaxonId[0];
      $cat = $el->fields->{'redListCategory.scaleCode'}[0];
      break;
    }
  }
  if ($id === null) {
    logs("UICN: aucune information UICN trouvée (2)");
    return false;
  }
  
  // on récupère la cible effective (c'est galère…)
  $url = "https://apiv3.iucnredlist.org/api/v3/taxonredirect/$id";
  $ret = get_curl_redirect($url);
  if ($ret === false) {
    logs("UICN: échec de récupération de la cible UICN");
    return false;
  }
  // récupération de l'identifiant de page
  $z = explode("/", $ret);
  if (!isset($z[5])) {
    logs("UICN: échec de récupération de la cible UICN (2)");
    return false;
  }
  $pageId = $z[5];
  
  // on repart de zéro : accès à la page elle-même
  curl_start2();
  $url = "https://www.iucnredlist.org/species/$id/$pageId";
  $ret = curl_get2($url);
  // le CSRF
  $tmp = explode("\n", $ret);
  $code = false;
  foreach($tmp as $l) {
    $x = strpos($l, 'csrf-token');
    if ($x !== false) {
      $code = preg_replace('/^.*csrf-token" content="([^"]*).*$/', '${1}', $l);
      break;
    }
  }
  if ($code === false) {
    logs("UICN: impossible de trouver le CSRF pour UICN");
    return false;
  }
  // le cookie de session…
  preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $ret, $matches);
  $cookies = array();
  foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
  }
  $c = [];
  $c[] = "X-CSRF-Token: $code";
  $c[] = "Referer: $url";
  foreach($cookies as $p1 => $p2) {
    $r = preg_replace("/=/", "", $p2);
    $c[] = "Cookie: $p1=$r";
  }
  $url = "https://www.iucnredlist.org/api/v4/species/$pageId";
  $ret = curl_get2($url, $c, false);
  $res = json_decode($ret);
  if ($res === null) {
    logs("UICN: erreur de décodage des informations UICN (2)");
    return false;
  }
  // on récupère les données
  if (isset($res->redListCategory->code)) {
    $struct['liens']['uicn']['risque'] = $res->redListCategory->code;
    if (isset($res->redListCategory->version)) {
      $struct['liens']['uicn']['commentaire'] = $res->redListCategory->version;
    }
    if (isset($res->redListCategory->criteria)) {
      $struct['liens']['uicn']['critere'] = $res->redListCategory->criteria;
    }
    $struct['liens']['uicn']['lien'] = $id;
    $struct['liens']['uicn']['nom'] = $res->taxon->scientificName;
    $struct['liens']['uicn']['auteur'] = $res->taxon->authority;
  } else {
    logs("UICN: impossible de trouver la catégorie");
    return false;
  }
  
  // si présent on récupère les noms en français
  $lst = [];
  if (isset($res->taxon->commonNames)) {
    foreach($res->taxon->commonNames as $cn) {
      if (isset($cn->language->en) and ($cn->language->en == "French")) {
        foreach($cn->names as $nn) {
          $lst[] = $nn;
        }
        break;
      }
    }
  }
  if (!empty($lst)) {
    $struct['vernaculaire']['UICN'] = $lst;
  }
  
  // test : récupération des informations de répartition géographique
  $lst = [];
  $ulst = [];
  if (isset($res->nativeLocations)) {
    foreach($res->nativeLocations as $loc) {
      if (isset($loc->presence) and ($loc->presence == "Presence Uncertain")) {
        $certain = false;
      } else {
        $certain = true;
      }
      if (isset($loc->origin) and ($loc->origin == "Native")) {
        foreach($loc->locations as $rloc) {
          if (isset($rloc->code)) {
            if ($certain) {
              $lst[$rloc->code] = $rloc->code;
            } else {
              $ulst[$rloc->code] = $rloc->code;
            }
          }
        }
      }
    }
  }
  if (!empty($lst) or !empty($ulst)) {
    $bundle = [];
    if (!empty($lst)) {
      $bundle['certain'] = $lst;
    }
    if (!empty($ulst)) {
      $bundle['uncertain'] = $ulst;
    }
    $struct['distribution']['UICN'] = $bundle;
  }

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // TODO : partie classification
  return false;
}


function m_uicn_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['uicn']['lien'])) {
    $data = $struct['liens']['uicn'];
    $cible = wp_met_italiques($data['nom'], $struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    return "{{UICN | " . $data['lien'] . " | " . $cible . " | consulté le=$cdate }}";
  } else if (isset($struct['liens']['uicn']['liste-id'])) {
    $data = $struct['liens']['uicn'];
    $cible = wp_met_italiques($data['liste-nom'], $struct['taxon']['rang'], $struct['regne']);
    return "{{UICN taxons | " . $data['liste-id'] . " | " . $data['liste-nom'] . " | consulté le=$cdate }}";
  } else {
    return false;
  }
}

function m_uicn_liens($struct) {
  if (isset($struct['liens']['uicn']['lien'])) {
    return "<a href='https://apiv3.iucnredlist.org/api/v3/taxonredirect/" .
           $struct['liens']['uicn']['lien'] . "</a>";
  } else if (isset($struct['liens']['uicn']['liste-id'])) {
    $data = $struct['liens']['uicn'];
    return "<a href='https://www.iucnredlist.org/search?taxonomies=" . $data['liste-id'] .
           "&searchType=species'>UICN taxons</a>";
  } else {
    return false;
  }
}

