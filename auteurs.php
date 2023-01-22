<?php

/*
  Traitements de la zone « auteurs » (et données associées)
*/


////// nouvelle partie : en test
require "liste_botanistes.php"; // $aut_botanistes[]
require "liste_zoologistes.php"; // $aut_zoologistes[]

// séparateur ? (basique)
function est_separateur($e) {
  if (($e == ',') or ($e == '&') or ($e == ';') or ($e == ':') or
      ($e == '(') or ($e == ')') or ($e == '[') or ($e == ']')) {
    return true;
  }
  return false;
}

// true si hors tableau ou si séparateur (option pour espace=séparateur)
function est_separateur_t($tbl, $i, $sp=false) {
  if (!isset($tbl[$i])) {
    return true;
  }
  $e = $tbl[$i];
  if ($sp and ($e == ' ')) {
    return true;
  }
  if (($e == ',') or ($e == '&') or ($e == ';') or ($e == ':') or
      ($e == '(') or ($e == ')') or ($e == '[') or ($e == ']')) {
    return true;
  }
  return false;
}

// mot à la position courante ?
function est_mot($tbl, $i, $mot, &$der) {
  $tmot = preg_split('//u', $mot, -1, PREG_SPLIT_NO_EMPTY);
  $tmot = array_reverse($tmot);
  $nb = count($tmot);
  $match = true;
  $ou = 0;
  for($p=$i; $p>$i-$nb; $p--) {
    if (!isset($tbl[$p])) {
      $match = false;
      break;
    }
    if ($tbl[$p] != $tmot[$ou]) {
      $match = false;
      break;
    }
    $ou++;
  }
  if (!$match) {
    return false;
  }
  // a-t-on un séparateur de chaque coté ?
  if (est_separateur_t($tbl, $i+1, true) and est_separateur_t($tbl, $i-$nb, true)) {
    if (isset($tbl[$i-$nb])) {
      $der = $i-$nb;
    } else {
      $der = -1;
    }
    return true;
  }
  return false;
}

// une date ?
function est_date($txt) {
  $val = (int)trim($txt);
  if (($val >1350) and ($val < 2100)) {
    return true;
  }
  return false;
}

// tente d'identifier un auteur
function identifie_auteur($struct, $nom, $date, &$suggestions) {
  global $aut_botanistes;
  global $aut_zoologistes;
  $nom = trim($nom);
  // si règne relevant de la botanique on teste les formes auteur référencées
  if (($struct['regne'] == 'végétal') or ($struct['regne'] == 'champignon')) {
    $nomc = str_replace(" ", "", $nom);
    // on cherche
    foreach($aut_botanistes as $el) {
      if (($nom == $el[0]) or ($nomc == $el[0])) {
        // on ne vérifie pas les dates, c'est supposé être normalisé
        return "[[" . $el[1] . "|" . $el[0] . "]]";
      }
    }
    return false;
  }
  
  if ($struct['regne'] == 'virus') {
    // pas de gestion des auteurs pour les virus
    return false;
  }
  
  // zoologiste, on parcours la liste
  $possibles = [];
  foreach($aut_zoologistes as $el) {
    if ($nom == $el[0]) {
      $possibles[] = $el;
    }
  }
  if (empty($possibles)) {
    return false; // aucun candidat
  }
  // si on n'a pas de date on retourne juste les possibles
  if (!$date) {
    foreach($possibles as $p) {
      $suggestions[] = $p;
    }
    return false;
  }
  
  // dates : on teste avec un encadrement
  foreach($possibles as $p) {
    $d = $p[2];
    $f = $p[4];
    $v = $p[3];
    if (!$d and !$f and !$v) {
      // aucune date, on le met au cas où
      $suggestions[] = $p;
      continue;
    }
    if ($d and $f) {
      $debut = $d+15;
      $fin = $f;
    } else if (!$d and !$f) {
      $debut = $v - 10;
      $fin = $v + 10;
    } else if ($d and !$f) {
      $debut = $d+15;
      $fin = $d+70;
    } else if (!$d and $f) {
      $fin = $f;
      $debut = $f-70;
    }
    if (($date >= $debut) and ($date <= $fin)) {
      $suggestions[] = $p;
    }
  }
  // si demandé et une seule suggestion on l'utilise
  if ((get_config("auteurs") == 'n1') and (count($suggestions) == 1)) {
    $tmp = $suggestions[0];
    $suggestions = [];
    return "[[" . $tmp[1] . "|" . $tmp[0] . "]]";
  }
  
  // de toute façon on ne retourne rien
  return false;
}


