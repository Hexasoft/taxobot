<?php

// traduction des rangs LPSN (pour WP et pour le modèle externe)
$lpsn_rangs = [
  "species" => "espèce", "genus" => "genre", "family" => "famille",
  "order" => "ordre", "class" => "classe", "phylum" => "phylum", "domain" => "domaine", "kingdom" => "kingdom" ];

// déclaration du module
function m_lpsn_init() {
  // gère la classification, les liens externes, accepte tous les domaines
  return declare_module("lpsn", true, true, true);
}

// retourne le "règne" en fonction de la classification
function m_lpsn_regne($classif) {
  foreach($classif as $el) {
    if ($el['nom'] == 'Bacteria') {
      return 'bactérie';
    }
    if ($el['nom'] == 'Archaea') {
      return 'archaea';
    }
  }
  // par défaut ? Erreur ?
  return 'bactérie';
}

// retourne le taxon supérieur à partir d'un tableau (HTML)
function m_lpsn_sup($tbl) {
  foreach($tbl as $idx => $_ligne) {
    if (isset($tbl[$idx+1])) {
      $ligne = $_ligne . $tbl[$idx+1];
    }
    if (strpos($_ligne, '<b>Parent taxon:</b>') !== false) {
      $l = preg_replace(",^.*Parent taxon:[<]/b[>][ ]*,", "", $ligne);
      $x = explode('"', $l);
      if (!isset($x[1])) {
        return false;
      }
      $y = explode("/", $x[1]);
      if (!isset($y[1]) or !isset($y[2])) {
        return false;
      }
      $tmp = [];
      $tmp['type'] = $y[1];
      $tmp['lien'] = $y[2];
      return $tmp;
    }
  }
  return false;
}

// met en forme une zone auteur (pour zone non taxobox)
function m_lpsn_enjolive($txt) {
  $txt = preg_replace("/([^{])et al[.]/", '\1{{et al.}}', $txt);
  $txt = preg_replace("/([^A-Za-z])corrig[.]/", '\1' . "''corrig.''", $txt);
  $txt = preg_replace("/^corrig[.]/", "''corrig.''", $txt);
  $txt = preg_replace("/([^A-Za-z])ex([^A-Za-z])/", '\1' . "''ex''" . '\2', $txt);
  $txt = preg_replace("/^ex([^A-Za-z])/", "''ex''" . '\2', $txt);

  return $txt;
}

// tableau global, je n'ai pas trouvé mieux. Bref.
$m_lpsn_contenu = [];

