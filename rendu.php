<?php

/*
  S'occupe de générer le rendu
*/

// pour la mise en forme des liens auteur
require_once "auteurs.php";


// rendu de l'introduction
function rendu_intro($struct) {
  // on cherche une famille
  $fam = false;
  $famr = false;
  foreach($struct['rangs'] as $r) {
    if ($r['rang'] == 'famille') {
      $fam = $r['nom'];
      $famr = $fam;
      $fam = wp_met_italiques($fam, "famille", $struct['regne'], true);
      break;
    }
  }
  $lien = wp_un_rang($struct['taxon']['rang']);
  $nom = wp_nom_rang($struct['taxon']['rang'], true, false, false);
  $tnom = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
  if (wp_inf_rang($struct['taxon']['rang'])) {
    $tmp = "'''$tnom''' est $lien" . $nom . " ";
  } else {
    $tmp = "Les '''$tnom''' forment $lien" . $nom . " ";
  }
  if ($fam) {
    $tmp .= 'de la [[Famille (biologie)|famille]] des ';
    $tmp .= $fam . ".\n";
  } else {
    $tmp .= ".\n";
  }
  return $tmp;
}


// rendu de la taxobox
function rendu_taxobox($struct) {
  $resu = "";
  $tmp = wp_ebauche($struct);
  if ($tmp != "") {
    $resu .= "{{ébauche|" . $tmp . "}}\n";
  } else {
    $resu .= "{{ébauche}}\n";
  }
  
  // si charte = algue on supprime l'empire
  if ($struct['regne'] == 'algue') {
    wp_supprime_rang($struct, 'empire');
  }

  // données taxobox début
  $taxon = $struct['taxon']['nom'];
  $rang = $struct['taxon']['rang'];
  if (isset($struct['classification-taxobox'])) {
    $classif = $struct['classification-taxobox'];
  } else {
    $classif = "";
  }
  if (isset($struct['image']['image'])) {
    $image = $struct['image']['image'];
    if (isset($struct['image']['legende'])) {
      $legende = $struct['image']['legende'];
    } else {
      $legende = "<!-- insérez légende descriptive de l'image -->";
    }
  } else {
    $image = "<!-- insérez une image -->";
    $legende = "<!-- légende si image -->";
  }
  $regne = $struct['regne'];
  $afftaxon = wp_met_italiques($taxon, $rang, $regne);

  if (isset($struct['regne-cache']) and $struct['regne-cache']) {
    $sup = "| règne=cacher ";
  } else {
    $sup = "";
  }

  // affichage
  $resu .= "{{Taxobox début | $regne | $afftaxon | $image | $legende | classification=$classif $sup}}\n";
  
  // données de classification
  $tbl = [];
  foreach($struct['rangs'] as $r) {
    $rangN = $r['rang'];
    $nom = $r['nom'];
    $tbl[] = "{{Taxobox | $rangN | $nom }}";
  }
  $tbl = array_reverse($tbl);
  // affichage
  $resu .= implode("\n", $tbl);
  $resu .= "\n";
  
  // le taxon lui-même
  $auteur = auteurs_traite(isset($struct['taxon']['auteur'])?$struct['taxon']['auteur']:"");
  $resu .= "{{Taxobox taxon | $regne | $rang | $taxon | $auteur }}\n";
  
  // UICN
  if (isset($struct['liens']['uicn']) and isset($struct['liens']['uicn']['risque'])) {
    $risque = $struct['liens']['uicn']['risque'];
    if (isset($struct['liens']['uicn']['critere'])) {
      $critere = $struct['liens']['uicn']['critere'];
    } else {
      $critere = "";
    }
    $resu .= "{{Taxobox UICN | $risque | $critere }}\n";
  }

  // CITES
  if (isset($struct['liens']['cites']) and isset($struct['liens']['cites']['annexe'])) {
    $annexe = $struct['liens']['cites']['annexe'];
    if (isset($struct['liens']['cites']['date'])) {
      $date = $struct['liens']['cites']['date'];
    } else {
      $date = "";
    }
    if (isset($struct['liens']['cites']['precision'])) {
      $prec = $struct['liens']['cites']['precision'];
    } else {
      $prec = "";
    }
    $resu .= "{{Taxobox CITES | $annexe | $date | $prec }}\n";
  }
  
  // fin
  $resu .= "{{Taxobox fin}}\n";
  
  return $resu;
}

