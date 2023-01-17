<?php

/*
  fonctions permettant de traiter des conditions sur le contenu de $struct
  et retournant des valeurs.
  permet de créer des séries de conditions pour spécifier certains éléments
  (portails, catégories, ébauche, auteurs)
  
  Format d'une valeur :
    regne / classification / rang / rangs.{espèce|genre|famille|…}
  
  Une ligne d'expression est un code PHP valide. Il peut utiliser if, les opérateurs
  de comparaison, les parenthèses.
  Il *doit* retourner la valeur voulue ou FALSE.
  
  Les valeurs (cf plus haut) doivent être encadrées par le caractère "/".
  
  Exemple, qui test si l'Embranchement=Cnidaria pour les ébauches
  if (/rangs.embranchement/ == 'Cnidaria') { return 'cnidaire'; } else { return FALSE; }
  
  Toutes les entrées d'un fichier sont évaluées. Dès qu'il y a une réponse l'évaluation s'arrête

*/

// remplace les entrées de valeurs dans le texte indiqué
function sel_remplace($struct, $txt) {
  global $rangs;

  $val = $struct['regne'];
  $txt = str_replace("/regne/", "'$val'", $txt);
  $val = $struct['classification'];
  $txt = str_replace("/classification/", "'$val'", $txt);
  $val = $struct['taxon']['rang'];
  $txt = str_replace("/rang/", "'$val'", $txt);
  foreach($rangs as $idx => $r) {
    foreach($struct['rangs'] as $rn) {
      if ($rn['rang'] == $idx) {
        $val = $rn['nom'];
        $txt = str_replace("/rangs.$idx/", "'$val'", $txt);
        break;
      }
    }
  }
  // maintenant on remplace tout ce qui reste entre "/" par un texte impossible
  // pour éviter des messages d'erreur si un élément n'existe pas
  $txt = preg_replace(",/[^|]*/,", "'TexteNonPossible'", $txt);
  
  return $txt;
}

// charge et évalue un fichier de sélections à partir de la structure courante
function sel_evalue($fichier, $struct) {
  if (!file_exists($fichier)) {
    return false;
  }
  $ev = file_get_contents($fichier);
  if ($ev === false) {
    logs("Selecteurs: impossible de lire le fichier '$fichier'");
    return false;
  }
  $tbl = explode("\n", $ev);
  $cnt = 0;
  foreach($tbl as $ligne) {
    $cnt++;
    // on passe les lignes vides et les commentaires
    $l = trim($ligne);
    if ($l == "") { continue; }
    if ($l[0] == "#") { continue; }
    
    $ret = sel_remplace($struct, $l);
    try {
      $res = eval($ret);
    } catch (ParseError $e) {
      logs("Selecteurs: erreur dans le fichier '$fichier' à la ligne $cnt. Ignoré");
      $res = false;
    }
    // si on a un résultat on le retourne (on quitte)
    if (($res !== null) and ($res !== false)) {
      return $res;
    }
  }
  // rien trouvé
  return false;
}

