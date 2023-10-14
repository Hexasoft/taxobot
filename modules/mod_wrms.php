<?php

/*
  Module pour wrms (non classification)
*/

// retourne le nom du modèle Bioref associé à GBIF (théoriquement peut dépendre du rang)
function wrms_bioref($rang=null) {
  return "WRMS";
}
// retourne le nom de la classification pour Taxobox début
function wrms_classif() {
  return "WoRMS";
}
// déclaration du module
function m_wrms_init() {
  return declare_module("wrms", true, true, true, 996);
}

// conversion rang WRMS / WP
$wrms_rangs = [
  'Clade' => 'clade',
  'Type' => 'type',
  'Group' => 'groupe',
  'Unspecified' => 'non-classé',
  'Subform' => 'sous-forme',
  'Form' => 'forme',
  'Variety' => 'variété',
  'Pathovar' => 'pathovar',
  'Cultivar' => 'cultivar',
  'Subspecies' => 'sous-espèce',
  'Hybrid' => 'hybride',
  'Species' => 'espèce',
  'Subserie' => 'sous-série',
  'Serie' => 'série',
  'Subsection' => 'sous-section',
  'Section' => 'section',
  'Subgenus' => 'sous-genre',
  'Genus' => 'genre',
  'Subtribe' => 'sous-tribu',
  'Tribe' => 'tribu',
  'Supertribe' => 'super-tribu',
  'Infratribe' => 'infra-tribu',
  'Subfamily' => 'sous-famille',
  'Family' => 'famille',
  'null2' => 'épifamille',
  'Superfamily' => 'super-famille',
  'Microorder' => 'micro-ordre',
  'Infraorder' => 'infra-ordre',
  'Suborder' => 'sous-ordre',
  'Order' => 'ordre',
  'Superorder' => 'super-ordre',
  'Subcohort' => 'sous-cohorte',
  'Cohort' => 'cohorte',
  'Supercohort' => 'super-cohorte',
  'Subterclass' => 'subter-classe',
  'Infraclass' => 'infra-classe',
  'Subclass' => 'sous-classe',
  'Class' => 'classe',
  'Superclass' => 'super-classe',
  'Megaclass' => 'super-classe',
  'Gigaclass' => 'giga-classe',
  'Parvphylum' => 'parv-embranchement',
  'Microphylum' => 'micro-embranchement',
  'Infraphylum' => 'infra-embranchement',
  'Subphylum' => 'sous-embranchement',
  'Phylum' => 'embranchement',
  'Superphylum' => 'super-embranchement',
  'Infradivision' => 'infra-division',
  'Subdivision' => 'sous-division',
  'Division' => 'division',
  'Subphylum Subdivision' => 'sous-division',
  'Phylum Division' => 'division',
  'Superdivision' => 'super-division',
  'Infrakingdom' => 'infra-règne',
  'null' => 'rameau',
  'Subkingdom' => 'sous-règne',
  'Kingdom' => 'règne',
  'Superkingdom' => 'super-règne',
  'Subdomain' => 'sous-domaine',
  'Domain' => 'domaine',
  'Superdomain' => 'super-domaine',
  'Empire' => 'empire',
];
function wrms_rang($rang) {
  global $wrms_rangs;
  if (isset($wrms_rangs[$rang])) {
    return $wrms_rangs[$rang];
  } else {
    return "NOTFOUND-$rang";
  }
}

// liste des "règnes" et traduction selon WP
$wrms_regnes = [
  'Animalia' => 'animal',
  'Archaea' => 'archaea',
  'Bacteria' => 'bactérie',
  'Fungi' => 'champignon',
  'Plantae' => 'végétal',
  'Viruses' => 'virus',
  'Incertae sedis' => 'neutre',
  'Protozoa' => 'protiste',
  'Chromista' => 'algue',
];
function wrms_charte($nom) {
  global $wrms_regnes;
  if (!isset($wrms_regnes[$nom])) {
    return 'neutre';
  }
  return $wrms_regnes[$nom];
}

// nettoyage des entrées ayant un † dans le nom
function wrms_nettoie_dagger($txt) {
  $daggerfound = false;
  
  $tmp = str_replace("&nbsp;&#8224;", "", $txt);
  $tmp = str_replace("&nbsp;", "", $tmp);
  $tmp = str_replace("&#8224;", "", $tmp);
 
  if ($tmp != $txt) {
    $daggerfound = true;
  }
  
  return array('txt' => $tmp, 'eteint' => $daggerfound);
}