// taxons inférieurs
function rendu_inf($struct) {
  $cdate = dates_recupere();
  if (!isset($struct['sous-taxons']) or empty($struct['sous-taxons'])) {
    return "";
  }
  if (!isset($struct['sous-taxons']['liste']) or empty($struct['sous-taxons']['liste'])) {
    return "";
  }
  //$_rang = cherche_rang($struct['sous-taxons']['liste'][0]['rang'], $struct['sous-taxons']['source']);
  // on cherche la liste des rangs inférieurs
  $_lst = [];
  foreach($struct['sous-taxons']['liste'] as $el) {
    $tmp = wp_nom_rang($el['rang'], false, false, true);
    if ($tmp == "NOTFOUND") { continue; }
    $_lst[$tmp] = $tmp; // pour être unique
  }
  $lst = [];
  foreach($_lst as $l) {
    $lst[] = $l;
  }
  $cnt = count($lst);
  if ($cnt == 0) {
    $rang = "[aucun rang remonté]";
  } else if ($cnt == 1) {
    $rang = $lst[0];
  } else {
    $rang = $lst[0];
    for($i=1; $i<$cnt; $i++) {
      if ($i < $cnt-1) {
        $rang .= ", ";
      } else {
        $rang .= " et ";
      }
      $rang .= $lst[$i];
    }
  }
  $_rang = $lst[0];
  $mdl = $struct['sous-taxons']['source'];
  
  $ret = "\n== Liste des taxons de rang inférieur ==\nListe des $rang selon {{Bioref|$mdl|$cdate}} :\n";
  $ret0 = "";
  foreach($struct['sous-taxons']['liste'] as $l) {
    if (isset($l['rang']) and !empty($l['rang'])) {
      $x = $l['rang'];
    } else {
      $x = $_rang;
    }
    if (isset($l['auteur'])) {
      $auteur = " " . preg_replace("/([^{])et al[.]/", '$1{{et al.}}', $l['auteur']);
    } else {
      $auteur = "";
    }
    $cible = wp_met_italiques($l['nom'], $x, $struct['regne'], true);
    $ret0 .= "* $cible" . $auteur . "\n";
  }
  if (est_colonnes(count($struct['sous-taxons']['liste']))) {
    $ret .= colonnes_contenu($ret0);
  } else {
    $ret .= $ret0;
  }
  return "\n" . $ret;
}

// infos supplémentaires
function rendu_supp($struct) {
  $cdate = dates_recupere();
  $ret = "";
  if (true) {
    $ret .= "== Systématique ==\n";
    // reprise du nom complet
    $REF = $struct['classification'];
    $cible = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
    $z = lien_pour_auteur($struct['regne']);
    $bota = [ "végétal", "champignon", "algue", "bactérie", "archaea" ];
    if (!in_array($struct['regne'], $bota)) {
      $mot = "[[nom valide]]";
    } else {
      $mot = "[[nom correct]]";
    }
    if (isset($struct['taxon']['auteur']) and !empty($struct['taxon']['auteur'])) {
      $ret .= "Le $mot complet (avec [[$z|auteur]]) de ce taxon est " . $cible;
    } else {
      $ret .= "Le $mot de ce taxon est " . $cible;
    }
    if (isset($struct['taxon']['auteur']) and !empty($struct['taxon']['auteur'])) {
      $auteur = " " . preg_replace("/([^{])et al[.]/", '$1{{et al.}}', $struct['taxon']['auteur']);
      $ret .= $auteur;
    }
    $ret .= "{{Bioref|$REF|$cdate|ref}}.\n\n";
    
    if (isset($struct['basionyme'])) {
      $basio = lien_pour_basionyme($struct['regne']);
      $cible = wp_met_italiques($struct['basionyme']['nom'], $struct['taxon']['rang'], $struct['regne']);
      $x = explode(" ", $struct['basionyme']['nom']);
      if (isset($struct['basionyme']['auteur'])) {
        $auteur = " " . preg_replace("/([^{])et al[.]/", '$1{{et al.}}', $struct['basionyme']['auteur']);
      } else {
        $auteur = "";
      }
      if (count($x) == 2) {
        $ret .= "L'espèce a été initialement classée dans le genre ''[[" . $x[0] . "]]'' sous le " .
                $basio . " " .  $cible . $auteur .
                "{{Bioref|" . $struct['basionyme']['source'] .
                "|$cdate|ref}}.\n\n";
      } else {
        $ret .= "Le $basio de ce taxon est : " . $cible .
                $auteur . "{{Bioref|" . $struct['basionyme']['source'] .
              "|$cdate|ref}}\n\n";
      }
    }
    if (isset($struct['vernaculaire'])) {
      // appel à vide (juste un test)
      $cnt = 0;
      $txt = conditionne_noms($struct, $cnt);
      if ($cnt > 1) {
        $pl = "les [[nom vernaculaire|noms vernaculaires]] ou [[nom normalisé|normalisés]] suivants";
      } else {
        $pl = "le [[nom vernaculaire]] ou [[nom normalisé|normalisé]] suivant";
      }
      $ret .= "Ce taxon porte en français $pl : $txt.\n\n";
    }
    if (isset($struct['synonymes'])) {
      $trier = get_config('trier-synonymes');
      $target = lien_pour_synonyme($struct['regne']);
      $cible = wp_met_italiques($struct['taxon']['nom'], $struct['taxon']['rang'], $struct['regne']);
      if (count($struct['synonymes']['liste']) > 1) {
        $pl = "$cible a pour [[$target|synonymes]]";
      } else {
        $pl = "$cible a pour [[$target|synonyme]]";
      }
      $ret .= "$pl" .
              "{{Bioref|" . $struct['synonymes']['source'] . "|$cdate|ref}} :\n";
      $retT = [];
      foreach($struct['synonymes']['liste'] as $s) {
        $wkl = get_config('liens-synonymes');
        $rr = (isset($s['rang'])?$s['rang']:$struct['taxon']['rang']);
        if ($wkl and est_inf_espece($rr)) {
          if (!get_config('liens-inf-sp')) {
            $wkl = false;
          }
        }
        if (isset($s['rang'])) {
          $x = $s['rang'];
        } else {
          $x = $struct['taxon']['rang'];
        }
        $cible = wp_met_italiques($s['nom'], $x, $struct['regne'], $wkl);
        if (isset($s['auteur'])) {
          $auteur = " " . preg_replace("/([^{])et al[.]/", '$1{{et al.}}', $s['auteur']);
        } else {
          $auteur = "";
        }
        $retT[] = "* $cible" . $auteur . "\n";
      }
      if ($trier) {
        sort($retT);
      }
      $ret0 = implode($retT);
      if (est_colonnes(count($struct['synonymes']['liste']))) {
        $ret .= colonnes_contenu($ret0);
      } else {
        $ret .= $ret0;
      }
    }
  }

  if (!empty($ret)) {
    return "\n\n" . $ret;
  } else {
    return "";
  }
}

