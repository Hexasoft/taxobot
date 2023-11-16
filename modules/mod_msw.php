<?php

/*
  Module pour msw (non classification)
*/

// déclaration du module
function m_msw_init() {
  return declare_module("msw", false, true, ['animal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_msw_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // on accède au site pour avoir un cookie
  clean_data();
  $url = "https://www.departments.bucknell.edu/biology/resources/msw3/";
  $ret = get_data($url);
  if ($ret === false) {
    logs("MSW: erreur réseau");
    return false;
  }
  // on fait une recherche
  $url = "https://www.departments.bucknell.edu/biology/resources/msw3/search.asp";
  $header = [ 'Referer: https://www.departments.bucknell.edu/biology/resources/msw3/',
              'Origin: https://www.departments.bucknell.edu' ];
  $post = "s=" . str_replace(" ", "+", $taxon) . "&Submit=Submit";
  $ret = post_data($url, $post, $header);
  if ($ret === false) {
    logs("MSW: erreur réseau");
    return false;
  }
  // on parcours le résultat pour chercher l'entrée concernée
  $tbl = explode("\n", $ret);
  $data = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, 'input name="searchResults"') === false) {
      continue;
    }
    // extraction des cibles
    $data = preg_replace("/^.* value=\"/", "", $ligne);
    $data = preg_replace("/\">.*$/", "", $data);
    break;
  }
  if ($data === false) {
    logs("MSW: taxon non trouvé");
    return false;
  }
  $url = "https://www.departments.bucknell.edu/biology/resources/msw3/export.asp?s=y";
  $post = "searchResults=$data";
  $header = [ "Content-Type: application/x-www-form-urlencoded",
              'Origin: https://www.departments.bucknell.edu',
              "Host: www.departments.bucknell.edu",
              "Referer: https://www.departments.bucknell.edu/biology/resources/msw3/search.asp",
              "TE: Trailers",
            ];
  $ret = post_data($url, $post, $header);
  if ($ret === false) {
    logs("MSW: erreur réseau (3)");
    return false;
  }
  // on parcours les entrées
  $tbl = explode("\n", $ret);
  $id = false;
  $ln = [];
  $prem = true;
  foreach($tbl as $ligne) {
    if ($prem) {
      $prem = false;
      continue;
    }
    $ch = explode(",", $ligne);
    foreach($ch as $idx => $el) {
      $ch[$idx] = str_replace('"', "", $el);
    }
    // construction du nom scientifique concerné
    $nom = "";
    if (!empty($ch[11])) {
      $nom .= " " . $ch[11];
    }
    if (!empty($ch[10])) {
      $nom = " " . $ch[10] . $nom;
    }
    if (!empty($ch[8])) {
      $nom = " " . $ch[8] . $nom;
    }
    for($i=6; $i>=1; $i--) {
      if (empty($nom)) {
        if (!empty($ch[$i])) {
          $nom = $ch[$i];
        }
      }
    }

    $nom = trim($nom);
    if (empty($nom)) {
      continue; // pas de nom
    }
    if ($nom == $taxon) {
      $id = $ch[0];
      $ln = $ch;
      break;
    }
  }

  if ($id === false) {
    logs("MSW: taxon non trouvé (2)");
    return false;
  }

  $blob = [];
  $blob['nom'] = $taxon;
  $blob['id'] = $id;
  // utf8_encode() is deprecated
  $chaine_source = $ch[16] . ", " . $ch[17];
  $blob['auteur'] = mb_convert_encoding($chaine_source, 'UTF-8', 'ISO-8859-1'); // msw3 header = ISO-8859-1
  $struct['liens']['msw'] = $blob;

  if (!$classif) {
    return true;
  }
  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_msw_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['msw']['id'])) {
    // → ne pas mettre d'italiques, le modèle s'en occupe
    $cible = wp_met_italiques($struct['liens']['msw']['nom'],
                  $struct['taxon']['rang'], $struct['regne'], false);
    return "{{MSW | " . $struct['liens']['msw']['id'] . " | " . $cible . " | " . $struct['liens']['msw']['auteur'] .
             " | " . "consulté le=$cdate }}";
  }
  return false;
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_msw_liens($struct) {
  if (isset($struct['liens']['msw']['id'])) {
    return "<a href='https://www.departments.bucknell.edu/biology/resources/msw3/browse.asp?id=" .
           $struct['liens']['msw']['id'] . "'>MSW</a>";
  }
  return false;
}