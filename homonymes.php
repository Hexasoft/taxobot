<?php

$homonymes = [
  'Cancer' => [ '*' => 'Cancer (crustacé)' ],
  'Pilumnus' => [ '*' => 'Pilumnus (crabe)' ],
];

/*
 * Cherche un homonyme à un terme. Prend un terme en paramètre et retourne
 * FALSE si aucun homonyme n'est trouvé, sinon il retourne le terme précisé
 * pour la biologie (exemple : "Cancer" → "Cancer (crustacé)")
 * La charte permet de choisir s'il y a des homonymes entre plusieurs domaines
 */
function cherche_homonyme($terme, $charte) {
  global $homonymes;
  
  if (!isset($homonymes[$terme])) {
    return false;
  }
  foreach($homonymes[$terme] as $cible => $el) {
    if ($cible == '*') {
      return $el; // c'est un générique
    }
    if ($cible == $charte) {
      return $el; // on trouve la bonne charte
    }
  }
  return false; // sinon non
}