// coupe un élément de type chaîne (non traitée)
// $suggestions contient des auteurs *possibles*
function aut_analyse($struct, $el, &$suggestions, &$dt) {
  $mots = [ ['ex.',true], ['ex',true], ['in.',true], ['in',true], ['and',false], ['et al.',false],
            ['emend.',true], ];

  $dt = false;
  $suggestions = [];
  $resu = [];
  $el = trim($el);
  $buf = "";
  // on découpe le contenu à partir des éléments simples
  $tbl = preg_split('//u', $el, -1, PREG_SPLIT_NO_EMPTY);
  foreach($tbl as $e) {
    if (est_separateur($e)) {
      if (!empty($buf)) {
        $out = [];
        $out['type'] = 'cur';
        $out['texte'] = $buf;
        $resu[] = $out;
        $buf = "";
      }
      $out = [];
      $out['type'] = 'sep';
      $out['texte'] = $e;
      $resu[] = $out;
      $buf = "";
      continue;
    }
    $buf .= $e;
  }
  // le dernier éventuellement
  if (!empty($buf)) {
    $out = [];
    $out['type'] = 'cur';
    $out['texte'] = $buf;
    $resu[] = $out;
  }

  $resu2 = $resu;
  $resu = [];

  // on reprend chaque élément non traité pour analyse des mots séparateurs
  $last = 0;
  foreach($resu2 as $el) {
    if ($el['type'] != 'cur') {
      $resu[] = $el;
      continue;
    }
    $tbl = preg_split('//u', $el['texte'], -1, PREG_SPLIT_NO_EMPTY);
    $last = 0;
    foreach($tbl as $idx => $e) {
      $ok = false;
      $der = -1;
      foreach($mots as $x) {
        if (est_mot($tbl, $idx, $x[0], $der)) {
          $ok = true;
          $qui = $x[0];
          break;
        }
      }
      if ($ok) {
        // on traite ce qui est avant
        if ($der >= 0) {
          $buf = "";
          for($p=$last; $p<=$der; $p++) {
            $buf .= $tbl[$p];
          }
          $out = [];
          $out['type'] = 'cur';
          $out['texte'] = $buf;
          $resu[] = $out;
          $last = $der+1;
        }
        // le séparateur
        $out = [];
        $out['type'] = 'sep';
        if ($qui == 'and') {
          $qui = '&'; // on standardise
        }
        if ($qui == 'et al.') {
          $qui = '{{et al.}}'; // on standardise
        }
        $out['texte'] = $qui;
        $resu[] = $out;
        $last = $idx+1;
      }
    }
    // à la fin si besoin on ajoute
    if ($last < count($tbl)) {
      $buf = "";
      for($p=$last; $p<count($tbl); $p++) {
        $buf .= $tbl[$p];
      }
      $out = [];
      $out['type'] = 'cur';
      $out['texte'] = $buf;
      $resu[] = $out;
    }
  }
  
  $resu2 = $resu;
  $resu = [];
  $date = false;
  // on cherche les dates
  foreach($resu2 as $el) {
    if ($el['type'] != 'cur') {
      $resu[] = $el;
      continue;
    }
    if (est_date($el['texte'])) {
      $out = [];
      $out['type'] = 'date';
      $out['texte'] = "[[" . trim($el['texte']) . " en science|" . trim($el['texte']) . "]]";
      $date = (int)trim($el['texte']);
      $dt = $date;
      $resu[] = $out;
    } else {
      $resu[] = $el;
    }
  }

  $resu2 = $resu;
  $resu = [];
  // on cherche à résoudre ce qui reste comme auteur
  foreach($resu2 as $el) {
    if ($el['type'] != 'cur') {
      $resu[] = $el;
      continue;
    }
    $res = identifie_auteur($struct, $el['texte'], $date, $suggestions);
    if ($res === false) {
      $resu[] = $el;
    } else {
      $out = [];
      $out['type'] = 'nom';
      $out['texte'] = $res;
      $resu[] = $out;
    }
  }
  
  // ce qui reste est indiqué avec le modèle {{auteur}}
  $resu2 = $resu;
  $resu = [];
  foreach($resu2 as $el) {
    if ($el['type'] != 'cur') {
      $resu[] = $el;
      continue;
    }
    if (empty(trim($el['texte']))) {
      // les parties vides ne sont pas retenues
      continue;
    }
    $out = [];
    $out['type'] = 'nom';
    $out['texte'] = "";
    $tmp = $el['texte'];
    if ($tmp[0] == ' ') {
      $out['texte'] .= " ";
    }
    $out['texte'] = '{{auteur|[[' . trim($tmp) . ']]}}';
    if (mb_substr($tmp, -1) == ' ') {
      $out['texte'] .= " ";
    }
    $resu[] = $out;
  }
  
  return $resu;
}

