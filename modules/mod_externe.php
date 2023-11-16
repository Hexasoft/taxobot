<?php

/*
  Récupère des liens externes (WD, commons, species…)
*/

function m_externe_init() {
  return declare_module("externe", false, false, true);
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
    goto catcommons;
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

  if (!$ok) {
    logs("Externe/WD: taxon non trouvé");
    goto catcommons;
  }
  if (empty($id)) {
    logs("Externe/WD: taxon non trouvé (2)");
    goto catcommons;
  }

  $struct['liens']['externe']['wikidata']['id'] = $id;

  /// catégorie commons
catcommons:
  $url = "https://commons.wikimedia.org/w/index.php?title=Category:" .
         urlencode($taxon) .  "&action=raw";
  $ret = get_data($url);
  if (($ret == false) or empty($ret)) {
    logs("Externe/CatCommons: probleme réseau");
    goto commons;
  }
  $tbl = explode("\n", $ret);
  foreach($tbl as $l) {
    if (strpos($l, "<h1>Error</h1>") !== false) {
      logs("Externe/CatCommons: taxon non trouvé");
      goto commons;
    }
  }
  $struct['liens']['externe']['ccommons']['page'] = $taxon;

  /// commons (page)
commons:
  $url = "https://commons.wikimedia.org/w/index.php?title=" .
         urlencode($taxon) .  "&action=raw";
  $ret = get_data($url);
  if (($ret == false) or empty($ret)) {
    logs("Externe/PageCommons: probleme réseau");
    goto species;
  }
  $tbl = explode("\n", $ret);
  foreach($tbl as $l) {
    if (strpos($l, "<h1>Error</h1>") !== false) {
      logs("Externe/PageCommons: taxon non trouvé");
      goto species;
    }
  }
  $struct['liens']['externe']['commons']['page'] = $taxon;

  /// species
species:
  $url = "https://species.wikimedia.org/w/index.php?title=" .
         urlencode($taxon) .  "&action=raw";
  $ret = get_data($url);
  if (($ret == false) or empty($ret)) {
    logs("Externe/Species: probleme réseau");
    goto suite;
  }
  $struct['liens']['externe']['species']['page'] = $taxon;

suite:
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
  if (!empty($out)) {
    return $out;
  } else {
    return false;
  }
}