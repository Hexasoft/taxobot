<?php

/*
  Module pour wcvp (non classification)
*/

// déclaration du module
function m_wcvp_init() {
  return declare_module("wcvp", false, true, ['végétal']);
}

// récupération des infos. Résultats à stocker dans $struct. Si $classif=TRUE doit
// gérer la classification également
function m_wcvp_infos(&$struct, $classif) {
  $tous_non_valides = get_config('inclure-invalides');
  $taxon = $struct['taxon']['nom'];

  $url = "https://wcvp.science.kew.org/api/v1/search?q=" . urlencode($taxon);
  $ret = get_data($url);
  if ($ret === false) {
    logs("WCVP: echec de récupération réseau");
    return false;
  }
  $res = json_decode($ret);
  if ($res === null) {
    logs("WCVP: Echec de décodage des données");
    return false;
  }

  if (!isset($res->results)) {
    logs("WCVP: Pas de réponse pour ce taxon");
    return false;
  }

  // parcours
  $ok = false;
  foreach($res->results as $r) {
    if (!isset($r->id) or !isset($r->name) or !isset($r->accepted) or !$r->accepted) {
      continue;
    }
    if ($r->name != $taxon) {
      continue;
    }
    // trouvé
    $blob = [];
    $blob['id'] = $r->id;
    $blob['nom'] = $r->name;
    $blob['auteur'] = $r->author;
    $struct['liens']['wcvp'] = [];
    $struct['liens']['wcvp'][] = $blob;
    $ok = true;
    break;
  }
  if (!$ok) {
    // on cherche idem mais avec les non acceptés ("nv")
    foreach($res->results as $r) {
      // pas besoin de tester 'accepted' : on l'a testé avant
      if (!isset($r->id) or !isset($r->name)) {
        continue;
      }
      if ($r->name != $taxon) {
        continue;
      }
      // trouvé
      $blob = [];
      $blob['id'] = $r->id;
      $blob['nom'] = $r->name;
      $blob['auteur'] = $r->author;
      $blob['synonyme'] = true;
      // on l'ajoute seulement si on accepte les non valides
      if ($tous_non_valides) {
        $struct['liens']['wcvp'] = [];
        $struct['liens']['wcvp'][] = $blob;
      }
      // on ajoute (si possible) la cible valide selon eux
      if (isset($r->synonymOf)) {
        $blob = [];
        $blob['id'] = $r->synonymOf->id;
        $blob['nom'] = $r->synonymOf->name;
        if (isset($r->synonymOf->author)) {
          $blob['auteur'] = $r->synonymOf->author;
        }
        if (!isset($struct['liens']['wcvp'])) {
          $struct['liens']['wcvp'] = [];
        }
        $struct['liens']['wcvp'][] = $blob;
      }
      $ok = true;
      break;
    }
  }
  if (!$ok) {
    logs("WCVP: Taxon non trouvé (même non valide) dans la liste retournée");
    return false;
  }
  // si pas plus loin, retour
  if (!$classif) {
    return true;
  }

  // pas de classification
  return false;
}

// génération des liens externes (modèles dans Voir aussi)
function m_wcvp_ext($struct) {
  $cdate = dates_recupere();
  if (isset($struct['liens']['wcvp'])) {
    $tbl = [];
    $tblx = $struct['liens']['wcvp'];
    foreach($tblx as $t) {
      if (isset($t['id'])) {
        $txt = $t['nom'];
        if (isset($t['auteur'])) {
          $auteur = " " . $t['auteur'];
        }
        if (isset($t['synonyme']) and $t['synonyme']) {
          $sup = " | nv";
        } else {
          $sup = "";
        }
        $tbl[] = "{{WCVP | " . $t['id'] . " | " . $txt . " | " . $auteur . $sup . " | consulté le=$cdate }}";
        $tbl[] = "{{POWO | " . $t['id'] . " | " . $txt . " | " . $auteur . $sup . " | consulté le=$cdate }}";
        $tbl[] = "{{IPNI | " . $t['id'] . " | " . $txt . " | " . $auteur . " | consulté le=$cdate }}";
      }
    }
    return $tbl;
  } else {
    return false;
  }
}

// génération de liens vers les éléments (pour partie aide/debug de l'interface)
function m_wcvp_liens($struct) {
  if (isset($struct['liens']['wcvp'])) {
    $tbl = [];
    $tblx = $struct['liens']['wcvp'];
    $tmp = [];
    foreach($tblx as $t) {
      if (isset($t['synonyme']) and $t['synonyme']) {
        $nv = " <small>(nv)</small>";
      } else {
        $nv = "";
      }
      $tmp[] = "<a href='https://wcvp.science.kew.org/taxon/" . $t['id'] .
               "'>WCVP$nv</a>";
      $tmp[] = "<a href='http://powo.science.kew.org/taxon/" . $t['id'] .
               "'>POWO$nv</a>";
      $tmp[] = "<a href='https://www.ipni.org/n/" . $t['id'] .
               "'>IPNI$nv</a>";
    }
    return $tmp;
  } else {
    return false;
  }
}