// rendu de la zone "Description"
function rendu_description($struct) {
  $resu = "\n== Description ==\n";
  if (!isset($struct['description'])) {
    $resu .= "{{Section vide ou incomplète}}\n";
    return $resu;
  }
  foreach($struct['description'] as $ref => $liste) {
    $resu .= implode(". ", $liste);
    $resu .= "{{Bioref|$ref|ref}}.";
  }
  return $resu . "\n";
}

// rendu de la zone "Distribution"
function rendu_distribution($struct) {
  $resu = "\n== Distribution ==\n";
  if (!isset($struct['distribution'])) {
    $resu .= "{{Section vide ou incomplète}}\n";
    return $resu;
  }
  // conversion code-pays
  $source = "";
  $certain = [];
  $uncertain = [];
  foreach($struct['distribution'] as $ref => $liste) {
    $source = $ref;
    if (isset($liste['certain'])) {
      foreach($liste['certain'] as $code) {
        $tmp = data_pays_code($code);
        $certain[] = $tmp;
      }
    }
    if (isset($liste['uncertain'])) {
      foreach($liste['uncertain'] as $code) {
        $tmp = data_pays_code($code);
        $uncertain[] = $tmp;
      }
    }
  }
  if (!empty($certain)) {
    sort($certain);
    $certain = array_unique($certain);
  }
  if (!empty($uncertain)) {
    sort($uncertain);
    $uncertain = array_unique($uncertain);
  }
  if (count($struct['distribution']) == 1) {
    if (!empty($certain)) {
      if (count($certain) > 1) {
        $resu .= "Ce taxon se rencontre dans les pays suivants{{Bioref|$source|ref}} : ";
      } else {
        $resu .= "Ce taxon se rencontre dans le pays suivant{{Bioref|$source|ref}} : ";
      }
      $resu .= implode(", ", $certain);
      $resu .= ".\n";
    }
    if (!empty($uncertain)) {
      if (!empty($certain)) {
        $resu .= "\n";
      }
      if (count($uncertain) > 1) {
        $resu .= "La présence de ce taxon est incertaine dans les pays suivants{{Bioref|$source|ref}} : ";
      } else {
        $resu .= "La présence de ce taxon est incertaine dans le pays suivant{{Bioref|$source|ref}} : ";
      }
      $resu .= implode(", ", $uncertain);
      $resu .= ".\n";
    }
  } else {
    $resu .= "''Une distribution issue de plusieurs sources existe. Non implémenté pour le moment''\n";
  }
  return $resu;
}

// rendu "tymologie
function rendu_etymologie($struct) {
  if (!isset($struct['etymologie'])) {
    return "";
  }
  $resu = "\n== Étymologie ==\n";
  $resu .= $struct['etymologie']['texte'] . "{{Bioref|" . $struct['etymologie']['source'] . "|ref}}.\n";
  return $resu;
}

