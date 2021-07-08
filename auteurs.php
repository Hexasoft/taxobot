<?php

/*
  Traitements de la zone « auteurs » (et données associées)
*/

// mots à ignorer (même si certains sont généralement collés)
$auteurs_ignore = [ "ex.", "ex", "&", "[", "]", ",", "(", ")" ];

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
];

// retourne une nouvelle version de la chaîne d'auteurs passée en paramètre
function auteurs_traite($auteurs) {
  global $auteurs_ignore, $auteurs_espace;

  // cas particulier : vide
  if (trim($auteurs) == "") {
    return ""; // ne pas tenter de mettre des {{auteur}} et ne pas ajouter la date à préciser
  }

  // on tente de remplacer chaque auteur de la liste par une version "protégée"
  foreach($auteurs_espace as $a) {
    $dest = str_replace(" ", "@", $a);
    $auteurs = str_replace($a, $dest, $auteurs);
  }

  // on explode par espaces
  $tmp = explode(" ", $auteurs);
  $out = [];
  foreach($tmp as $t) {
    // termes à ignorer
    if (in_array($t, $auteurs_ignore)) {
      $out[] = $t;
      continue;
    }
    // si c'est une date on n'y touche pas
    if (preg_match("/([123][0-9][0-9][0-9])/", $t) == 1) {
      $out[] = $t;
      continue;
    }
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
  $auteurs = preg_replace("/(1[0-9][0-9][0-9])/", '[[$1 en science|$1]]', $auteurs);

  // si pas de date on ajoute {{date à préciser}}
  if (preg_match("/(1[0-9][0-9][0-9])/", $t) != 1) {
    $auteurs .= " {{date à préciser}}";
  }

  return $auteurs;
}

