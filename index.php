<?php

/*
 * Génération du formulaire de configuration et d'accès
 *   pour appeler le programme taxobot.php
 */

// les éléments nécessaires
require_once "outils.php";
require_once "configuration.php";
require_once "modules.php";
require_once "wikipedia.php";
require_once "rendu.php";

// on initialise la page
html_head("Taxobot - v" . $version);

// message de début
echo <<<EOL
<p>Remarques :</p>
<ul>
<li>Le mode « Ne traiter que les liens externes » peut présenter des erreurs de mise en italiques</li>
<li>Si le taxon existe sous le même nom dans plusieurs règnes (cas de quelques genres entre zoologie/botanique)
le comportement peut être curieux</li>
</ul>
<hr>
<p>Sélectionnez une classification (liste déroulante) et indiquez un taxon (nom scientifique uniquement) puis validez. Vous pouvez également sélectionner (optionnellement) un domaine. ATTENTION : changer la classification ou le domaine peut conduire à des situations incompatibles.</p>
EOL;

// il faut charger les modules, pour connaître les classifications
init_outils();
// on récupère les modules, et on les initialise
$modules = cherche_modules();
if (($modules === false) or (empty($modules))) {
  echo "<p><b>Erreur : impossible de trouver les modules.</b></p>\n";
  fini_outils();
  html_end();
  die(1);
}
// liste des noms associés
$id_modules = noms_vers_identifiants($modules);

// on charge tous les modules
foreach($modules as $m) {
  require_once "./modules/$m";
}

// on initialise tous les modules
foreach($id_modules as $id) {
  $f = "m_" . $id . "_init";
  $ret = $f();
  if ($ret == false) {
    echo "<p><b>Erreur : impossible de trouver les modules.</b></p>\n";
    fini_outils();
    html_end();
    die(1);
  }
}
// modules gérant de la classification
$liste = classif_modules();
// classification par défaut
$cdef = meilleure_classification("*");

// on prépare la FORM
echo "<form action='taxobot.php' method='GET'>\n";

// table de mise en forme
echo "<table>\n";
echo "<tr><td width='33%'>\n";
echo "Classification : <select name='classification'>\n";

foreach($liste as $nc) {
  if ($nc == $cdef) {
    echo "<option value='$nc' selected>$nc</option>\n";
  } else {
    echo "<option value='$nc'>$nc</option>\n";
  }
}

echo "</select>\n";

// le domaine
echo <<<EOL
<br/><br/>
Domaine : <select name='domaine'>
EOL;
echo "<option value='*'>Tous</option>\n";
echo "<option value='animal'>Animal</option>\n";
echo "<option value='végétal'>Végétal</option>\n";
echo "<option value='champignon'>Champignon</option>\n";
echo "<option value='bactérie'>Bactérie</option>\n";
echo "<option value='algue'>Algue</option>\n";
echo "<option value='protiste'>Protiste</option>\n";
echo "</select>\n";

// le nom du taxon
echo <<<EOL
<br/><br/>
Taxon : <input type="text" name="taxon" required>
EOL;

echo "</td><td>\n";

// les options
echo <<<EOL
<input type="number" name="seuil-colonnes" min="-2" max="50" value="-2"> Nb d'entrées (taxons inf., syn.) avant mise en colonnes. -2=[25], -1=jamais, 0=toujours, sinon nombre<br/>
<input type="checkbox" name="liens-synonymes" value="oui" checked> Mettre un lien aux synonymes<br/>
<input type="checkbox" name="liens-inf-sp" value="oui"> Mettre un lien aux synonymes inf. à l'espèce<br/>
<input type="checkbox" name="suivre-synonymes" value="oui" checked>Si le taxon est un synonyme traiter le nom valide<br/>
<input type="checkbox" name="juste-ext" value="oui">Ne traiter que les liens externes<br/>
<input type="checkbox" name="inclure-invalides" value="oui">Inclure les biorefs additionnels<br/>
EOL;
echo "</td>\n</tr></table>\n";

// on termine la FORM (+ submit)
echo <<<EOL
<br/><br/>
<input type='submit' value='Lancer la recherche'>
<br/>

</form>
EOL;

// bas de page
echo "<br/><hr>\n";
echo "<p>Remarque : la classification ITIS est encore expérimentale (et beaucoup plus lente).</p>\n";

// terminaison de la page
html_end();