// fabrique une version texte à partir d'une structure
function aut_vers_texte($arbre) {
  // table des séparateurs avec leurs caractéristiques (texte, italique, sp avant, sp après)
  $seps = [ ['(',false,true,false],['[',false,true,false],[')',false,false,true],['[',false,false,true],
            [',',false,false,true],[';',false,true,true],[':',false,true,true],
            ['in.',true,true,true],['in',true,true,true],['ex.',true,true,true],['ex',true,true,true],
            ['and',false,true,true],['&',false,true,true],['{{et al.}}',false,true,false],
            ['emend.',true,true,false],
          ];
  $resu = "";
  
  foreach($arbre as $el) {
    if ($el['type'] == 'sep') {
      $pre = true;
      $post = true;
      $it = true;
      foreach($seps as $sep) {
        if ($sep[0] == $el['texte']) {
          $it = $sep[1];
          $pre = $sep[2];
          $post = $sep[3];
          break;
        }
      }
      if ($pre) {
        $resu .= ' ';
      }
      if ($it) {
        $resu .= "''";
      }
      $resu .= $el['texte'];
      if ($it) {
        $resu .= "''";
      }if ($post) {
        $resu .= ' ';
      }
      continue;
    }
    $resu .= $el['texte'];
  }
  $resu = preg_replace('/  /', ' ', $resu);
  $resu = preg_replace('/  /', ' ', $resu);
  $resu = preg_replace('/  /', ' ', $resu);
  return trim($resu);
}


// traitement de la liste des auteurs ($auteurs) : retourne le texte à intégrer dans {{taxobox taxon}}
function new_auteurs_traite(&$struct, $auteurs) {
  // précaution
  $auteurs = trim($auteurs);
  if ($auteurs == "") {
    return $auteurs;
  }

  $sug = [];
  $date = false;
  $arbre = aut_analyse($struct, $auteurs, $sug, $date);
  $texte = aut_vers_texte($arbre);
  if (!$date) {
    $texte .= " {{date à préciser}}";
  }

  if (!empty($sug)) {
    $struct['suggestions'] = $sug;
    // pour le moment on génère les suggestions dans les logs
      logs("Suggestions d'auteurs (abréviation, lien, date naissance, activité vers, date mort) :");
    foreach($sug as $s) {
      logs($s[0] . " → [[" . $s[1] . "]] (" . ($s[2]?$s[2]:"-") . "," . ($s[3]?$s[3]:"-") . "," . ($s[4]?$s[4]:"-") . ")");
    
    }
  }
  return $texte;
}




// mots à ignorer (même si certains sont généralement collés)
$auteurs_ignore = [ "ex.", "ex", "in", "in.", "and", "&", "[", "]", ",", "(", ")" ];

