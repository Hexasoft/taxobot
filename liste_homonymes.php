<?php

/**
 * Table d'équivalence des homonymies classées par classifieur
 * 
 * Format :
 * <terme> => ['<classifieur>' => '<texte affiché>']
 * 
 * Significations :
 *   - <terme > : terme pour lequel on cherche un homonyme ;
 *   - <classifieur> : permet d'associer <terme> à <texte affiché> ;
 *   - <texte affiché> : texte par lequel <terme> est remplacé.
 * 
 * Classifieurs acceptés :
 *    - une charte (ex. 'animal', 'végétal', ...), cf. Modèle:Taxoboxoutils premier parent ;
 *    - '*' : un correspondance générique (ex. toujours remplacer 'Cancer' par 'Cancer (crustacé)') ;
 *    - 'hom' : une page d'homnymie ; indique que la correspondance doit être corrigée manuellement, cf. Modèle:Lien vers une page d'homonymie.
 * 
 * Obligatoire : '*' ou charte
 * Optionnel : 'hom'
 *    - 'hom' est recommandé avec une charte car il permet de catégoriser les pages comme étant à corriger si la charte n'est pas trouvée.
 *
 */

$homonymes = [
  // A
  'Abronia' => [ 'hom' => 'Abronia', 'animal' => 'Abronia (zoologie)', 'végétal' => 'Abronia (botanique)' ],

  // C
  'Cancer' => [ '*' => 'Cancer (crustacé)' ],

  // P
  'Pilumnus' => [ '*' => 'Pilumnus (crabe)' ],
  
];

/**
 * Recherche le texte à afficher pour un terme donné en utilisant un classifieur.
 *
 * @param string $terme - Le terme pour lequel on cherche un homonyme.
 * @param string $classifieur - Le sélecteur qui trie la recherche (souvent le règne ou la charte).
 *
 * @return array [$pageh, $el] - Retourne un tableau contenant deux éléments :
 *                      - Le premier correspond à $pageh. True si on renvoie une page d'homonymie.
 *                      - Le second correspond à $el : soit <texte affiché>, soit false si aucun terme n'est trouvé ou ne peut être renvoyé.
 *
 */

function cherche_homonyme($terme, $classifieur) {
  global $homonymes;

    $pageh = false; // Par défaut, on ne renvoie pas une page d'homonymie
    $el = false;    // Par défaut, on ne renvoie aucun texte
  
    if (!isset($homonymes[$terme])) { // Terme spécifié absent du dictionnaire
        return [$pageh, $el]; 
    }
    
    if (isset($homonymes[$terme][$classifieur])) { // Recherche sur un classifieur
      $el = $homonymes[$terme][$classifieur];
    } elseif ($classifieur != "*" && isset($homonymes[$terme]['*'])) { // Recherche générique (sauf si déjà effectuée)
      $el = $homonymes[$terme]['*'];
    } elseif (isset($homonymes[$terme]['hom'])) { // Regarde si on doit activer Modèle:Lien vers une page d'homonymie
      $pageh = true;
      $el = $homonymes[$terme]['hom'];
    } 
    return [$pageh, $el]; // Résultats de la recherche. Par défaut, aucun résultat.
  }