// extraction des infos de la page WRMS
function wrms_extraire($page, $id) {
  $out = [];
  $out['id'] = $id;
  $tbl = explode("\n", $page);
  foreach($tbl as $idx => $ligne) {
    $ligne = trim($ligne);
    // nom complet (parfois nécessaire)
    if (strpos($ligne, '<b><i role="button" tabindex="0"') !== false) {
      $tmp = wrms_nettoie_dagger(trim(strip_tags($ligne)));
      $out['nom-complet'] = $tmp['txt'];
      $out['eteint'] = $tmp['eteint'];
    }
    // nom accepté (seulement présent si synonyme)
    if (strpos($ligne, '>Accepted Name<') !== false) {
      $tmp = explode('"', $tbl[$idx+5]);
      if (isset($tmp[1])) {
        $x = explode("=", $tmp[1]);
        if (isset($x[2])) {
          $tmp = wrms_nettoie_dagger($x[2]);
          $out['cible'] = $tmp['txt'];
          $out['eteint'] = $tmp['eteint'];
        }
      }
      $out['rang'] = wrms_rang(trim($tbl[$idx+5]));
      continue;
    }
    // auteur
    if (strpos($ligne, 'div id="Authority') !== false) {
      $out['auteur'] = trim($tbl[$idx+4]);
    }
    // rang
    if (strpos($ligne, '<div id="Rank"') !== false) {
      $out['rang'] = wrms_rang(trim($tbl[$idx+4]));
      continue;
    }
    // statut
    if (strpos($ligne, '>Status<') !== false) {
      if (isset($tbl[$idx+5])) {
        $out['statut'] = trim(strip_tags($tbl[$idx+5]));
      }
    }
    // basionyme
    if (strpos($ligne, '>Orig. name<') !== false) {
      $x = explode('"', $tbl[$idx+5]);
      if (isset($x[1])) {
        $y = explode("=", $x[1]);
        if (isset($y[2])) {
          $out['basionyme'] = $y[2];
        }
      }
      // pourquoi j'avais désactivé ça ?!
      $out['basionyme'] = trim(strip_tags($tbl[$idx+5]));
    }
    // vernaculaires
    if (strpos($ligne, 'aphia_ct_vernacular_') !== false) {
      if (strpos($ligne, '>French<') !== false) {
        if (isset($tbl[$idx+1])) {
          $x = trim(strip_tags($tbl[$idx+1]));
          if (!empty($x)) {
            if (!isset($out['vernaculaire'])) {
              $out['vernaculaire'] = [];
            }
            $out['vernaculaire'][] = $x;
          }
        }
      }
    }
    // synonymes
    if (strpos($ligne, '>Synonymised names<') !== false) {
      $i = $idx + 4;
      unset($out['synonymes']);
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $tmp = trim($tbl[$i]);
        if (strpos($tmp, 'class="aphia_core_line_spacer') !== false) {
          break;
        }
        if (strpos($tmp, 'aphia_ct_tu_') === false) {
          $i++;
          continue;
        }
        $x = explode('"', $tmp);
        if (isset($x[5])) {
          $y = explode(":", $x[5]);
          if (isset($y[4])) {
            if (!isset($out['synonymes'])) {
              $out['synonymes'] = [];
            }
            $tmp = wrms_nettoie_dagger($y[4]);
            $out['synonymes'][] = $tmp['txt'];
          }
        }
        $i++;
      }
    }
    // classification
    if (strpos($ligne, 'for="Classification">') !== false) {
      $out['classification'] = [];
      // on parcours les éléments
      $i = $idx + 6;
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $tmp = trim($tbl[$i]);
        if ($tmp == '</ol>') {
          break;
        }
        $p1 = preg_replace(',</a>.*$,', '', $tmp);
        $ns = strip_tags($p1);
        $p2 = preg_replace(',^.*</a>,', '', $tmp);
        $p2 = strip_tags($p2);
        $p2 = str_replace(['&nbsp;', '(', ')'], '', $p2);
        $blob = [];
        $tmp = wrms_nettoie_dagger($ns);
        $blob['nom'] = $tmp['txt'];
        $blob['rang'] = wrms_rang($p2);
        $blob['eteint'] = $tmp['eteint'];
        if (($blob['rang'] == 'royaume') or ($blob['rang'] == 'règne')) {
          // fixe le règne
          $out['charte'] = wrms_charte($ns);
        }
        // si autre que 'algue' ou 'protiste' on supprime le règne/royaume
        if (($blob['rang'] != 'royaume') and ($blob['rang'] != 'règne')) {
          $out['classification'][] = $blob;
        } else {
          if (($out['charte'] == 'algue') or ($out['charte'] == 'protiste')) {
            $out['classification'][] = $blob;
          }
        }
        $der_nom = $ns;
        $der_rang = $p2;
        $i++;
      }
      // on vire le dernier (qui est égal au taxon)
      $tmp = array_pop($out['classification']);
      // on récupère le nom du taxon
      $out['nom'] = $tmp['nom'];
      $out['eteint'] = $tmp['eteint'];
    }
    
    // description originale
    if (strpos($ligne, 'id="OriginalDescription') !== false) {
      $i = $idx + 2;
      unset($out['description']);
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $tmp = trim($tbl[$i]);
        if (strpos($tmp, 'class="aphia_core_line_spacer') !== false) {
          break;
        }
        if (strpos($tmp, 'aphia_ct_source_') === false) {
          $i++;
          continue;
        }
        $x = preg_replace("/^.*correctHTML['\"]>/", "", $tmp);
        $x = preg_replace("/<\/span>.*$/", "", $x);
        $x = str_replace("&lt;em&gt;", "", $x);
        $x = str_replace("&lt;/em&gt;", "", $x);
        // on cherche un éventuel lien vers la ressource
        $tbl = explode('href=', $tmp);
        foreach($tbl as $el) {
          if (strpos($el, 'http') === false) {
            continue;
          }
          $z = substr($el, 0, 1);
          $y = explode($z, $el);
          if (isset($y[1])) {
            if (strpos($y[1], "aphia") === false) {
              $x .= " [" . $y[1] . " lire]";
            }
          }
        }
        $out['description'] = $x;
        $i++;
      }
    }
    // sous-taxons
    if (strpos($ligne, '>Direct children') !== false) {
      $i = $idx + 4;
      $out['sous-taxons'] = [];
      while(true) {
        if (!isset($tbl[$i])) {
          break;
        }
        $blob = [];
        $tmp = trim($tbl[$i]);
        if (strpos($tmp, 'class="aphia_core_line_spacer') !== false) {
          break;
        }
        if (strpos($tmp, 'class="aphia_core_pb-3') === false) {
          $i++;
          continue;
        }
        $t = explode('"', $tmp);
        if (!isset($t[3])) {
          logs("WRMS: sous-taxon non identifié. Ignoré");
          $i++;
          continue;
        }
        $t2 = explode("=", $t[3]);
        if (!isset($t2[2])) {
          logs("WRMS: sous-taxon non identifié (2). Ignoré");
          $i++;
          continue;
        }
        // cas d'un taxon synonymisé
        if (strpos($tmp, " accepted as ") !== false) {
          $i++;
          continue;
        }
        // cas d'un taxon non pleinement valide
        if ((strpos($tmp, "nomen dubium") !== false) or (strpos($tmp, "nomen nudum") !== false) or
            (strpos($tmp, "uncertain") !== false) or (strpos($tmp, "unaccepted") !== false)) {
          $i++;
          continue;
        }
        $blob['id'] = $t2[2];
        $p1 = preg_replace(',<a .*$,', '', $tmp);
        $x = trim(strip_tags(trim($p1)));
        $blob['rang'] = wrms_rang($x);
        $p2 = preg_replace(',^.*<a ,', '<a ', $tmp);
        $x = trim(strip_tags(trim($p2)));
        $tmp = wrms_nettoie_dagger($x);
        $blob['nom'] = $tmp['txt'];
        $blob['eteint'] = $tmp['eteint'];
        $out['sous-taxons'][] = $blob;
        $i++;
      }
    }
  }
  if (!isset($out['auteur'])) {
    if (isset($out['nom-complet'])) {
      $tmp = str_replace($out['nom'], "", $out['nom-complet']);
      $out['auteur'] = trim($tmp);
    }
  }
  // si le basionyme est identique on l'enlève
  if (isset($out['basionyme']) and ($out['basionyme'] == $id)) {
    unset($out['basionyme']);
  }

  return $out;
}