// noms connus pour avoir un espace dedans (pour protection contre les erreurs)
$auteurs_espace = [
  // botanistes
  'A.de Vos', 'A.E.van Wyk', 'A.L.du Toit', 'Airy Shaw', 'Aké Assi', 'Baker f.', 'Burtt Davy',
  'C.I Peng', 'Dalla Torre', 'de Bary', 'De la Soie', 'de Lannoy', 'de Laub.', 'De Man', 'De Moor',
  'de Noé', 'De Not.', 'De Puydt', 'De Seynes', 'De Toni', 'De Vis', 'de Vries', 'de Vriese',
  'De Wild.', 'de Wit', 'Delle Chiaje', 'Des Moul.', 'Di Negro', 'Ding Hou', 'Douglass M.Hend.',
  'Du Rietz', 'Du Roi', 'Dy Phon', 'E. Olivier', 'E.\'t Hart', 'El Azzouni', 'Fay W.Li',
  'Font Quer', 'Fraser f.', 'Garcia de Orta', 'Gideon F.Sm.', 'Haller f.', 'Hallier f.',
  'Ibn Tattou', 'J.Garden bis', 'J.J.de Wilde', 'J.Kickx f.', 'J.White Dubl.', 'J.White R.N.',
  'Jacot Guill.', 'Jean F.Brunel', 'Jeff W.Grimes', 'John Parkinson', 'Joy Thomps.', 'Keng f.',
  'Ker Gawl.', 'Kerstin Koch', 'La Llave', 'Le Cointe', 'Le Gall', 'Le Houér.', 'Le Jol.',
  'Le Maout', 'Le Monn.', 'Le Prévost', 'Le Turq.', 'M.P.de Vos', 'Maas Geest.', 'Meijer Drees',
  'Muñoz Garm.', 'Paul G.Wilson', 'Ralf Bauer', 'Rivas Mart.', 'Ronse Decr.', 'S.van der Westh.',
  'S.Yun Liang', 'San Felice', 'Sande Lac.', 'Scott Elliot', 'Silva Manso', 'Soto Arenas',
  'Thiéry Mén.', 'Van Geert', 'Van Heurck', 'Van Houtte', 'van Jaarsv.', 'Van Sterbeeck',
  'Vanden Berghen', 'W.J.de Wilde', 'W.Saunders bis', 'Weber Bosse', 'Yan Liu', '\'t Hart', '\'t Mannetje',
   // zoologistes
   'Ala Ponzone', 'Arnault de Nobleville', 'Bellier de la Chavignerie', 'Bolivar y Urrutia',
   'Bon de Saint Hilaire', 'Bory de Saint-Vincent', 'Bosc d’Antic', 'Boyer de Fonscolombe',
   'Bruand d\'Uzelle', 'Buen y Lozano', 'Bus de Gisignies', 'Chiaje ou Delle Chiaje', 'Cooke Jr',
   'F. Cuvier', 'G. Cuvier', 'da Costa', 'Dalla Torre', 'de Beauvais', 'de Beer', 'De Betta', 'de Buen',
   'De Filippi', 'de Folin', 'de Geer', 'de Gregorio', 'De Kay', 'De la Riva', 'Della Torre',
   'Delle Chiaje', 'De Man', 'Denys de Montfort', 'De Prunner', 'de Sélys Longchamps', 'de Selys-Longchamps',
   'De Vis', 'de Winton', 'de Witte', 'Drummond ou Drummond-Hay', 'Du Chaillu', 'Duchassaing de Fonbressin',
   'Ducrotay de Blainville', 'Fischer von Waldheim', 'Forsyth Major', 'Gadeau de Kerville',
   'Geoffroy Saint-Hilaire', 'É. Geoffroy', 'I. Geoffroy', 'Girard de Villars', 'Heim de Balsac',
   'Jiménez de la Espada', 'Kunckel d\'Herculais', 'La Blanchère', 'La Marca', 'Laporte de Castelnau',
   'Le Conte', 'Le Danois', 'Lefebvre de Cérisy', 'Le Guyader', 'Le Loeuff', 'Le Maout', 'Le Masson Le Golft',
   'Le Moult', 'Leschenault de la Tour', 'Lort Phillips', 'Mac Lachlan', 'Maloteau de Guerne',
   'Martín del Campo y Sánchez', 'Meyer de Schauensee', 'von Meyer', 'Michelin de Choisy',
   'Millin de Grandmaison', 'Moreau de Jonnès', 'Mouton-Fontenille de La Clotte', 'Mutis y Bosio',
   'Nees von Esenbeck', 'Nicéforo María', 'Palisot de Beauvois', 'Phelps, Jr', 'Picot de Lapeyrouse',
   'Pictet de la Rive', 'Poda von Neuhaus', 'Rang des Adrets', 'Reid Henry', 'Rodríguez de la Fuente',
   'Rosén von Rosenstein', 'Rudbeck le Jeune', 'Saint Girons', 'Seoane y Pardo-Montenegro',
   'Sessé y Lacasta', 'Smith-Stanley Edward', 'Sonnini de Manoncourt', 'Targioni Tozzetti',
   'Tilesius von Tilenau', 'Valmont de Bomare', 'Van Beneden', 'Van Cleave', 'Van Denburgh',
   'Van Der Linden', 'Van Duzee', 'Van Eecke', 'Van Hasselt', 'Van Niel', 'Van Roosmalen',
   'Van Straelen', 'von Helversen', 'von Martens', 'Vo Quy', 'Weber-van Bosse',
   // spécial : pour gérer "et al."
   'et al.',
];


