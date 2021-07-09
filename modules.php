<?php

/*
  Gestion des modules d'accès aux données (déclarations, fonctionnalités…)
*/

// classification par défaut
$m_default = false;

// tableau des modules et de leurs fonctionnalités
$m_modules = [

];

// références pour les domaines
$domaines_true = [
  'algue' => [
    'accepte' => true,
    'sous' => [],
  ],
  'animal' => [
    'accepte' => true,
    'sous' => [
      'oiseau' => [
        'accepte' => true,
        'sous' => [],
      ],
      'reptile' => [
        'accepte' => true,
        'sous' => [],
      ],
      'poisson' => [
        'accepte' => true,
        'sous' => [],
      ],
      'mammifère' => [
        'accepte' => true,
        'sous' => [],
      ],
      'amphibien' => [
        'accepte' => true,
        'sous' => [],
      ],
    ],
  ],
  'archaea' => [
    'accepte' => true,
    'sous' => [],
  ],
  'bactérie' => [
    'accepte' => true,
    'sous' => [],
  ],
  'champignon' => [
    'accepte' => true,
    'sous' => [],
  ],
  'protiste' => [
    'accepte' => true,
    'sous' => [],
  ],
  'végétal' => [
    'accepte' => true,
    'sous' => [],
  ],
  'virus' => [
    'accepte' => true,
    'sous' => [],
  ],
  'neutre' => [
    'accepte' => true,
    'sous' => [],
  ],
  'eucaryote' => [
    'accepte' => true,
    'sous' => [],
  ],
  'procaryote' => [
    'accepte' => true,
    'sous' => [],
  ],
];
$domaines_false = [
  'algue' => [
    'accepte' => false,
    'sous' => [],
  ],
  'animal' => [
    'accepte' => false,
    'sous' => [
      'oiseau' => [
        'accepte' => false,
        'sous' => [],
      ],
      'reptile' => [
        'accepte' => false,
        'sous' => [],
      ],
      'poisson' => [
        'accepte' => false,
        'sous' => [],
      ],
      'mammifère' => [
        'accepte' => false,
        'sous' => [],
      ],
      'amphibien' => [
        'accepte' => false,
        'sous' => [],
      ],
    ],
  ],
  'archaea' => [
    'accepte' => false,
    'sous' => [],
  ],
  'bactérie' => [
    'accepte' => false,
    'sous' => [],
  ],
  'champignon' => [
    'accepte' => false,
    'sous' => [],
  ],
  'protiste' => [
    'accepte' => false,
    'sous' => [],
  ],
  'végétal' => [
    'accepte' => false,
    'sous' => [],
  ],
  'virus' => [
    'accepte' => false,
    'sous' => [],
  ],
  'neutre' => [
    'accepte' => false,
    'sous' => [],
  ],
  'eucaryote' => [
    'accepte' => false,
    'sous' => [],
  ],
  'procaryote' => [
    'accepte' => false,
    'sous' => [],
  ],
];


// insert récursivement la valeur $val pour l'entrée $nom et ses descendants dans le $domaine
function rec_domaines(&$domaine, $nom, $val) {
  foreach($domaine as $id => $cont) {
    if (($nom == "*") or ($id == $nom)) {
      $domaine[$id]['accepte'] = $val;
      // si présents on fait pareil sur les descendants
      foreach($cont['sous'] as $id2 => $cont2) {
        rec_domaines($domaine[$id]['sous'], "*", $val);
      }
    } else {
      // on regarde dans les sous-domaines
      rec_domaines($domaine[$id]['sous'], $nom, $val);
    }
  }
}

// gestion des domaines : crée une table des domaines gérés ou pas. Tout domaine ajouté
// ajoute automatiquement tous les sous-domaines concernés (dans un sens ou dans l'autre)
// si $def vaut TRUE tous les domaines sont acceptés, sauf ceux listés
// si $def vaut FALSE tous les domaines sont refusés, sauf ceux listés
function creer_domaines($def, $liste) {
  global $domaines_true, $domaines_false;
  
  if ($def) {
    $domaines = $domaines_true;
    $val = false;
  } else {
    $domaines = $domaines_false;
    $val = true;
  }
  foreach($liste as $d) {
    // on l'ajoute
    rec_domaines($domaines, $d, $val);
  }
  return $domaines;
}

// fonction de comparaison de modules (ordre décroissant de la priorité)
function mod_trie($a, $b) {
  if (!isset($a['niveau'])) {
    return 1;
  }
  if (!isset($b['niveau'])) {
    return -1;
  }
  return $b['niveau'] - $a['niveau'];
}

