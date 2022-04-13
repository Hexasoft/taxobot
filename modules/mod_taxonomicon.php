<?php

/*
  Module pour taxonomicon (non classification)
*/

// déclaration du module
function m_taxonomicon_init() {
  return declare_module("taxonomicon", false, true, true);
}

/*
curl 'http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Taxon&by=ScientificName&search=' -X POST -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp;q=0.8' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Content-Type: application/x-www-form-urlencoded' -H 'Origin: http://taxonomicon.taxonomy.nl' -H 'Connection: keep-alive' -H 'Referer: http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Taxon&by=ScientificName&search=' -H 'Cookie: ASP.NET_SessionId=mkmg20rieofxlmh2tv2j1vhh' -H 'Upgrade-Insecure-Requests: 1' --data-raw '__VIEWSTATE=%2FwEPDwUKMTg5NzEwMzU2MQ9kFgJmD2QWAgIDD2QWCAIJDxYCHglpbm5lcmh0bWwFAjogZAILDxYCHwAF2wE8dWwgY2xhc3M9IkNvbnRleHRNZW51Ij48bGk%2BPHNwYW4gY2xhc3M9IkNvbnRleHRNZW51Ij5UYXhhPC9zcGFuPjwvbGk%2BPGxpPjxhIGNsYXNzPSJDb250ZXh0TWVudSIgaHJlZj0iVGF4b25UcmVlLmFzcHgiPkJyb3dzZSBUcmVlPC9hPjwvbGk%2BPGxpPjxhIGNsYXNzPSJDb250ZXh0TWVudSIgaHJlZj0iVGF4b25TZWFyY2guYXNweCI%2BQWR2YW5jZWQgU2VhcmNoPC9hPjwvbGk%2BPC91bD5kAg0PFgIfAAVsPHVsIGNsYXNzPSJUYWJTdHJpcCI%2BPGxpIGNsYXNzPSJTaW5nbGVUYWIiPjxzcGFuIGNsYXNzPSJUYWJTdHJpcCI%2BIFNlYXJjaCByZXN1bHRzOiAwICBmb3VuZDwvc3Bhbj48L2xpPjwvdWw%2BZAIRD2QWAgIBDxYCHwAF3gs8cD5ObyBkYXRhIGF2YWlsYWJsZS48L3A%2BDQo8aDI%2BT3RoZXIgc2VhcmNoIG9wdGlvbnM8L2gyPg0KPHRhYmxlPg0KCTx0cj4NCgkJPHRkPlNlYXJjaCAnRW50aXR5IGJ5IFNjaWVudGlmaWMgTmFtZSc6PC90ZD48dGQ%2BPGEgY2xhc3M9Ikl0ZW0iIGhyZWY9Ii9UYXhvbkxpc3QuYXNweD9zdWJqZWN0PVRheG9uJmJ5PVNjaWVudGlmaWNOYW1lJnNlYXJjaD0qIj5zdGFydHMgd2l0aCAnJzwvYT4NCgkJPC90ZD48L3RyPg0KCTx0cj4NCgkJPHRkIC8%2BPHRkPjxhIGNsYXNzPSJJdGVtIiBocmVmPSIvVGF4b25MaXN0LmFzcHg%2Fc3ViamVjdD1UYXhvbiZieT1TY2llbnRpZmljTmFtZSZzZWFyY2g9KiI%2BZW5kcyB3aXRoICcnPC9hPg0KCQk8L3RkPjwvdHI%2BDQoJPHRyPg0KCQk8dGQgLz48dGQ%2BPGEgY2xhc3M9Ikl0ZW0iIGhyZWY9Ii9UYXhvbkxpc3QuYXNweD9zdWJqZWN0PVRheG9uJmJ5PVNjaWVudGlmaWNOYW1lJnNlYXJjaD0qKiI%2BY29udGFpbnMgJyc8L2E%2BDQoJCTwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZD5Vc2Ugd2lsZGNhcmRzIGluIHlvdXIgc2VhcmNoIHN0cmluZzo8L3RkPjx0ZD4nKicgbWF0Y2hlcyB6ZXJvIG9yIG1vcmUgY2hhcmFjdGVyczwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZCAvPjx0ZD4nPycgbWF0Y2hlcyBhIHNpbmdsZSBjaGFyYWN0ZXI8L3RkPjwvdHI%2BDQoJPHRyPg0KCQk8dGQgLz48dGQ%2BJ1thZWlvdV0nIG1hdGNoZXMgdGhlIGdpdmVuIHNldCBvZiBjaGFyYWN0ZXJzPC90ZD48L3RyPg0KCTx0cj4NCgkJPHRkIC8%2BPHRkPidbYS1jXScgbWF0Y2hlcyB0aGUgZ2l2ZW4gcmFuZ2Ugb2YgY2hhcmFjdGVyczwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZD5TZWFyY2ggZXh0ZXJuYWwgc2VhcmNoIGVuZ2luZXM6PC90ZD48dGQ%2BPGEgY2xhc3M9Ikl0ZW0iIGhyZWY9Imh0dHBzOi8vd3d3Lmdvb2dsZS5jb20vc2VhcmNoP251bT0xMCZhbXA7cXVlcnk9JTIyJTIyIiB0YXJnZXQ9Il9ibGFuayI%2BR29vZ2xlPC9hPjwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZCAvPjx0ZD48YSBjbGFzcz0iSXRlbSIgaHJlZj0iaHR0cDovL3NlYXJjaC55YWhvby5jb20vc2VhcmNoP3A9JTIyJTIyIiB0YXJnZXQ9Il9ibGFuayI%2BWWFob28hPC9hPjwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZCAvPjx0ZD48YSBjbGFzcz0iSXRlbSIgaHJlZj0iaHR0cDovL3d3dy5iaW5nLmNvbS9zZWFyY2g%2FcT0lMjIlMjIiIHRhcmdldD0iX2JsYW5rIj5CaW5nPC9hPjwvdGQ%2BPC90cj4NCgk8dHI%2BDQoJCTx0ZD5TZWFyY2ggZXh0ZXJuYWwgd2Vic2l0ZXM6PC90ZD48dGQ%2BPGEgY2xhc3M9Ikl0ZW0iIGhyZWY9Imh0dHA6Ly9lbi53aWtpcGVkaWEub3JnL3dpa2kvIiB0YXJnZXQ9Il9ibGFuayI%2BV2lraXBlZGlhIChFbmdsaXNoKTwvYT48L3RkPjwvdHI%2BDQoJPHRyPg0KCQk8dGQgLz48dGQ%2BPGEgY2xhc3M9Ikl0ZW0iIGhyZWY9Imh0dHA6Ly9ubC53aWtpcGVkaWEub3JnL3dpa2kvIiB0YXJnZXQ9Il9ibGFuayI%2BV2lraXBlZGlhIChEdXRjaCk8L2E%2BPC90ZD48L3RyPg0KPC90YWJsZT4NCmRkmHHYFpRBEzm2modhlwWo7gfp9bcjKm8Ycy6HF%2FCu%2Fb8%3D
&__EVENTVALIDATION=%2FwEWFgLs06LTBQLRtfGaDgKylZX4BAKfy%2B3%2BBwL97c3JDgLrnZmbAwLPm5DbBwKvrbCfBwLX4Zz0DQKLsK7hCQLh6Z6LCAKCpvqEAgL8gOXDAgLIqPbgDQLlzbbwCgK0oePODALjvPfDCgLB9O%2FyDQLXhLnuDQKZmqOtCQLApPGVAgL3tfOMC%2FRa3fzc44zVg0HSiUWPnUBVkoUNr72d1ahNHwr5WQaD&ctl00%24ddlQuickSearch=Entity+by+Scientific+Name
&ctl00%24txtQuickSearch=Pimplinae&ctl00%24btnQuickSearch=Go'

curl 'http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Entity&by=ScientificName&search=Pimplinae' -H 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp;q=0.8' -H 'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3' -H 'Accept-Encoding: gzip, deflate' -H 'Referer: http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Taxon&by=ScientificName&search=' -H 'Connection: keep-alive' -H 'Cookie: ASP.NET_SessionId=mkmg20rieofxlmh2tv2j1vhh' -H 'Upgrade-Insecure-Requests: 1'


*/


// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_taxonomicon_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  $ret = get_data("http://taxonomicon.taxonomy.nl");
  $url = "http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Entity&by=ScientificName&search=" . str_replace(" ", "+", $taxon);
  $header = [
    'Referer: http://taxonomicon.taxonomy.nl/TaxonList.aspx?subject=Taxon&by=ScientificName&search=',
    'Upgrade-Insecure-Requests: 1',
  ];
  $ret = get_data($url, $header);
  if ($ret === false) {
    logs("Taxonomicon: échec de la recherche");
    return false;
  }
  
  $tbl = explode("\n", $ret);
  $found = false;
  foreach($tbl as $idx => $ligne) {
    if (strpos($ligne, "Search results: 0 entities found") !== false) {
      logs("Taxonomicon: taxon non trouvé");
      return false;
    }
    if (strpos($ligne, '<a class="Valid" href="TaxonTree') !== false) {
      $struct['liens']['taxonomicon']['nom'] = $taxon;
      // extraction id
      $x = explode("=", $ligne);
      if (!isset($x[3])) {
        continue;
      }
      $y = explode("&", $x[3]);
      if (!isset($y[0])) {
        continue;
      }
      $struct['liens']['taxonomicon']['id'] = $y[0];
      // à partir d'ici c'est ok
      $trouve = true;
      // le nom du taxon
      $n = trim(strip_tags(preg_replace("/[(][^)]*[)]/", "",
                  preg_replace("/<span class=\"Authorship.*$/", "",
                    preg_replace("/^.*<span class=\"Taxon\">/", "", $ligne)))));
      if (!empty($n)) {
        $struct['liens']['taxonomicon']['nom'] = $n;
      }
      // l'auteur
      $a = trim(preg_replace(",</span.*$,", "", preg_replace("/^.*<span class=\"Authorship\">/", "", $ligne)));
      if (!empty($a)) {
        $struct['liens']['taxonomicon']['auteur'] = $a;
      }
      // il faudrait le rang, mais ça nécessite une conversion pas forcément utile
      break;
    }
  }
  

  if (!$trouve) {
    logs("Taxonomicon: taxon non trouvé (2)");
    return false;
  }
  
  if (!$classif) {
    return true;
  }
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_taxonomicon_ext($struct) {
  if (isset($struct['liens']['taxonomicon']['id'])) {
    $data = $struct['liens']['taxonomicon'];
    $cdate = dates_recupere();
    
    $nom = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $nom .= " " . $data['auteur'];
    }
    return "{{Taxonomicon | $id | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_taxonomicon_liens($struct) {
  if (isset($struct['liens']['taxonomicon']['id'])) {
    return "<a href='http://taxonomicon.taxonomy.nl/TaxonTree.aspx?id=" . $struct['liens']['taxonomicon']['id'] . "&src=0"
            . "'>Taxonomicon</a>";
  } else {
    return false;
  }
}

