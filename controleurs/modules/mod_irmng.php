<?php


/**
 * Module - Interim Register of Marine and Nonmarine Genera (IRMNG)
 *
 * Ce module permet de récupérer des informations sur un taxon à partir du site IRMNG.
 * Il génère des liens externes et des liens internes pour le site, mais il ne génère aucune classification.
 *
 */

/**
 * Déclare le module IRMNG.
 * 
 * @return array|bool
 */
// déclaration du module
function m_irmng_init() {
  return declare_module("irmng",  // nom
                        false,    // pas classification
                        true,     // liens externes
                        true      // liens internes
                       );
}


/**
 * Extrait les informations et les stocke dans une structure de données.
 *
 * @param array &$struct : cgstructure de données à remplir avec les informations.
 * @param bool $classif : indique si la classification du taxon doit être gérée.
 * @return bool True si les informations ont été récupérées avec succès, false sinon.
 * 
 */
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
	 'Accept-Encoding: gzip, deflate, br',
	 'Sec-Fetch-User: ?1',
     'Connection: keep-alive',
     'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3',
	 'TE: trailers',
  ];

  // requête de recherche : on ne veut pas être redirigé (paramètre false)
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
          $auteur = str_replace("&nbsp;", " ", $auteur);
          // présence d'une obèle
          if (strpos($auteur, "&#8224;") !== false) {
            $blob['eteint'] = true;
            $auteur = str_replace(" &#8224;", "", $auteur);
          }
          $blob['auteur'] = $auteur;
        }
        continue;
      }
      if (strpos($ligne, "Status</label>") !== false) {
        // le statut peut être à +4 ou à +5 selon les cas…
        $status = strip_tags(trim($tbl[$idx+4]));
        if (!empty($status)) {
          if ($status != "accepted") {
            $blob['synonyme'] = true;
          }
        } else {
          $status = strip_tags(trim($tbl[$idx+5]));
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

/**
 * Génère le modèle IRMNG de la section "Voir aussi"
 * @param array $struct : correspond aux données récupérées par m_irmng_infos()
 * @return string : retourne le modèle généré à partir des informations (identifiant, nom, auteur, éteint, synonyme)
 */
function m_irmng_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['irmng'])) {
    $data = $struct['liens']['irmng'];
    $cible = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    if (isset($data['auteur'])) {
      $cible .= " " . $data['auteur'];
    }
    if (isset($data['eteint']) and $data['eteint']) {
      $cible = "† " . $cible;
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

/**
 * Génère le lien vers la page IRMNG
 * @param array $struct : correspond aux données récupérées par m_irmng_infos()
 * @return string : lien HTML
 */
function m_irmng_liens($struct) {
  if (isset($struct['liens']['irmng']['id'])) {
    return "<a href='https://www.irmng.org/aphia.php?p=taxdetails&id=" . $struct['liens']['irmng']['id'] .
           "'>IRMNG</a>";
  } else {
    return false;
  }
}