// déclare l'existence d'un module
// $classif : true si est utilisable comme classification
// $ext : true si utilisable pour générer des liens externes
// $domaines : liste de noms de domaines (ex. : animal ; oiseau…) applicables
// Si la liste est vide tous les domaines sont applicables
function declare_module($nom, $classif, $ext, $domaines, $niveau=0, $default=false) {
  global $m_modules, $m_default;

  if (isset($m_modules[$nom])) {
    logs("declare_module: le module '$nom' est déjà déclaré");
    return false;
  }
  
  // par défaut ?
  if ($default) {
    $m_default = $nom;
  }
  
  // initialisation du module
  $blob = [];
  $blob['nom'] = $nom;
  $blob['classification'] = $classif;
  $blob['exterieur'] = $ext;
  $blob['niveau'] = $niveau;
  $blob['domaines-raw'] = [];
  if ($domaines === true) {
    $blob['domaines'] = creer_domaines(true, []);
    $blob['domaines-raw'] = [ 'tous' ];
  } else if ($domaines === false) {
    $blob['domaines'] = creer_domaines(false, []);
    $blob['domaines-raw'] = [ 'aucun' ];
  } else {
    $blob['domaines'] = creer_domaines(false, $domaines);
    $blob['domaines-raw'] = $domaines;
  }
  
  $m_modules[$nom] = $blob;
  
  // trier les modules présents selon leur priorité
  uasort($m_modules, "mod_trie");

  return true;
}

// retourne une table de la liste des modules (pour affichage)
function affiche_modules() {
  global $m_modules;
  $out = [];
  
  foreach($m_modules as $nom => $cont) {
    $txt = "$nom : ";
    if ($cont['classification']) {
      $txt .= "oui ";
    } else {
      $txt .= "non ";
    }
    if ($cont['exterieur']) {
      $txt .= "oui ";
    } else {
      $txt .= "non ";
    }
    $txt .= implode(", ", $cont['domaines-raw']);
    $out[] = $txt;
  }
  
  return $out;
}

// retourne la liste de tous les modules (dans le répertoire modules/)
function cherche_modules() {
  $fichiers = scandir("./modules");
  if ($fichiers === false) {
    error("cherche_modules: échec du 'scandir'");
    return false;
  }
  $tbl = [];
  foreach($fichiers as $fic) {
    // seulement les fichiers de format mod_XXXXX.php
    if (strpos($fic, "mod_") !== 0) {
      continue;
    }
    if (strpos("$fic", ".php") === false) {
      continue;
    }
    $tbl[] = $fic;
  }
  return $tbl;
}

// conversion nom-de-module → identifiant-de-module
function noms_vers_identifiants($liste) {
  $tbl = [];
  foreach($liste as $m) {
    $id = preg_replace("/mod_([^.]*)[.]php/", '$1', $m);
    if (($id == "") or ($id == $m)) {
      error("noms_vers_identifiants: l'entrée '$m' n'a pas pu être convertie");
      continue;
    }
    $tbl[] = $id;
  }
  return $tbl;
}

// retourne vrai si au moins l'un des (sous)éléments a 'accepte'
function vrai_dans_domaine($def) {
  if (empty($def)) {
    return false;
  }
  foreach($def as $nom => $data) {
    if ($data['accepte']) {
      return true;
    }
    $ret = vrai_dans_domaine($data['sous']);
    if ($ret) {
      return true;
    }
  }
  return false;
}

// retourne le contenu du domaine demandé
function rec_contenu_domaine($domaine, $def) {
  foreach($def as $nom => $data) {
    if ($nom == $domaine) {
      return $data;
    }
    $ret = rec_contenu_domaine($domaine, $data['sous']);
    if ($ret !== false) {
      return $ret;
    }
  }
  return false;
}

// recherche stricte sur un domaine
function rec_strict_domaine($domaine, $def) {
  foreach($def as $nom => $data) {
    if (($nom == $domaine) and ($data['accepte'])) {
      return true;
    }
    $ret = rec_strict_domaine($domaine, $data['sous']);
    if ($ret) {
      return true;
    }
  }
  return false;
}