// rendu publication originale
function rendu_originale($struct) {
  if (!isset($struct['originale'])) {
    return "";
  }
  
  if (is_array($struct['originale']) and (count($struct['originale']) > 1)) {
    $resu = "\n== Publications originales ==\n";
  } else {
    $resu = "\n== Publication originale ==\n";
  }
  if (is_array($struct['originale'])) {
    foreach($struct['originale'] as $pub) {
    $resu .= "* " . $pub . "\n";
    }
  } else {
    $resu .= "* " . $struct['originale'] . "\n";
  }
  return $resu;
}

// rendu de la zone voir aussi
function rendu_voir_aussi($struct) {
  global $gauto;
  $resu = "";
  $ext = [];
  $ref = [];
  $autres = [];
  
  // autres projets
  if (isset($struct['liens']['externe']['commons']) or isset($struct['liens']['externe']['species']) or
      isset($struct['liens']['externe']['ccommons'])) {
    $tmp = [];
    // page commons, sinon catégorie commons (si présent)
    if (isset($struct['liens']['externe']['commons']['page'])) {
      $tmp[] = "commons=" . $struct['liens']['externe']['commons']['page'];
    } elseif (isset($struct['liens']['externe']['ccommons']['page'])) {
      $tmp[] = "commons=Category:" . $struct['liens']['externe']['ccommons']['page'];
    }
    if (isset($struct['liens']['externe']['species']['page'])) {
      $tmp[] = "species=" . $struct['liens']['externe']['species']['page'];
    }
    $autres = $tmp;
  }
  
  // traitement liens externes
  if (isset($struct['liens'])) {
    foreach($struct['liens'] as $mod => $data) {
      $f = "m_" . $mod . "_ext";
      if (function_exists($f)) {
        $tmp = $f($struct);
      } else {
        $tmp = false;
      }
      if ($tmp) {
        // la génération des liens externes peut retourner une table de liens
        if (is_array($tmp)) {
          foreach($tmp as $t) {
            $ext[] = $t;
          }
        } else {
          $ext[] = $tmp;
        }
      }
    }
  }
  
  // TODO: différencier $ext de $ref (ou alors supprimer $ref et ses traitements)

  if (!empty($ext) or !empty($ref) or ! empty($autres)) {
    $resu .= "== Liens externes ==\n";
    if (!empty($autres)) {
      sort($autres);
      $resu .= "{{Autres projets\n";
      foreach($autres as $a) {
        $resu .= "| $a\n";
      }
      $resu .= "}}\n";
    }
    if (!empty($ext)) {
      if (count($ext) > 1) {
        //$resu .= "=== Références biologiques ===\n";
      } else {
        //$resu .= "=== Référence biologique ===\n";
      }
      natsort($ext);
      foreach($ext as $e) {
        $resu .= "* $e\n";
      }
    }
    if (!empty($ref)) {
      if (count($ref) > 1) {
        //$resu .= "=== Références taxinomiques ===\n";
      } else {
        //$resu .= "=== Référence taxinomique ===\n";
      }
      natsort($ref);
      foreach($ref as $r) {
        $resu .= "* $r\n";
      }
    }
  }
  
  if (!empty($resu)) {
    return "\n$resu";
  } else {
    return "";
  }
}

// rendu de fin d'article (catégories, portails…)
function rendu_fin($struct) {
  $ret = "\n== Notes et références ==\n{{références}}\n";
  if (!empty($struct['liens']['fin']['portails'])) {
    $ret .= "\n{{Portail|" . implode("|", $struct['liens']['fin']['portails']) . "}}\n";
  }
  if (!empty($struct['liens']['fin']['categories'])) {
    $ret .= "\n";
    foreach($struct['liens']['fin']['categories'] as $c) {
      $ret .= "[[Catégorie:" . $c . "]]\n";
    }
  }
  return $ret;
}

// fonction de rendu global
function rendu($struct) {
  $ret = "";
  
  // taxobox
  $ret .= rendu_taxobox($struct);
  // intro
  $ret .= rendu_intro($struct);
  // description
  $ret .= rendu_description($struct);
  // distribution
  $ret .= rendu_distribution($struct);
  // taxons inférieurs
  $ret .= rendu_inf($struct);
  // informations additionnelles
  $ret .= rendu_supp($struct);
  // étymologie
  $ret .= rendu_etymologie($struct);
  // publication originale
  $ret .= rendu_originale($struct);
  // partie voir aussi
  $ret .= rendu_voir_aussi($struct);
  // partie finale
  $ret .= rendu_fin($struct);
  
  // nettoyage : suppression des doubles-sauts
  $ret = str_replace("\n\n\n", "\n\n", $ret);
  $ret = str_replace("\n\n\n", "\n\n", $ret);
  
  return $ret;
}

