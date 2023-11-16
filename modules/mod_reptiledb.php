<?php

/*
  Données ReptileDB
*/

function m_reptiledb_init() {
  return declare_module("reptiledb", false, true, ['reptile']);
}

function m_reptiledb_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  if (!$classif) {
    // on ne gère que les espèces, genres et familles
    $rang = $struct['taxon']['rang'];
    if (($rang != 'espèce') && ($rang != 'genre') && ($rang != 'famille')) {
      logs("ReptileDB: uniquement espèce, genre ou famille");
      return false;
    }
  }

  $l = explode(" ", $taxon);
  if (isset($l[1])) {
    $espece = true;
    $p1 = $l[0];
    $p2 = $l[1];
    $url = "https://reptile-database.reptarium.cz/species?genus=$p1&species=$p2";
    $result['taxon']['rang'] = 'espèce';
  } else {
    $espece = false;
    $p1 = $taxon;
    $p2 = false;
    $url = "https://reptile-database.reptarium.cz/advanced_search?genus=$p1&exact%5B0%5D=genus&submit=Search";
  }

  // récupération données
  $ret = get_data($url);

  // erreur CURL
  if ($ret === false) {
    logs("ReptileDB: erreur réseau");
    return false;
  }
  // non trouvé
  $tmp = strpos($ret, "No species were found");
  if ($tmp !== false) {
    logs("ReptileDB: taxon non trouvé chez ReptileDB");
    return false;
  }
  $tmp = strpos($ret, "was not found");
  if ($tmp !== false) {
    logs("ReptileDB: taxon non trouvé chez ReptileDB");
    return false;
  }

  // on vérifie que c'est une page correcte
  $tmp = strpos($ret, "Subspecies");
  if ($tmp === false) {
    logs("ReptileDB: résultat erroné chez ReptileDB");
    return false;
  }

  // extraction du nom scientifique et de l'auteur
  if ($espece) {
    preg_match_all(',^.*<h1>(.*)</h1>$,mi', $ret, $matches);
    if (isset($matches[1][0])) {
      $ns = explode("<", $matches[1][0])[1];
      $ns = explode(">", $ns)[1];
      $aut = trim(explode(">", $matches[1][0])[2]);
    } else {
      $aut = "";
    }
  } else {
    // auteur dispo que sur les espèces
    $aut = "";
  }

  // puisque la page existe, on peut mettre les infos pour les liens externes
  $struct['liens']['reptiledb'] = [];
  if ($p2 !== false) {
    $struct['liens']['reptiledb']['type'] = 'espèce';
  } else {
    $struct['liens']['reptiledb']['type'] = 'genre';
  } // ajouter taxon supérieur
  $struct['liens']['reptiledb']['nom1'] = $p1;
  $struct['liens']['reptiledb']['nom2'] = $p2;
  $struct['liens']['reptiledb']['auteur'] = $aut;

  // terminé
  if (!$classif) {
    return true;
  }

  // si classification et autre chose qu'espèce → non
  if (!$espece) {
    logs("ReptileDB: ne peut être utilisé que pour les espèces");
    return false;
  }

  // TODO

  return false;
}

function m_reptiledb_ext($struct) {
  $cdate = dates_recupere();
  if (!isset($struct['liens']['reptiledb'])) {
    return false;
  }
  $data = $struct['liens']['reptiledb'];

  if ($data['type'] == "espèce") {
    return "{{ReptileDB espèce | " . $data['nom1'] . " | " . $data['nom2'] . " | " . $data['auteur'] .
            " | consulté le=$cdate }}";
  } elseif ($data['type'] == "genre") {
    return "{{ReptileDB genre | " . $data['nom1'] . " | " . $data['auteur'] .
            " | consulté le=$cdate }}";
  }
}

function m_reptiledb_liens($struct) {
  if (isset($struct['liens']['reptiledb'])) {
    if ($struct['liens']['reptiledb']['type'] == "espèce") {
      return "<a href='http://reptile-database.reptarium.cz/species.php?genus=" .
             $struct['liens']['reptiledb']['nom1'] . "&species=" .
             $struct['liens']['reptiledb']['nom2'] . "&exact%5B0%5D=genus&exact%5B0%5D=species" .
             "'>ReptileDB</a>";
    }
  } else {
    return false;
  }
}