// récupère et analyse une fiche taxon
function m_lpsn_analyse($type, $lien, $full=true) {
  global $lpsn_rangs;
  global $m_lpsn_contenu;

  debugc("LPSN: analyse $type / $lien / " . $full?"true":"false");

  $blob = [];
  $blob['lpsn-rang'] = $type;
  $blob['lpsn-lien'] = $lien;

  $url = "https://lpsn.dsmz.de/$type/$lien";
  $ret = get_data($url);
  if ($ret === false) {
    logs("LPSN: échec de récupération d'une fiche ($type / $lien)");
    return false;
  }
  $tbl = explode("\n", $ret);
  foreach($tbl as $idx => $_ligne) {
    if (isset($tbl[$idx+1])) {
      $ligne = $_ligne . $tbl[$idx+1];
    }
    if (strpos($_ligne, '<b>Name:</b>') !== false) {
      $ligne = str_replace('"', '', $ligne);
      $ligne = str_replace('<I>et al.</I>', 'et al.', $ligne);
      $ligne = str_replace('<I>ex</I>', 'ex', $ligne);
      $ligne = str_replace(' and ', ' & ', $ligne);
      $l = preg_replace(",^.*Name:[<][/]b[>][ ]*,", "", $ligne);
      $a = preg_replace(',[<]I[>],', '@', $l, 1);
      $b = preg_replace('~[<][/]I[>](?!.*[<][/]I[>])~', '@', $a);
      $b = str_replace("<I>", "", $b);
      $b = str_replace("</I>", "", $b);
      $b = str_replace("</p>", "", $b);
      $b = str_replace("</P>", "", $b);
      $x = explode("@", $b);
      if (isset($x[1]) and isset($x[2])) {
        $blob['nom'] = trim($x[1]);
        $y = preg_replace(",[ ]*[(]Approv[^)]*[)][ ]*,", " ", $x[2]);
        $blob['auteur'] = html_entity_decode(trim($y),
                             ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8");
      }
      continue;
    }
    if ((strpos($_ligne, '<b>Type species:</b>') !== false) or
        (strpos($_ligne, '<b>Type genus:</b>') !== false) or
        (strpos($_ligne, '<b>Type family:</b>') !== false) or
        (strpos($_ligne, '<b>Type class:</b>') !== false) or
        (strpos($_ligne, '<b>Type phylum:</b>') !== false)) {
      $l = preg_replace('/^.*[<]a href="/', '', $ligne);
      $l = preg_replace('/".*$/', '', $l);
      $x = explode("/", $l);
      if (isset($x[1]) and isset($x[2])) {
        $r = $x[1];
        $t = $x[2];
        $tmp = m_lpsn_analyse($r, $t, false);
        if ($tmp !== false) {
          $blob['type'] = $tmp;
        }
      }
      continue;
    }
    if (strpos($_ligne, '<b>Category:</b>') !== false) {
      $l = preg_replace(",^.*Category:[<][/]b[>][ ]*,", "", $ligne);
      $x = explode("<", $l);
      if (isset($x[0])) {
        if (isset($lpsn_rangs[$x[0]])) {
          $blob['rang'] = $lpsn_rangs[$x[0]];
        } else if (isset($lpsn_rangs[mb_strtolower($x[0])])) {
          $blob['rang'] = $lpsn_rangs[mb_strtolower($x[0])];
        } else {
          $blob['rang'] = 'non-classé';
        }
      }
      continue;
    }
    // étymologie
    if (strpos($_ligne, '<b>Etymology:</b>') !== false) {
      $l = preg_replace(",^.*Etymology:[<][/]b[>][ ]*,", "", $ligne);
      $tmp = [];
      $tmp['source'] = 'LPSN';
      $tmp['texte'] = "À TRADUIRE : « ''" . html_entity_decode(trim(strip_tags($l)),
                             ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8")
                             . "'' »";
      $blob['etymologie'] = $tmp;
      continue;
    }
    // différence entre 'effective publication' et 'original publication' ?
    if (strpos($_ligne, '<b>Original publication:</b>') !== false) {
      $l = preg_replace(",^.*Original publication:[<][/]b[>][ ]*,", "", $ligne);
      $blob['publication'] = html_entity_decode(trim(strip_tags($l)),
                             ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8");
      continue;
    }
    if (strpos($_ligne, '<b>Effective publication:</b>') !== false) {
      $l = preg_replace(",^.*Effective publication:[<][/]b[>][ ]*,", "", $ligne);
      $blob['publication'] = html_entity_decode(trim(strip_tags($l)),
                             ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8");
      continue;
    }
    if (strpos($_ligne, '<b>Valid publication:</b>') !== false) {
      $l = preg_replace(",^.*Valid publication:[<][/]b[>][ ]*,", "", $ligne);
      $blob['publication'] = html_entity_decode(trim(strip_tags($l)),
                             ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8");
      continue;
    }
    if (strpos($_ligne, '<b>Nomenclatural status:</b>') !== false) {
      $l = preg_replace(",^.*Nomenclatural status:[<][/]b[>][ ]*,", "", $ligne);
      $blob['nomenclature'] = trim(strip_tags($l));
      continue;
    }
    if (strpos($_ligne, '<b>Taxonomic status:</b>') !== false) {
      $l = preg_replace(",^.*Taxonomic status:[<][/]b[>][ ]*,", "", $ligne);
      $blob['taxonomie'] = trim(strip_tags($l));
      continue;
    }
    if (strpos($_ligne, '<b>Correct name:</b>') !== false) {
      $l = preg_replace(",^.*Correct name:[<][/]b[>][ ]*,", "", $ligne);
      $x = explode('"', $l);
      if (!isset($x[1])) {
        continue;
      }
      $y = explode("/", $x[1]);
      if (!isset($y[1]) or !isset($y[2])) {
        continue;
      }
      $blob['cible-type'] = $y[1];
      $blob['cible-lien'] = $y[2];
      continue;
    }
  }

  // en mode "court" on ne traite que ça
  if (!$full) {
    // on traite la mise en forme de la zone auteur (ici ce n'est pas traité
    // lors de l'affichage, contrairement au mode 'full')
    if (isset($blob['auteur'])) {
      $blob['auteur'] = m_lpsn_enjolive($blob['auteur']);
    }
    $m_lpsn_contenu = $tbl; // pour les appels suivants (recherche des taxons sup)
    return $blob;
  }

  // infos de taxons inférieurs
  $debut = false;
  foreach($tbl as $idx => $_ligne) {
    if (isset($tbl[$idx+1])) {
      $ligne = $_ligne . $tbl[$idx+1];
    }
    if (strpos($_ligne, '<b>Child taxa:</b>') !== false) {
      $debut = $idx + 1;
      break;
    }
  }
  // taxons inférieurs trouvés
  if ($debut) {
    $liste = [];
    while(isset($tbl[$debut])) {
      if (strpos($tbl[$debut], '</table>') !== false) {
        break; // fin de la liste
      }
      if (strpos($tbl[$debut], '<td><a href="/') !== false) {
        $l = $tbl[$debut+2];
        $l = preg_replace(",^[^>]*[>],", "", $l);
        $l = preg_replace(",[<].*$,", "", $l);
        if (trim($l) != "correct name") {
          $debut++;
          continue;
        }
        $l = $tbl[$debut+1];
        $l = preg_replace(",^[^>]*[>],", "", $l);
        $l = preg_replace(",[<].*$,", "", $l);
        if (strpos($l, "validly published") !== 0) {
          $debut++;
          continue;
        }
        // cible
        $tmp = [];
        $x = explode('"', $tbl[$debut]);
        if (!isset($x[1])) {
          continue;
        }
        $y = explode("/", $x[1]);
        if (!isset($y[1]) or !isset($y[2])) {
          continue;
        }
        $tmp['type'] = $y[1];
        $tmp['lien'] = $y[2];
        // pour avoir les infos (nom, rang, auteur)
        $tmp2 = m_lpsn_analyse($tmp['type'], $tmp['lien'], false);
        $liste[] = $tmp2;
      }
      $debut++;
    }
    if (!empty($liste)) {
      $blob['sous-taxons'] = $liste;
    }
  }

  // infos de synonymes
  $debut = false;
  foreach($tbl as $idx => $_ligne) {
    if (isset($tbl[$idx+1])) {
      $ligne = $_ligne . $tbl[$idx+1];
    }
    if (strpos($_ligne, '<b>Synonyms:</b>') !== false) {
      $debut = $idx + 1;
      break;
    }
  }
  // synonymes trouvés
  if ($debut) {
    $liste = [];
    while(isset($tbl[$debut])) {
      if (strpos($tbl[$debut], '</table>') !== false) {
        break; // fin de la liste
      }
      if (strpos($tbl[$debut], '<a href="/') !== false) {
        // cible
        $tmp = [];
        $x = explode('"', $tbl[$debut]);
        if (!isset($x[1])) {
          continue;
        }
        $y = explode("/", $x[1]);
        if (!isset($y[1]) or !isset($y[2])) {
          continue;
        }
        $el = m_lpsn_analyse($y[1], $y[2], false);
        if ($el !== false) {
          $liste[] = $el;
        }
      }
      $debut++;
    }
    if (!empty($liste)) {
      $blob['synonymes'] = $liste;
    }
  }

  // taxons supérieurs
  $liste = [];
  while(true) {
    $ret = m_lpsn_sup($tbl);
    if ($ret === false) {
      break;
    }
    $el = m_lpsn_analyse($ret['type'], $ret['lien'], false);
    if ($el === false) {
      break;
    }
    $liste[] = $el;
    $tbl = $m_lpsn_contenu; // pour le tour suivant
  }
    if (!empty($liste)) {
    $blob['classification'] = $liste;
  }
  return $blob;
}

function m_lpsn_infos(&$struct, $classif) {
  $taxon = $struct['taxon']['nom'];

  // appel à vide pour les cookies
  debugc("LPSN: requête à vide");
  $url = "https://lpsn.dsmz.de/advanced_search";
  $ret = get_data($url);
  if ($ret === false) {
    logs("LPSN: échec de connexion au site");
    return false;
  }

  // on fait la requête de recherche
  $ntaxon = str_replace(" ", "+", $taxon);
  $url = "https://lpsn.dsmz.de/advanced_search?adv[taxon-name]=$ntaxon&adv[category]=" .
         "&adv[nomenclature]=&adv[valid-publ]=yes&adv[candidatus]=no" .
         "&adv[correct-name]=yes&adv[authority]=&adv[deposit]=" .
         "&adv[nomenclatural-status]=&adv[proposed-as]=&adv[etymology]=" .
         "&adv[gender]=&adv[date-option]=&adv[date]=" .
         "&adv[date-between]=&adv[riskgroup]=&adv[submit]=submit-adv#results";
  debugc("LPSN: recherche taxon");
  $ret = get_data($url);
  if ($ret === false) {
    logs("LPSN: échec de connexion au site (2)");
    return false;
  }

  $tbl = explode("\n", $ret);
  $in = false;
  $trouve = false;
  $syn = false;
  // on cherche les réponses (éventuelles)
  foreach($tbl as $ligne) {
    if (!$in) {
      if (strpos($ligne, '<div class="body">') !== false) {
        $in = true;
      }
      continue;
    }
    if (strpos($ligne, '<a href="') !== false) {
      $px = explode('"', $ligne);
      if (isset($px[1])) {
        $py = explode("/", $px[1]);
        if (isset($py[1]) and isset($py[2])) {
          $rang = $py[1];
          $lien = $py[2];
          // on récupère les infos sur le taxon
          debugc("LPSN: vérification $rang / $lien");
          $ret = m_lpsn_analyse($rang, $lien, false);
          if ($ret === false) {
            continue;
          }
          // on vérifie que c'est le bon taxon, et qu'il est valide
          if ((!isset($ret['nom'])) or ($ret['nom'] != $taxon)) {
            continue;
          }
          // on vérifie qu'il est valide
          if (isset($ret['cible-lien'])) {
            // c'est un synonyme, on le garde
            $syn = [];
            $syn['rang'] = $ret['cible-type'];
            $syn['lien'] = $ret['cible-lien'];
            continue;
          }
          if (($ret['taxonomie'] == 'correct name') and
              (strpos($ret['nomenclature'], 'validly published') === 0)) {
            // trouvé, valide
            $trouve = true;
            debugc("LPSN: taxon trouvé");
            break;
          }
        }
      }
    }
  }

  // pas trouvé du tout
  if (!$trouve and ($syn === false)) {
    logs("LPSN: taxon non trouvé");
    return false;
  }

  // si synonyme
  if ($syn !== false) {
    // si pas classification : on retourne l'info directe
    if (!$classif) {
      $tmp = [];
      $tmp['nom'] = $ret['nom'];
      $tmp['rang'] = $ret['rang'];
      $tmp['auteur'] = $ret['auteur'];
      $tmp['rang-lpsn'] = $ret['lpsn-rang'];
      $tmp['lien'] = $ret['lpsn-lien'];
      $struct['liens']['lpsn'] = $tmp;
      return true;
    }
    // classification : si pas suivi syn erreur
    $suivre_synonymes = get_config("suivre-synonymes");
    if (!$suivre_synonymes) {
      logs("LPSN: synonyme trouvé, mais 'suivre-synonymes' désactivé");
      return false;
    }
    // on se relance sur le synonyme (cible)
    $ret = m_lpsn_analyse($syn['rang'], $syn['lien'], false);
    if ($ret === false) {
      logs("LPSN: échec d'accès aux informations de synonyme");
      return false;
    }
    $struct['taxon']['nom'] = $ret['nom'];
    return m_lpsn_info($struct, $classif);
  }
  // on récupère les infos
  debugc("LPSN: récupération des infos");
  $ret = m_lpsn_analyse($rang, $lien, true);
  // ici on a trouvé le bon : on enregistre les infos (pour lien externe)
  $tmp = [];
  $tmp['nom'] = $ret['nom'];
  $tmp['rang'] = $ret['rang'];
  $tmp['auteur'] = $ret['auteur'];
  $tmp['rang-lpsn'] = $ret['lpsn-rang'];
  $tmp['lien'] = $ret['lpsn-lien'];
  $struct['liens']['lpsn'] = $tmp;

  if (!$classif) {
    return true; // travail terminé
  }

  // classification : il nous faut les autres infos
  $struct['classification'] = 'LPSN';
  $struct['classification-taxobox'] = 'LPSN';
  $struct['taxon']['nom'] = $ret['nom'];
  $struct['taxon']['rang'] = $ret['rang'];
  $struct['taxon']['auteur'] = $ret['auteur'];

  // rangs supérieurs
  if (!isset($ret['classification']) or empty($ret['classification'])) {
    logs("LPSN: classification non trouvée");
    return false;
  }
  $struct['rangs'] = $ret['classification'];
  // on détermine le "règne"
  $struct['regne'] = m_lpsn_regne($struct['rangs']);

  // éviter le doublon règne/domaine
  if ($struct['regne'] == "bactérie") {
    foreach ($struct['rangs'] as $key => $value) {
      if ((isset($value['lpsn-lien']) && $value['lpsn-lien'] == 'bacteria') || 
          (isset($value['rang']) && $value['rang'] == 'domain')) {
          unset($struct['rangs'][$key]);
      }
    }
  $struct['rangs'] = array_values($struct['rangs']);
  }

  // taxons inférieurs
  if (isset($ret['sous-taxons']) and !empty($ret['sous-taxons'])) {
    $struct['sous-taxons']['liste'] = $ret['sous-taxons'];
    $struct['sous-taxons']['source'] = 'LPSN';
  }

  // synonymes
  if (isset($ret['synonymes']) and !empty($ret['synonymes'])) {
    $tmp = [];
    $tmp['liste'] = $ret['synonymes'];
    $tmp['source'] = 'LPSN';
    $struct['synonymes'] = $tmp;
  }

  // autres infos
  if (isset($ret['publication'])) {
    $struct['originale'] = $ret['publication'];
  }
  if (isset($ret['etymologie'])) {
    $struct['etymologie'] = $ret['etymologie'];
  }
  if (isset($ret['type'])) {
    $struct['type'] = $ret['type'];
    $struct['type']['source'] = 'LPSN';
  }

  return true;
}

// génération des liens externes (modèles dans Voir aussi)
function m_lpsn_ext($struct) {
  if (isset($struct['liens']['lpsn']['lien'])) {
    $data = $struct['liens']['lpsn'];
    $cdate = dates_recupere();

    $rang = isset($data['rang'])?$data['rang']:$struct['taxon']['rang'];
    $rangL = $data['rang-lpsn'];
    $nom = wp_met_italiques($data['nom'], $rang, $struct['regne']);
    $id = $data['lien'];
    if (isset($data['auteur'])) {
      $nom .= " " . rempl_et_al($data['auteur']);
    }
    return "{{LPSN | $rangL | $id | $nom | consulté le=$cdate}}";
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_lpsn_liens($struct) {
  if (isset($struct['liens']['lpsn']['lien'])) {
    $data = $struct['liens']['lpsn'];
    return "<a href='https://lpsn.dsmz.de/" . $data['rang-lpsn'] . "/" .
           $data['lien'] . "'>LPSN</a>";
  } else {
    return false;
  }
}