// remplace "et al." par {{et al.}}
function wrms_etal($nom) {
  return str_replace("et al.", "{{et al.}}", $nom);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_wrms_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  
  // on récupère la page de recherche (cookie)
  $url = "https://www.marinespecies.org/aphia.php?p=search";
  $ret = get_data($url);
  // on cherche le taxon
  $url = "https://www.marinespecies.org/aphia.php?p=taxlist";
  $post = "searchpar=0" . // Search by scientific name
          "&tComp=is" . // Full name
          "&tName=" . str_replace(" ", "+", $taxon) . // Taxon name
          "&action=search&rSkips=0&adv=1" .
          "&vOnly=0" . // only accepted taxa : 1, all : 0
          // Environmental parameters on "any"
          "&marine=" .
          "&fresh=" . 
          "&terrestrial=" .
          "&brackish=" .
          "&fossil=0" . // empty = any ; 3 = recent + fossil ; 4 = extant, not fossil-only. Pas mieux en any ?
          // cf. Demande 143. Problème de regex ? On a une sortie "charte/règne non trouvé", même en "any".
          // others flags : any
          "&unacceptreason=&image=&basionym=&nType=";
  $header = [ 'Referer: https://www.marinespecies.org/aphia.php?p=search',
              'Sec-Fetch-Dest: document',
              'Sec-Fetch-Mode: navigate',
              'Sec-Fetch-Site: same-origin',
              'Sec-Fetch-User: ?1',
              'TE: trailers',
              'Content-Type: application/x-www-form-urlencoded',
              'Origin: https://www.marinespecies.org',
              'Cookie: vliz_webc=vliz_webc1; limit_marine=0; limit_extant=0',
              'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
              'Accept-Encoding: gzip, deflate, br',
              'Upgrade-Insecure-Requests: 1'];
  $ret = post_data_header($url, $post, $header, false);
  if ($ret === false) {
    logs("WRMS: échec de la recherche");
    return false;
  }

  $tbl = explode("\n", $ret);
  $trouve = false;
  $url = false;
  // on cherche une redirection unique
  foreach($tbl as $l) {
    if (strpos($l, "aphia.php?p=taxdetails&id=") !== false) {
      $trouve = trim(preg_replace('/^.*id=/', '', $l));
      $url = trim(preg_replace('/location: /', '', $l));
      if ($url != trim($l)) {
        // résultat unique, avec redirection
        break;
      } else {
        $trouve = false;
        $url = false;
      }
    }
  }
  
  if ($trouve === false) {
    // on cherche une réponse sans redirection ou autre unaccepted
    foreach($tbl as $l) {
      if (strpos($l, "aphia.php?p=taxdetails&id=") !== false) {
        $trouve = trim(preg_replace('/^.*id=/', '', $l));
        $trouve = preg_replace('/".*$/', '', $trouve);
        if (!is_numeric($trouve)) {
          $trouve = false;
          continue;
        }
        $nom = preg_replace('/^.*><a href[=]"aphia.php[?][p]=taxdetails[&]id=/', '', $l);
        $nom = preg_replace('/^[0-9]*"[>]/', '', $nom);
        $nom = preg_replace(",^[<]i[^>]*[>][<]/i[>],", '', $nom);
        $nom = preg_replace(",^[<]i[>],", '', $nom);
        $nom = preg_replace("/[<].*$/", '', $nom);
        if ($nom != $taxon) {
          $trouve = false;
          continue;
        }
        // c'est le bon nom, est-ce qu'il y a une indication ?
        $suite = preg_replace('/^.*><a href[=]"aphia.php[?][p]=taxdetails[&]id=[^>]*>/', '', $l);
        $suite = preg_replace(",^[<]i[^>]*[>][<]/i[>],", '', $suite);
        if (strpos($suite, "uncertain") !== false) {
          $trouve = false; // incertain, on le laisse à ce niveau
          continue;
        }
        if (strpos($suite, "unassessed") !== false) {
          $trouve = false; // idem
          continue;
        }
        if (strpos($suite, "accepted as") !== false) {
          $trouve = false; // synonyme, on le laisse à ce niveau
          continue;
        }
        // trouvé
        break;
      }
    }
  }

  $naccept = false;
  if ($trouve === false) {
    // on cherche une réponse avec synonyme
    foreach($tbl as $l) {
      if (strpos($l, "aphia.php?p=taxdetails&id=") !== false) {
        $trouve = trim(preg_replace('/^.*id=/', '', $l));
        $trouve = preg_replace('/".*$/', '', $trouve);
        if (!is_numeric($trouve)) {
          $trouve = false;
          continue;
        }
        $nom = preg_replace('/^.*><a href[=]"aphia.php[?][p]=taxdetails[&]id=/', '', $l);
        $nom = preg_replace('/^[0-9]*"[>]/', '', $nom);
        $nom = preg_replace(",^[<]i[^>]*[>][<]/i[>],", '', $nom);
        $nom = preg_replace(",^[<]i[>],", '', $nom);
        $nom = preg_replace("/[<].*$/", '', $nom);
        if ($nom != $taxon) {
          $trouve = false;
          continue;
        }
        // c'est le bon nom, est-ce qu'il y a une indication ?
        $suite = preg_replace('/^.*><a href[=]"aphia.php[?][p]=taxdetails[&]id=[^>]*>/', '', $l);
        $suite = preg_replace(",^[<]i[^>]*[>][<]/i[>],", '', $suite);
        if (strpos($suite, "uncertain") !== false) {
          $trouve = false; // incertain, on le laisse à ce niveau
          $naccept = true;
          continue;
        }
        if (strpos($suite, "unassessed") !== false) {
          $naccept = true;
          $trouve = false; // idem
          continue;
        }
        if (strpos($suite, "accepted as") !== false) {
          // trouvé
          break;
        }
        // trouvé
        break;
      }
    }
  }

  // non trouvé
  if ($trouve === false) {
    if ($naccept) {
      logs("WRMS: taxon trouvé mais non accepté");
    } else {
      logs("WRMS: taxon non trouvé");
    }
    return false;
  }

  // on note l'identifiant
  $blob = [];
  $blob['id'] = $trouve;
  // on récupère la page pour avoir les autres infos
  $url = "https://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $trouve;
  // cookies pour accepter tous les types de taxon…
  add_cookies('www.marinespecies.org	FALSE	/	FALSE	0	limit_marine	0');
  add_cookies('www.marinespecies.org	FALSE	/	FALSE	0	limit_extant	0');
  $ret = get_data($url);
  if ($ret === false) {
    logs("WRMS: échec de récupération de la page d'informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = wrms_etal($struct['taxon']['auteur']);
    $struct['liens']['wrms'] = $blob;
    return false;
  }

  // extraction des infos
  $res = wrms_extraire($ret, $blob['id']);
  if ($res === false) {
    logs("WRMS: échec d'extraction des informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = wrms_etal($struct['taxon']['auteur']);
    $struct['liens']['wrms'] = $blob;
    return false;
  }

  $tmp = [];
  if (isset($res['nom'])) {
    $tmp['nom'] = $res['nom'];
  } else {
    $tmp['nom'] = $taxon;
  }
  if (isset($res['eteint'])) {
    $tmp['eteint'] = $res['eteint'];
  }
  if (isset($res['rang'])) {
    $tmp['rang'] = $res['nom'];
  } else {
    if (isset($struct['taxon']['rang'])) {
      $tmp['rang'] = $struct['taxon']['rang'];
    }
  }
  if (isset($res['auteur'])) {
    $tmp['auteur'] = wrms_etal($res['auteur']);
  } else {
    if (isset($struct['taxon']['auteur'])) {
      $tmp['auteur'] = wrms_etal($struct['taxon']['auteur']);
    }
  }
  if (isset($res['cible'])) {
    $tmp['synonyme'] = true;
  }
  $tmp['id'] = $res['id'];
  $struct['liens']['wrms'] = $tmp;

  // pas classification : terminé
  if (!$classif) {
    return true;
  }

  // il faut la charte/règne
  if (!isset($res['charte'])) {
    logs("WRMS: charte/règne non trouvé");
    return false;
  }
  $struct['regne'] = $res['charte'];
  
  // partie taxon
  $struct['taxon']['nom'] = $res['nom'];
  $struct['taxon']['auteur'] = wrms_etal($res['auteur']);
  $struct['taxon']['rang'] = $res['rang'];
  if (isset($res['eteint'])) {
    $struct['taxon']['eteint'] = $res['eteint'];
  }
  
  // classification : si synonyme on fait le suivi
  if (isset($res['cible']) and get_config("suivre-synonymes")) {
    // on récupère le nom du synonyme
    $url = "https://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $res['cible'];
    $ret = get_data($url);
    if ($ret === false) {
      logs("WRMS: échec de récupération du synonyme");
      return false;
    }
    // extraction des infos
    $res = wrms_extraire($ret, $res['cible']);
    if ($res === false) {
      logs("WRMS: échec d'analyse du synonyme");
      return false;
    }
    // on note l'ancien nom
    $struct['redirection']['nom'] = $struct['taxon']['nom'];
    // on fixe le nouveau nom
    $struct['taxon']['nom'] = $res['nom'];
    // on se ré-appelle sur la cible
    return(m_wrms_infos($struct, $classif));
  }
  
  // infos générales
  $struct['classification'] = 'WRMS';
  $struct['classification-taxobox'] = wrms_classif();
  
  if (isset($res['classification'])) {
    $struct['rangs'] = array_reverse($res['classification']);
  } else {
    logs("WRMS: pas de classification trouvée");
    return false;
  }
  // vernaculaires
  if (isset($res['vernaculaire'])) {
    $struct['vernaculaire'][wrms_bioref()] = $res['vernaculaire'];
  }
  
  // publication originale
  if (isset($res['description'])) {
    $struct['originale'] = $res['description'];
  }

  // basionyme
  if (isset($res['basionyme'])) {
    $url = "https://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $res['basionyme'];
    $ret = get_data($url);
    if ($ret === false) {
      logs("WRMS: échec de récupération d'information sur le basionyme. Ignoré");
    } else {
      $tmp = wrms_extraire($ret, $res['basionyme']);
      if ($tmp === false) {
        logs("WRMS: échec de récupération d'information sur le basionyme (2). Ignoré");
      } else {
        $struct['basionyme']['nom'] = $tmp['nom'];
        $struct['basionyme']['auteur'] = wrms_etal($tmp['auteur']);
        $struct['basionyme']['source'] = wrms_bioref();
      }
    }
  }
  
  // synonymes
  if (isset($res['synonymes'])) {
    $lst = [];
    foreach($res['synonymes'] as $syn) {
      $url = "https://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $syn;
      $ret = get_data($url);
      if ($ret === false) {
        logs("WRMS: échec de récupération d'information sur un synonyme. Ignoré");
      } else {
        $tmp = wrms_extraire($ret, $syn);
        if ($tmp === false) {
          logs("WRMS: échec de récupération d'information sur un synonyme (2). Ignoré");
        } else {
          $x = [];
          $x['nom'] = $tmp['nom'];
          $x['auteur'] = wrms_etal($tmp['auteur']);
          $x['rang'] = $tmp['rang'];
          $lst[] = $x;
        }
      }
    }
    $struct['synonymes']['liste'] = $lst;
    $struct['synonymes']['source'] = wrms_bioref();
  }
  
  // sous-taxons
  if (isset($res['sous-taxons'])) {
    $lst = [];
    foreach($res['sous-taxons'] as $stt) {
      $st = $stt['id'];
      $url = "https://www.marinespecies.org/aphia.php?p=taxdetails&id=" . $st;
      $ret = get_data($url);
      if ($ret === false) {
        logs("WRMS: échec de récupération d'information sur un sous-taxon. Ignoré");
      } else {
        $tmp = wrms_extraire($ret, $st);
        if ($tmp === false) {
          logs("WRMS: échec de récupération d'information sur un sous-taxon (2). Ignoré");
        } else {
          $x = [];
          $x['nom'] = $tmp['nom'];
          $x['auteur'] = wrms_etal($tmp['auteur']);
          $x['rang'] = $tmp['rang'];
          if (isset($tmp['eteint'])) {
            $x['eteint'] = $tmp['eteint'];
          }
          $lst[] = $x;
        }
      }
    }
    $struct['sous-taxons']['liste'] = $lst;
    $struct['sous-taxons']['source'] = wrms_bioref();
  }
  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_wrms_ext($struct) {
  if (isset($struct['liens']['wrms']['id'])) {
    $data = $struct['liens']['wrms'];
    $cdate = dates_recupere();
    
    $nom = $data['nom'];
    if (isset($data['eteint']) and $data['eteint']) {
      $sup = "éteint=oui | ";
    } else {
      $sup = "";
    }
    $nom = wp_met_italiques($data['nom'],
        isset($data['rang'])?$data['rang']:$struct['taxon']['rang'], $struct['regne']);
    $id = $data['id'];
    if (isset($data['auteur'])) {
      $auteur = $data['auteur'];
    } else {
      $auteur = '';
    }
    return "{{WRMS | $id | $nom | $auteur | $sup" . "consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_wrms_liens($struct) {
  if (isset($struct['liens']['wrms']['id'])) {
    return "<a href='https://www.marinespecies.org/aphia.php?p=taxdetails&id=" .
           $struct['liens']['wrms']['id'] . "'>WRMS</a>";
  } else {
    return false;
  }
}

