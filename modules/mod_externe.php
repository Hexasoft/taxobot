<?php

/*
  Récupère des liens externes (WD, commons, species…)
*/

function m_externe_init() {
  return declare_module("externe", false, false, true);
}

function wiki_constructor($subdom, $dom, $title, $action = null) {
  $valid_dom = ['wikimedia', 'wikidata', 'wikipedia', 'wiktionary'];
  $url = 'https://';

  if (!empty($subdom) && in_array($dom, $valid_dom) && !empty($title)) {
      $url .= $subdom . '.' . $dom . '.org/w/index.php?title=' . urlencode($title);

      if (isset($action)) {
          $url .= '&action=' . $action;
      }

      return $url;
  } else {
      error("wiki_constructor: paramètres non valides");
      return null;
  }
}

function has_wiki_pages($title) {
  $commons_url = wiki_constructor('commons', 'wikimedia', $title, 'info');
  $cat_commons_url = wiki_constructor('commons', 'wikimedia', 'Category:' . $title, 'info'); 
  $species_url = wiki_constructor('species', 'wikimedia', $title, 'info');
  $fr_wiktionary_url = wiki_constructor('fr', 'wiktionary', $title, 'info');

  $commons_exists = page_exists($commons_url);
  $cat_commons_exists = page_exists($cat_commons_url);
  $species_exists = page_exists($species_url);
  $fr_wiktionary_exists = page_exists($fr_wiktionary_url);

  return [$commons_exists, $cat_commons_exists, $species_exists, $fr_wiktionary_exists];
}

function page_exists($url) {
  $ret = get_data($url);
  if (empty($ret)) {
    logs("Externe: probleme réseau");
    return false;
  }

  if (strpos($ret, '"wgArticleId":0') == true) {
    return false; // Page does not exist
  }
  return true; // Page exists
}

function m_externe_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  /// wikidata
  // pour obtenir le cookie
  $ret = get_data("https://query.wikidata.org/");
  $url = "https://query.wikidata.org/sparql?query=";
  $sel = "SELECT ?item WHERE { ?item wdt:P31 wd:Q16521 ; wdt:P225 \"" . $taxon . "\" . }";
  $url .= urlencode($sel);
  $ret = get_data($url);
  if ($ret === false) {
    logs("Externe/WD: echec de récupération réseau");
  }

  // on tente d'extraire le résultat
  $tbl = explode("\n", $ret);
  $ok = false;
  foreach($tbl as $t) {
    $tst = strpos($t, "www.wikidata.org/entity/");
    if ($tst === false) {
      continue;
    }
    $id = preg_replace(",^.*wikidata.org/entity/(Q[0-9]*)<.*$,", '$1', $t);
    $ok = true;
    break;
  }

  if (!$ok OR empty($id)) {
    logs("Externe/WD: taxon non trouvé");
  } else {
  $struct['liens']['externe']['wikidata']['id'] = $id;
  }

  list($commons_exists, $cat_commons_exists, $species_exists, $fr_wiktionary_exists) = has_wiki_pages($taxon);

  $struct['liens']['externe']['commons']['page'] = $commons_exists ? $taxon : null;
  $struct['liens']['externe']['ccommons']['page'] = $cat_commons_exists ? 'Category:' . $taxon : null;
  $struct['liens']['externe']['species']['page'] = $species_exists ? $taxon : null;
  $struct['liens']['externe']['frwiktionary']['page'] = $fr_wiktionary_exists ? $taxon : null;

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }

  // pas de classif ici
  return false;
}

function m_externe_ext($struct) {
  return false;
}

function m_externe_liens($struct) {
  $out = [];
  if (isset($struct['liens']['externe']['wikidata']['id'])) {
    $out[] = "<a href='https://www.wikidata.org/wiki/" . $struct['liens']['externe']['wikidata']['id'] .
             "'>Wikidata</a>";
  }
  if (isset($struct['liens']['externe']['species']['page'])) {
    $out[] = "<a href='https://species.wikimedia.org/wiki/" .
             $struct['liens']['externe']['species']['page'] . "'>Species</a>";
  }
  if (isset($struct['liens']['externe']['commons']['page'])) {
    $out[] = "<a href='https://commons.wikimedia.org/wiki/" .
             $struct['liens']['externe']['commons']['page'] . "'>Commons (page)</a>";
  }
  if (isset($struct['liens']['externe']['ccommons']['page'])) {
    $out[] = "<a href='https://commons.wikimedia.org/wiki/" .
             $struct['liens']['externe']['ccommons']['page'] . "'>Commons (cat)</a>";
  }
  if (isset($struct['liens']['externe']['frwiktionary']['page'])) {
    $out[] = "<a href='https://commons.wikimedia.org/wiki/" .
             $struct['liens']['externe']['frwiktionary']['page'] . "'>Wiktionnaire</a>";
  }

  if (!empty($out)) {
    return $out;
  } else {
    return false;
  }
}