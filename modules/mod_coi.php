<?php

/*
  Module de classification COI (à faire)
*/

// déclaration du module
function m_coi_init() {
  // permet classification, liens externes et accepte tous les domaines
  return declare_module("coi", false, true, [ "oiseau" ], 10);
}

// récupère les données générales liées à COI
function m_coi_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  $data = file_get_contents("./data/coi.csv");
  if ($data === false) {
    logs("COI: échec de récupération du fichier local");
    return false;
  }
  $tbl = explode("\n", $data);
  $classe = "";
  $ordre = "";
  $famille = "";
  $genre = "";
  $espece = "";
  $ssp = "";
  $trouve = false;
  foreach($tbl as $ligne) {
    // extraction des infos de la ligne
    $out = str_getcsv($ligne);
    $out = array_splice($out, 1);
    if (empty($out[3])) {
      continue;
    }
    $test = (strtoupper($out[3]) == $out[3]);
    $nb = str_word_count($out[3]);
    // on trie selon la configuration
    if ($test && ($nb == 1)) {
      $classe = ucfirst(strtolower(trim($out[3])));
      $ordre = "";
      $famille = "";
      $genre = "";
      $espece = "";
      $ssp = "";
    } else if ($test && ($nb == 2)) {
      // présence de "ORDER" ?
      $xpl = explode(" ", $out[3]);
      if ($xpl[0] != "ORDER") {
        // non reconnu
        logs("COI: ligne non reconnue (ignorée)");
        continue;
      }
      // extraction nom
      $ordre = ucfirst(strtolower(trim($xpl[1])));
      $famille = "";
      $genre = "";
      $espece = "";
      $ssp = "";
    } else if (!$test && ($nb == 2)) {
      // famille ou espèce
      $xpl = explode(" ", $out[3]);
      if ($xpl[0] == 'Family') {
        $famille = trim($xpl[1]);
        $genre = "";
        $espece = "";
        $ssp = "";
      }
      // espèce
      $espece = trim($out[3]);
      $ssp = "";
    } else if (!$test && ($nb == 1)) {
      $genre = trim($out[3]);
      $espece = "";
      $ssp = "";
    } else if (!$test && ($nb == 3)) {
      // sous-espèce
      $ssp = trim($out[3]);
    } else {
      logs("COI: ligne non reconnue (2) (ignorée)");
      continue;
    }
    // on regarde le taxon le plus précis courant
    $courant = "";
    $type = "";
    $data = $out;
    if (!empty($ssp)) {
      $courant = $ssp;
      $type = "sous-espèce";
    } else if (!empty($espece)) {
      $courant = $espece;
      $type = "espece";
    } else if (!empty($genre)) {
      $courant = $genre;
      $type = "genre";
    } else if (!empty($famille)) {
      $courant = $famille;
      $type = "famille";
    } else if (!empty($ordre)) {
      $courant = $ordre;
      $type = "ordre";
    } else if (!empty($classe)) {
      $courant = $classe;
      $type = "classe";
    } else {
      logs("COI: type inconnu. Erreur");
      return false;
    }
    // est-ce qu'on a trouvé notre taxon ?
    if ($taxon == $courant) {
      $trouve = true;
      break;
    }
  }
  if (!$trouve) {
    logs("COI: taxon non trouvé");
    return false;
  }
  
  // on a trouvé le taxon chez COI
  if (($type == "sous-espèce") || ($type == "espece") || ($type == "genre")) {
    $auteur = $data[4];
  } else {
    $auteur = "";
  }
  
  $blob = [];
  $blob['nom'] = $courant;
  if (!empty($auteur)) {
    $blob['auteur'] = $auteur;
  }
  $blob['rang'] = $type;
  $blob['ligne'] = $data[0];
  $struct['liens']['coi'] = $blob;

  if (!$classif) {
    return true;
  }
  return false;
}

// retourne les liens externes liés à COI (si présents)
function m_coi_ext($struct) {
  if (isset($struct['liens']['coi'])) {
    $data = $struct['liens']['coi'];
    $cdate = dates_recupere();
    $texte = wp_met_italiques($data['nom'], $data['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $texte .= " " . $data['auteur'];
    }
    $texte .= " (ligne " . $data['ligne'] . ")";
    return "{{COI | | $texte | consulté le=$cdate }}";
  } else {
    return false;
  }
}

// retourne les liens HTTP directs liés à COI (si présents)
function m_coi_liens($struct) {
  if (isset($struct['liens']['coi'])) {
    return "<a href='https://www.worldbirdnames.org/bow/'>COI (ligne " . $struct['liens']['coi']['ligne'] . ")</a>";
  } else {
    return false;
  }
}

