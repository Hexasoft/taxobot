<?php

/*
  Module pour IRMNG (non classification)
*/

// déclaration du module
function m_irmng_init() {
  return declare_module("irmng", false, true, true);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_irmng_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // accès pour extraire les cookies
  get_clear();
  $url = "https://www.irmng.org/aphia.php?p=search";
  $ret = get_data($url);
  if ($ret === false) {
    logs("IRMNG: echec de récupération réseau");
    return false;
  }
  $url = "https://www.irmng.org/aphia.php?p=taxlist";
  $post = "searchpar=0&tComp=is&action=search&rSkips=0&adv=0&tName=" .
          str_replace(" ", "+", $taxon);
  $header = [
     "Host: www.irmng.org",
     "Origin: https://www.irmng.org",
	 "Referer: https://www.irmng.org/aphia.php?p=search",
	 'Content-Type: application/x-www-form-urlencoded',
	 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
	 'Upgrade-Insecure-Requests: 1',
	 'Sec-Fetch-Dest: document',
	 'Sec-Fetch-Mode: navigate',
	 'Sec-Fetch-Site: same-origin',
	 'Sec-Fetch-User: ?1',
	 'TE: trailers',
  ];

  $ret = post_data_header($url, $post, $header, false);
  if ($ret === false) {
    logs("IRMNG: echec de récupération réseau (2)");
    return false;
  }
  $tbl =explode("\n", $ret);
  $ok = false;
  foreach($tbl as $ligne) {
    if (strpos($ligne, "location: https://www.irmng.org/aphia.php?p=taxdetails&id=") === false) {
      continue;
    }
    $ok = preg_replace("/^location.*id=/", "", $ligne);
    $ok = preg_replace("/^([0-9]*).*$/", '$1', $ok);
    break;
  }
  if ($ok === false) {
    logs("IRMNG: taxon non trouvé");
    return false;
  }
  
  // trouvé, on note l'ID et on voit pour d'autres infos
  $blob = [];
  $blob['id'] = $ok;
  $blob['nom'] = $taxon;
  // extraction des infos détaillées
  $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $ok;
  $ret = get_data($url);
  if ($ret !== false) {
    $tbl = explode("\n", $ret);
    foreach($tbl as $idx => $ligne) {
      if (strpos($ligne, "class=\"h5 aphia_core_header-inline") !== false) {
        $comp = strip_tags(trim($tbl[$idx-1]));
        $auteur = str_replace($taxon, "", $comp);
        $auteur = trim($auteur);
        if (!empty($auteur)) {
          $blob['auteur'] = $auteur;
        }
        continue;
      }
      if (strpos($ligne, "Status</label>") !== false) {
        $status = strip_tags(trim($tbl[$idx+4]));
        if (!empty($status)) {
          if ($status != "accepted") {
            $blob['synonyme'] = true;
          }
        }
        continue;
      }
      if (strpos($ligne, "Accepted Name") !== false) {
        $cible = strip_tags(trim($tbl[$idx+4]));
        if (!empty($cible)) {
          if ($status != "accepted") {
            $blob['cible'] = $cible;
          }
        }
        continue;
      }
    }
  }
  
  // on stocke les infos
  $struct['liens']['irmng'] = $blob;

  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }
  
  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_irmng_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['irmng'])) {
    $data = $struct['liens']['irmng'];
    $cible = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    if (isset($data['synonyme']) and $data['synonyme']) {
      $post = " </small>(non valide";
      if (isset($data['cible']) and !empty($data['cible'])) {
        $post .= " → " . $data['cible'] . ")</small>";
      } else {
        $post .= ")</small>";
      }
    } else {
      $post = "";
    }
    return "{{IRMNG | " . $data['id'] . " | " . $cible . " | consulté le=$cdate }}$post";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_irmng_liens($struct) {
  if (isset($struct['liens']['irmng']['id'])) {
    return "<a href='http://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $struct['liens']['irmng']['id'] .
           "'>WoRMS</a>";
  } else {
    return false;
  }
}


