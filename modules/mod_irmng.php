<?php

/**
 * Module - Interim Register of Marine and Nonmarine Genera (IRMNG)
 *
 * Ce module permet de récupérer des informations sur un taxon à partir du site IRMNG.
 * Il génère :
 * - une classification ('WoRMS')
 * - des liens externes (bioref)
 * - des liens internes
 *
 */

function irmng_bioref($rang=null) {
  return "IRMNG";
}
// retourne le nom de la classification pour Taxobox début
function irmng_classif() {
  return "IRMNG";
}
// déclaration du module
function m_irmng_init() {
  return declare_module($nom="irmng", $classif=true, $ext=true, $domaines=true);
}

// conversion rang irmng / WP
$irmng_rangs = [
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
  'Parvorder' => 'parv-ordre',
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
function irmng_rang($rang) {
  global $irmng_rangs;
  if (isset($irmng_rangs[$rang])) {
    return $irmng_rangs[$rang];
  } else {
    return "NOTFOUND-$rang";
  }
}

// liste des "règnes" et traduction selon WP
$irmng_regnes = [
  'Animalia' => 'animal',
  'Archaea' => 'archaea',
  'Bacteria' => 'bactérie',
  'Fungi' => 'champignon',
  'Plantae' => 'végétal',
  'Viruses' => 'virus',
  'Incertae sedis' => 'neutre',
  'Protozoa' => 'protiste',
  'Chromista' => 'protiste', // ancien. algue
];
function irmng_charte($nom) {
  global $irmng_regnes;
  if (!isset($irmng_regnes[$nom])) {
    return 'neutre';
  }
  return $irmng_regnes[$nom];
}

// nettoyage des entrées ayant un † dans le nom
function irmng_nettoie_dagger($txt) {
  $daggerfound = false;

  $tmp = str_replace("&nbsp;&#8224;", "", $txt);
  $tmp = str_replace("&nbsp;", "", $tmp);
  $tmp = str_replace("&#8224;", "", $tmp);

  if ($tmp != $txt) {
    $daggerfound = true;
  }

  return array('txt' => $tmp, 'eteint' => $daggerfound);
}

/**
 * Extrait les informations et les stocke dans une structure de données.
 *
 * @param array &$struct : cgstructure de données à remplir avec les informations.
 * @param bool $classif : indique si la classification du taxon doit être gérée.
 * @return bool True si les informations ont été récupérées avec succès, false sinon.
 * 
 */

// extraction des infos de la page irmng
function irmng_extraire($page, $id) {
  $out = [];
  $out['id'] = $id;
  $tbl = explode("\n", $page);

  foreach($tbl as $idx => $ligne) {
    $ligne = trim($ligne);
    // nom complet (parfois nécessaire)
    if (strpos($ligne, '<b><i role="button" tabindex="0"') !== false) {
      $tmp = irmng_nettoie_dagger(trim(strip_tags($ligne)));
      $out['nom-complet'] = $tmp['txt'];
      $out['eteint'] = $tmp['eteint'];
    }
    // nom accepté (seulement présent si synonyme)
    if (strpos($ligne, '>Accepted Name<') !== false) {
      $tmp = explode('"', $tbl[$idx+5]);
      if (isset($tmp[1])) {
        $x = explode("=", $tmp[1]);
        if (isset($x[2])) {
          $tmp = irmng_nettoie_dagger($x[2]);
          $out['cible'] = $tmp['txt'];
          $out['eteint'] = $tmp['eteint'];
        }
      }
      $out['rang'] = irmng_rang(trim($tbl[$idx+5]));
      continue;
    }
    // auteur
    if (strpos($ligne, 'div id="Authority') !== false) {
      $out['auteur'] = trim($tbl[$idx+4]);
    }
    // rang
    if (strpos($ligne, '<div id="Rank"') !== false) {
      $out['rang'] = irmng_rang(trim($tbl[$idx+4]));
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
            $tmp = irmng_nettoie_dagger($y[4]);
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
        $tmp = irmng_nettoie_dagger($ns);
        $blob['nom'] = $tmp['txt'];
        $blob['rang'] = irmng_rang($p2);
        $blob['eteint'] = $tmp['eteint'];
        if (($blob['rang'] == 'royaume') or ($blob['rang'] == 'règne')) {
          // fixe le règne
          $out['charte'] = irmng_charte($ns);
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
        $x = str_replace("&lt;i&gt;", "''", $x);
        $x = str_replace("&lt;/i&gt;", "''", $x);

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
    if (strpos($ligne, 'id="ChildTaxa"') !== false) {
      $i = $idx + 1;
      $out['sous-taxons'] = [];

      while (isset($tbl[$i]) && strpos($tbl[$i], '</ol>') === false) {
        $ligne_sous_taxon = trim($tbl[$i]);
    
        if (strpos($ligne_sous_taxon, '<i>') !== false) {
            $blob = [];
            $t = explode('"', $ligne_sous_taxon);
    
            if (!isset($t[3])) {
                logs("irmng: sous-taxon non identifié. Ignoré");
                $i++;
                continue;
            }
    
            $t2 = explode("=", $t[3]);
    
            if (!isset($t2[2])) {
                logs("irmng: sous-taxon non identifié (2). Ignoré");
                $i++;
                continue;
            }
    
            // "Skip" : synonyme (accepted as), uncertain, ...
            if (strpos($ligne_sous_taxon, " accepted as ") !== false || strpos($ligne_sous_taxon, "nomen dubium") !== false ||
                strpos($ligne_sous_taxon, "uncertain") !== false) {
                $i++;
                continue;
            }
    
            $blob['id'] = $t2[2];
            $p1 = preg_replace(',<a .*$,', '', $ligne_sous_taxon);
            $x = trim(strip_tags(trim($p1)));
            $blob['rang'] = irmng_rang($x);
            $p2 = preg_replace(',^.*<a ,', '<a ', $ligne_sous_taxon);
            $x = trim(strip_tags(trim($p2)));
            $tmp = irmng_nettoie_dagger($x);
            $blob['nom'] = $tmp['txt'];
            $blob['eteint'] = $tmp['eteint'];
            $out['sous-taxons'][] = $blob;
        }
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
function irmng_etal($nom) {
  return str_replace("et al.", "{{et al.}}", $nom);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_irmng_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];
  $tmp = explode(" ", $taxon);
  $nb_mots = count($tmp);

  // on récupère la page de recherche (cookie)
  $url = "https://www.irmng.org/aphia.php?p=search";
  $ret = get_data($url);
  // on cherche le taxon
  $url = "https://www.irmng.org/aphia.php?p=taxlist";
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
  $header = [ 'Referer: https://www.irmng.org/aphia.php?p=search',
              'Sec-Fetch-Dest: document',
              'Sec-Fetch-Mode: navigate',
              'Sec-Fetch-Site: same-origin',
              'Sec-Fetch-User: ?1',
              'TE: trailers',
              'Content-Type: application/x-www-form-urlencoded',
              'Origin: https://www.irmng.org',
              'Cookie: vliz_webc=vliz_webc1; limit_marine=0; limit_extant=0',
              'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
              'Accept-Encoding: gzip, deflate, br',
              'Upgrade-Insecure-Requests: 1'];
  $ret = post_data_header($url, $post, $header, false);
  if ($ret === false) {
    logs("irmng: échec de la recherche");
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
        // si le rang est supérieur au genre le nom contient aussi l'auteur
        if ($nb_mots == 1) {
          // on tente de soustraire la fin
          $fin = str_replace("$taxon ", "", $nom);
          $nnom = str_replace(" $fin", "", $nom);
          if (!empty(trim($nnom))) {
            $nom = $nnom;
          }
        }
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
      logs("irmng: taxon trouvé mais non accepté");
    } else {
      logs("irmng: taxon non trouvé");
    }
    return false;
  }

  // on note l'identifiant
  $blob = [];
  $blob['id'] = $trouve;
  // on récupère la page pour avoir les autres infos
  $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $trouve;
  // cookies pour accepter tous les types de taxon…
  add_cookies('www.irmng.org	FALSE	/	FALSE	0	limit_marine	0');
  add_cookies('www.irmng.org	FALSE	/	FALSE	0	limit_extant	0');
  $ret = get_data($url);
  if ($ret === false) {
    logs("irmng: échec de récupération de la page d'informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = isset($struct['taxon']['auteur']) ? irmng_etal($struct['taxon']['auteur']) : '';
    $struct['liens']['irmng'] = $blob;
    return false;
  }

  // extraction des infos
  $res = irmng_extraire($ret, $blob['id']);
  if ($res === false) {
    logs("irmng: échec d'extraction des informations");
    // erreur, mais on met quand même l'identifiant
    $blob['nom'] = $taxon;
    $blob['rang'] = $struct['taxon']['rang'];
    $blob['auteur'] = irmng_etal($struct['taxon']['auteur']);
    $struct['liens']['irmng'] = $blob;
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
    $tmp['rang'] = $res['rang'];
  } else {
    if (isset($struct['taxon']['rang'])) {
      $tmp['rang'] = $struct['taxon']['rang'];
    }
  }
  if (isset($res['auteur'])) {
    $tmp['auteur'] = irmng_etal($res['auteur']);
  } else {
    if (isset($struct['taxon']['auteur'])) {
      $tmp['auteur'] = irmng_etal($struct['taxon']['auteur']);
    }
  }
  if (isset($res['cible'])) {
    $tmp['synonyme'] = true;
  }
  $tmp['id'] = $res['id'];
  $struct['liens']['irmng'] = $tmp;

  // pas classification : terminé
  if (!$classif) {
    return true;
  }

  // il faut la charte/règne
  if (!isset($res['charte'])) {
    logs("irmng: charte/règne non trouvé");
    return false;
  }
  $struct['regne'] = $res['charte'];

  // partie taxon
  $struct['taxon']['nom'] = $res['nom'];
  $struct['taxon']['auteur'] = isset($res['auteur']) ? irmng_etal($res['auteur']) : '';
  $struct['taxon']['rang'] = $res['rang'];
  if (isset($res['eteint'])) {
    $struct['taxon']['eteint'] = $res['eteint'];
  }

  // classification : si synonyme on fait le suivi
  if (isset($res['cible']) and get_config("suivre-synonymes")) {
    // on récupère le nom du synonyme
    $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $res['cible'];
    $ret = get_data($url);
    if ($ret === false) {
      logs("irmng: échec de récupération du synonyme");
      return false;
    }
    // extraction des infos
    $res = irmng_extraire($ret, $res['cible']);
    if ($res === false) {
      logs("irmng: échec d'analyse du synonyme");
      return false;
    }
    // on note l'ancien nom
    $struct['redirection']['nom'] = $struct['taxon']['nom'];
    // on fixe le nouveau nom
    $struct['taxon']['nom'] = $res['nom'];
    // on se ré-appelle sur la cible
    return(m_irmng_infos($struct, $classif));
  }

  // infos générales
  $struct['classification'] = 'irmng';
  $struct['classification-taxobox'] = irmng_classif();

  if (isset($res['classification'])) {
    $struct['rangs'] = array_reverse($res['classification']);
  } else {
    logs("irmng: pas de classification trouvée");
    return false;
  }
  // vernaculaires
  if (isset($res['vernaculaire'])) {
    $struct['vernaculaire'][irmng_bioref()] = $res['vernaculaire'];
  }

  // publication originale
  if (isset($res['description'])) {
    $struct['originale'] = $res['description'];
  }

  // basionyme
  if (isset($res['basionyme'])) {
    $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $res['basionyme'];
    $ret = get_data($url);
    if ($ret === false) {
      logs("irmng: échec de récupération d'information sur le basionyme. Ignoré");
    } else {
      $tmp = irmng_extraire($ret, $res['basionyme']);
      if ($tmp === false) {
        logs("irmng: échec de récupération d'information sur le basionyme (2). Ignoré");
      } else {
        $struct['basionyme']['nom'] = $tmp['nom'];
        $struct['basionyme']['auteur'] = irmng_etal($tmp['auteur']);
        $struct['basionyme']['source'] = irmng_bioref();
      }
    }
  }

  // synonymes
  if (isset($res['synonymes'])) {
    $lst = [];
    foreach($res['synonymes'] as $syn) {
      $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $syn;
      $ret = get_data($url);
      if ($ret === false) {
        logs("irmng: échec de récupération d'information sur un synonyme. Ignoré");
      } else {
        $tmp = irmng_extraire($ret, $syn);
        if ($tmp === false) {
          logs("irmng: échec de récupération d'information sur un synonyme (2). Ignoré");
        } else {
          $x = [];
          $x['nom'] = $tmp['nom'];
          $x['auteur'] = irmng_etal($tmp['auteur']);
          $x['rang'] = $tmp['rang'];
          $lst[] = $x;
        }
      }
    }
    $struct['synonymes']['liste'] = $lst;
    $struct['synonymes']['source'] = irmng_bioref();
  }

  // sous-taxons
  if (isset($res['sous-taxons'])) {
    $lst = [];
    foreach($res['sous-taxons'] as $stt) {
      $st = $stt['id'];
      $url = "https://www.irmng.org/aphia.php?p=taxdetails&id=" . $st;
      $ret = get_data($url);
      if ($ret === false) {
        logs("irmng: échec de récupération d'information sur un sous-taxon. Ignoré");
      } else {
        $tmp = irmng_extraire($ret, $st);
        if ($tmp === false) {
          logs("irmng: échec de récupération d'information sur un sous-taxon (2). Ignoré");
        } else {
          $x = [];
          $x['nom'] = $tmp['nom'];
          $x['auteur'] = isset($tmp['auteur']) ? irmng_etal($tmp['auteur']) : '';
          $x['rang'] = $tmp['rang'];
          if (isset($tmp['eteint'])) {
            $x['eteint'] = $tmp['eteint'];
          }
          $lst[] = $x;
        }
      }
    }
    $struct['sous-taxons']['liste'] = $lst;
    $struct['sous-taxons']['source'] = irmng_bioref();
  }
  return true;
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