// recherche les modules qui peuvent traiter le domaine concerné
function modules_possibles($domaine) {
  global $m_modules;
  $tbl = [];
  // si domaine=* on ajoute tout
  if ($domaine == "*") {
    foreach($m_modules as $nom => $data) {
      $tbl[] = $nom;
    }
    return $tbl;
  }
  // on teste
  foreach($m_modules as $nom => $data) {
    // d'abord une recherche exacte
    $ret = rec_strict_domaine($domaine, $data['domaines']);
    if ($ret) {
      // on l'ajoute et on passe au suivant
      $tbl[] = $nom;
      continue;
    }
    // pas en direct, on cherche par propagation de sous-domaine
    // on cherche l'élément qui correspond au domaine demandé
    $base = rec_contenu_domaine($domaine, $data['domaines']);
    $tmp = [];
    $tmp['anonymous'] = $base;
    if (vrai_dans_domaine($tmp)) {
      $tbl[] = $nom;
    }
  }
  return $tbl;
}

// parcours récursivement les (sous)domaines pour voir le plus profond qui correspond
function rec_prof_classification($def, $domaine, $prof) {
  $ret = 0;
  foreach($def as $id => $cont) {
    $ret2 = 0;
    // si correspondance exacte
    if ((($domaine == '*') or ($id == $domaine)) and ($cont['accepte'])) {
      $ret = $prof + 1;
    } else {
      $ret2 = rec_prof_classification($cont['sous'], "*", $prof+1);
    }
    // si on trouve "mieux" on change
    if ($ret2 > $ret) {
      $ret = $ret2;
    }
  }
  return $ret;
}

// retourne à quelle profondeur la classification indiquée correspond au domaine
function profondeur_classification($nom, $domaine) {
  global $m_modules;
  
  $classif = $m_modules[$nom]['domaines'];
  $prof = rec_prof_classification($classif, $domaine, 0);
  
  return $prof;
}

// retourne la profondeur du domaine demandé
function profondeur_domaine($def, $domaine, $prof) {
  foreach($def as $nom => $cont) {
    if ($nom == $domaine) {
      return $prof+1;
    }
    // on cherche dessous
    $ret = profondeur_domaine($cont['sous'], $domaine, $prof+1);
    if ($ret > $prof) {
      return $ret;
    }
  }
  return $prof;
}

// recherche la meilleure classification à appliquer au domaine sélectionné
function meilleure_classification($domaine) {
  global $m_modules, $m_default;
  global $domaines_true;

  // si le domaine est "*" on retourne celui par défaut
  if ($domaine == "*") {
    return $m_default;
  }

  // on cherche les modules qui supportent la classification
  $tbl = [];
  foreach($m_modules as $nom => $data) {
    if ($data['classification']) {
      $tbl[] = $nom;
    }
  }
  // pas trouvé, on ne peut rien faire
  if (empty($tbl)) {
    logs("meilleure_classification: aucune classification !");
    return false; // ne devrait pas se produire
  }
  
  // si un seul trouvé, on le retourne
  if (count($tbl) == 1) {
    return $tbl[0]; // ne devrait pas arriver
  }
  
  // on cherche la profondeur du domaine
  $prof = profondeur_domaine($domaines_true, $domaine, 0);
  
  // on cherche la (ou les) classification la plus précise qui gère ce domaine
  $tbl2 = [];
  foreach($tbl as $c) {
    // on cherche à quelle profondeur cette classification correspond à ce domaine
    $val = profondeur_classification($c, $domaine);
    if ($val > 0) {
      $tbl2[$c] = $val;
    }
  }
  
  // si pas trouvé on retourne la classification par défault
  if (empty($tbl2)) {
    return $m_default;
  }
  // s'il n'y en a qu'une on la retourne
  if (count($tbl2) == 1) {
    return array_keys($tbl2)[0];
  }
  // s'il y en a plus on cherche ceux qui ont exactement la même profondeur
  $tbl3 = [];
  foreach($tbl2 as $nom => $eprof) {
    if ($eprof == $prof) {
      $tbl3[] = $nom;
    }
  }
  // si aucun trouvé on retourne celui par défaut
  if (empty($tbl3)) {
    return $m_default;
  }
  // si un seul on le retourne
  if (count($tbl3) == 1) {
    return $tbl3[0];
  }
  // si plusieurs et que celle par défaut est présente on la retourne
  foreach($tbl3 as $c) {
    if ($c == $m_default) {
      return $c;
    }
  }
  // sinon tri par le niveau de préférence et on prend le "meilleur"
  $p = [];
  foreach($m_modules as $nom => $cont) {
    if (in_array($nom, $tbl3)) {
      if (isset($cont['niveau'])) {
        $p[$nom] = $cont['niveau'];
      } else {
        $p[$nom] = 0;
      }
    }
  }
  asort($p);
  $p = array_reverse($p);
  // on retourne le premier (celui qui est le plus "haut")
  return $p[0];
}