// fonction de préparation (éventuelle) de données pour les auteurs (chargement de fichier, etc.), pour
// éviter de le faire à chaque auteur. N'est appelée qu'une seule fois. Reçoit $struct pour éventuellement
// initialiser/compléter spécifiquement les données
// 'contraintes' est soit NULL (aucune contrainte) soit une table de 'rang'=>'nom-rang'
$aut_data = [
  [ 'noms' => [ 'L.' ],
    'cible' => 'Carl von Linné',
    'depart' => 1720, 'fin' => 1778,
    'contraintes' => null, ],
  [ 'noms' => [ 'Linné' ],
    'cible' => 'Carl von Linné',
    'depart' => 1720, 'fin' => 1778,
    'contraintes' => null, ],
  [ 'noms' => [ 'Linnæus' ],
    'cible' => 'Carl von Linné',
    'depart' => 1720, 'fin' => 1778,
    'contraintes' => null, ],
  [ 'noms' => [ 'Linnaeus' ],
    'cible' => 'Carl von Linné',
    'depart' => 1720, 'fin' => 1778,
    'contraintes' => null, ],
];
function auteurs_resoudre_init($struct) {
  return;
}

// reçoit un nom d'auteur (depuis la zone auteurs) + éventuellement une date ainsi que la structure terminée
// et retourne FALSE si rien trouvé ou une forme wikifiée (wikilien, {{lien}}, etc.)
function auteurs_resoudre($cur, $date, $struct) {
  global $aut_data;

  foreach($aut_data as $aut) {
    foreach($aut['noms'] as $n) {
      if ($n == $cur) {
      
      }
    }
  }

  return false;
}

// retourne une nouvelle version de la chaîne d'auteurs passée en paramètre
function auteurs_traite(&$struct, $auteurs) {
  global $auteurs_ignore, $auteurs_espace;

  if (get_config("auteurs") != 's') {
    return new_auteurs_traite($struct, $auteurs);
  }

  // cas particulier : vide
  if (trim($auteurs) == "") {
    return ""; // ne pas tenter de mettre des {{auteur}} et ne pas ajouter la date à préciser
  }

  // initialisation de la fonction de résolution des auteurs
  auteurs_resoudre_init($struct);

  // on tente de remplacer chaque auteur de la liste par une version "protégée"
  foreach($auteurs_espace as $a) {
    $dest = str_replace(" ", "@", $a);
    $auteurs = str_replace($a, $dest, $auteurs);
  }

  // on explode par espaces
  $tmp = explode(" ", $auteurs);
  $out = [];
  
  // premier passage pour trouver une date éventuellement
  $date = false;
  foreach($tmp as $t) {
    if (preg_match("/([123][0-9][0-9][0-9])/", $t) == 1) {
      $date = $t;
      break; // trouvé, on ne prend que la première
    }
  }

  // parcours de chaque élément
  foreach($tmp as $t) {
    // termes à ignorer
    if (in_array($t, $auteurs_ignore)) {
      $out[] = $t;
      continue;
    }
    // termes à traiter spécialement
    if ($t == 'et@al.') {
      $out[] = '{{et@al.}}';
      continue;
    }
    if ($t == 'et@al.,') {
      $out[] = '{{et@al.}},';
      continue;
    }
    // si c'est une date on n'y touche pas
    if (preg_match("/([123][0-9][0-9][0-9])/", $t) == 1) {
      $out[] = $t;
      continue;
    }
    // extraction
    $cur = $t;
    $pre = "";
    $post = "";
    // on récupère s'il y a un "caractère à la con" au début
    $x = mb_substr($cur, 0, 1);
    if (($x == '[') or ($x == '(')) {
      $pre .= $x;
      $cur = mb_substr($cur, 1);
    }
    // idem à la fin
    $x = mb_substr($cur, -1);
    if (($x == ']') or ($x == ")") or ($x == ",")) {
      $post .= $x;
      $cur = mb_substr($cur, 0, mb_strlen($cur)-1);
    }
    
    // on appelle la fonction de traitement spécifique, qui peut décider de remplacer
    // un auteur par sa cible si connue
    $valid = auteurs_resoudre($cur, $date, $struct);
    if ($valid) {
      // trouvé, on l'insert (la fonction doit retourner une forme complète, wikilien par ex.)
      $out[] = "$pre$valid$post";
      continue;
    }
    
    
    // on ajoute le modèle auteur
    $cur = "{{auteur|[[" . $cur . "]]}}";
    
    // on l'insert
    $out[] = "$pre$cur$post";
  }
  
  // reconstruction de la sortie
  $auteurs = implode(" ", $out);

  // on enlève les éventuels @
  $auteurs = str_replace("@", " ", $auteurs);

  // on remplace la (ou les) date par un lien
  $auteurs = preg_replace("/([123][0-9][0-9][0-9])/", '[[$1 en science|$1]]', $auteurs);

  // si pas de date on ajoute {{date à préciser}}
  if (preg_match("/([123][0-9][0-9][0-9])/", $t) != 1) {
    $auteurs .= " {{date à préciser}}";
  }

  return $auteurs;
}

