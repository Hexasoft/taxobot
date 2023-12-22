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
  'Aa' => ['*' => 'Aa (genre)' ],
  'Abra' => ['*' => 'Abra (mollusque)' ],
  'Abraxas' => ['*' => 'Abraxas (papillon)' ],
  'Abronia' => ['*' => 'Abronia (botanique)' ],
  'Abronia' => ['*' => 'Abronia (zoologie)' ],
  'Albula' => ['*' => 'Albula (genre)',  ],
  'Acacia' => ['hom' => 'Acacia' ],
  'Acanthella' => ['*' => 'Acanthella (Dictyonellidae)' ],
  'Acanthella' => ['hom' => 'Acanthella', 'animal' => 'Acanthella (Dictyonellidae)', 'végétal' => 'Acanthella (Melastomataceae)' ],
  'Acanthis' => ['*' => 'Acanthis (genre)' ],
  'Acca' => ['hom' => 'Aca', 'animal' => 'Acca (papillon)', 'végétal' => 'Acca (plante)' ],
  'Acolhua' => ['*' => 'Acolhua (insecte)' ],
  'Acridinae' => ['hom' => 'Acridinae', 'Amphibia' => 'Acridinae (amphibien)', 'Insecta' => 'Acridinae (insecte)' ],
  'Acridinae' => ['*' => 'Acridinae (insecte)' ],
  'Actinote' => ['*' => 'Actinote (genre)' ],
  'Acétabulaire' => ['*' => 'Acétabulaire (biologie)' ],
  'Ada' => ['hom' => 'Ada', 'animal' => 'Ada (oiseau)', 'végétal' => 'Ada (orchidée)' ],
  'Adesmia' => ['animal' => 'Adesmia (insecte)', 'végétal' => 'Adesmia (plante)' ],
  'Adia' => ['*' => 'Adia (diptère)' ],
  'Adonis' => ['*' => 'Adonis (genre)' ],
  'Aetius' => ['*' => 'Aetius (genre)' ],
  'Agama' => ['*' => 'Agama (genre)' ],
  'Agarista' => ['animal' => 'Agarista (genre animal)', 'végétal' => 'Agarista (genre végétal)' ],
  'Agathon' => ['*' => 'Agathon (diptère)' ],
  'Agdistis' => ['*' => 'Agdistis (insecte)' ],
  'Aglaia' => ['*' => 'Aglaia (plante)' ],
  'Agnosia' => ['*' => 'Agnosia (genre)' ],
  'Agrius' => ['*' => 'Agrius (insecte)' ],
  'Aigle noir' => ['*' => 'Aigle noir (oiseau)' ],
  'Aix' => ['*' => 'Aix (oiseau)' ],
  'Akko' => ['*' => 'Akko (poisson)' ],
  'Alafia' => ['*' => 'Alafia (botanique)' ],
  'Alaria' => ['*' => 'Alaria (algue)' ],
  'Alaria' => ['*' => 'Alaria (animal)' ],
  'Alcippe' => ['*' => 'Alcippe (genre)' ],
  'Alectis' => ['*' => 'Alectis (genre)' ],
  'Alexa' => ['*' => 'Alexa (plante)' ],
  'Alfaro' => ['*' => 'Alfaro (genre)' ],
  'Alisea' => ['*' => 'Alisea (champignon)' ],
  'Alisea' => ['*' => 'Alisea (poisson)' ],
  'Allende' => ['*' => 'Allende (genre)' ],
  'Alpestre' => ['*' => 'Alpestre (papillon)' ],
  'Alpine' => ['*' => 'Alpine (papillon)' ],
  'Alsophila' => ['*' => 'Alsophila (papillon)' ],
  'Alsophila' => ['*' => 'Alsophila (plante)' ],
  'Althaea' => ['*' => 'Althaea (genre)' ],
  'Amara' => ['*' => 'Amara (insecte)' ],
  'Amarante' => ['*' => 'Amarante (plante)' ],
  'Amata' => ['*' => 'Amata (papillon)' ],
  'Amazone' => ['*' => 'Amazone (oiseau)' ],
  'Ambroisie' => ['*' => 'Ambroisie (genre)' ],
  'Amhara' => ['*' => 'Amhara (genre)' ],
  'Ammophila' => ['*' => 'Ammophila (insecte)' ],
  'Ammophila' => ['*' => 'Ammophila (plante)' ],
  'Ampyx' => ['*' => 'Ampyx (trilobite)' ],
  'Amycus' => ['*' => 'Amycus (genre)' ],
  'Anadia' => ['*' => 'Anadia (lézard)' ],
  'Anax' => ['*' => 'Anax (libellule)' ],
  'Andalouse' => ['*' => 'Andalouse (papillon)' ],
  'Andersonia' => ['*' => 'Andersonia (Ericaceae)' ],
  'Andromeda' => ['*' => 'Andromeda (genre)' ],
  'Angela' => ['*' => 'Angela (insecte)' ],
  'Angerona' => ['*' => 'Angerona (papillon)' ],
  'Angostura' => ['*' => 'Angostura (plante)' ],
  'Aniba' => ['*' => 'Aniba (genre)' ],
  'Anicius' => ['*' => 'Anicius (genre)' ],
  'Anisognathus' => ['*' => 'Anisognathus (Aves)' ],
  'Annamia' => ['*' => 'Annamia (poisson)' ],
  'Anthiinae' => ['*' => 'Anthiinae (Carabidae)' ],
  'Anthodon' => ['*' => 'Anthodon (reptile)' ],
  'Anthrax' => ['*' => 'Anthrax (diptère)' ],
  'Antonietta' => ['*' => 'Antonietta (gastropode)' ],
  'Aotus' => ['*' => 'Aotus (plante)' ],
  'Apion' => ['*' => 'Apion (genre)' ],
  'Apoda' => ['*' => 'Apoda (papillon)' ],
  'Appendicularia' => ['*' => 'Appendicularia (Melastomataceae)' ],
  'Appias' => ['*' => 'Appias (genre)' ],
  'Aquarius' => ['*' => 'Aquarius (hémiptère)' ],
  'Aquila' => ['*' => 'Aquila (genre)' ],
  'Ara' => ['*' => 'Ara (genre)' ],
  'Arca' => ['*' => 'Arca (mollusque)' ],
  'Arcadia' => ['*' => 'Arcadia (amphibien)' ],
  'Archeria' => ['*' => 'Archeria (amphibien)' ],
  'Archeria' => ['*' => 'Archeria (plante)' ],
  'Archon' => ['*' => 'Archon (genre)' ],
  'Arduina' => ['*' => 'Arduina (genre)' ],
  'Arenaria' => ['*' => 'Arenaria (oiseau)' ],
  'Arenaria' => ['*' => 'Arenaria (plante)' ],
  'Argiope' => ['*' => 'Argiope (genre)' ],
  'Argynnina' => ['*' => 'Argynnina (genre)' ],
  'Arion' => ['*' => 'Arion (mollusque)' ],
  'Aristide' => ['*' => 'Aristide (plante)' ],
  'Ariston' => ['*' => 'Ariston (genre)' ],
  'Arius' => ['*' => 'Arius (poisson)' ],
  'Arma' => ['*' => 'Arma (hémiptère)' ],
  'Armadillo' => ['*' => 'Armadillo (genre)' ],
  'Arses' => ['*' => 'Arses (genre)' ],
  'Ascalaphe' => ['*' => 'Ascalaphe (insecte)' ],
  'Ascaris' => ['*' => 'Ascaris (genre)' ],
  'Ascaris' => ['*' => 'Ascaris (parasite)' ],
  'Asellus' => ['*' => 'Asellus (genre)' ],
  'Asio' => ['*' => 'Asio (genre)' ],
  'Aspe' => ['*' => 'Aspe (poisson)' ],
  'Aster' => ['*' => 'Aster (genre)' ],
  'Asterope' => ['*' => 'Asterope (genre)' ],
  'Astragale' => ['*' => 'Astragale (flore)' ],
  'Astrea' => ['*' => 'Astrea (genre)' ],
  'Astérope' => ['*' => 'Astérope (papillon)' ],
  'Atalaya' => ['*' => 'Atalaya (genre)' ],
  'Atys' => ['*' => 'Atys (mollusque)' ],
  'Augusta' => ['*' => 'Augusta (plante)' ],
  'Aurelia' => ['*' => 'Aurelia (méduse)' ],
  'Aureliana' => ['*' => 'Aureliana (plante)' ],
  'Auricularia' => ['*' => 'Auricularia (champignon)' ],
  'Aurivillius' => ['*' => 'Aurivillius (genre)' ],
  'Aurore' => ['*' => 'Aurore (papillon)' ],
  'Australodiscus' => ['*' => 'Australodiscus (algue)' ],
  'Avitus' => ['*' => 'Avitus (genre)' ],
  'Axiothea' => ['*' => 'Axiothea (conodonte)' ],
  'Axis' => ['*' => 'Axis (cerf)' ],
  'Azara' => ['*' => 'Azara (genre)' ],
  'Azilia' => ['*' => 'Azilia (araignée)' ],
  'Azurite' => ['*' => 'Azurite (flore)' ],

  // B

  
  // C
  'Cancer' => [ '*' => 'Cancer (crustacé)' ],
  'Columba' => [ '*' => 'Columba (oiseau)' ],
  
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