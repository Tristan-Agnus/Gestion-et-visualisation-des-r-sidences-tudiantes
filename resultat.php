<?php
// --- ÉTAPE 1 : PRÉPARATION ET CONNEXION ---

// On inclut le fichier db.php qui contient la connexion à la base de données ($pdo)
require 'db.php'; 

// On récupère les données envoyées par le formulaire via la méthode POST.
// L'opérateur ?? '' signifie : "si la donnée n'existe pas, on met une chaîne vide".
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$groupe = $_POST['groupe'] ?? '';

// --- ÉTAPE 2 : LA REQUÊTE SQL ---

// On prépare la requête SQL pour chercher les étudiants.
// JOIN : On lie la table Etudiant à la table Lieu pour la résidence principale (rp).
// LEFT JOIN : On lie aussi pour la résidence secondaire (rs), mais on garde l'étudiant même s'il n'en a pas.
$sql = "SELECT e.nom, e.prenom, e.id_groupe, 
               rp.nom AS ville_rp, 
               rs.nom AS ville_rs 
        FROM Etudiant e
        JOIN Lieu rp ON e.id_lieu_rp = rp.id_lieu
        LEFT JOIN Lieu rs ON e.id_lieu_rs = rs.id_lieu
        WHERE e.nom = ? OR e.prenom = ? OR e.id_groupe = ?";

// On prépare la requête pour éviter les injections SQL (sécurité)
$stmt = $pdo->prepare($sql);
// On injecte les variables de l'utilisateur dans les "?" de la requête
$stmt->execute([$nom, $prenom, $groupe]);
// On récupère tous les résultats sous forme de tableau associatif
$etudiants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat - Domicilomètre</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Résultat de la recherche</h2>

    <?php 
    // On vérifie si la base de données a renvoyé au moins un résultat
    if (count($etudiants) > 0): 
    ?>
        
        <?php 
        // On commence une boucle pour afficher chaque étudiant trouvé un par un
        foreach ($etudiants as $etu): 
        ?>
            <div class="result-box">
                <h3 style="color: #0056b3;"><?= $etu['prenom'] ?> <?= $etu['nom'] ?> (Groupe <?= $etu['id_groupe'] ?>)</h3>

                <h4>🏠 Résidence Principale : <?= $etu['ville_rp'] ?></h4>
                
                <?php
                // 1. On prépare le nom de la ville pour la recherche (minuscules et sans tirets)
                $ville_recherchee = strtolower(str_replace('-', ' ', $etu['ville_rp']));
                
                // 2. On initialise les variables de population et de contrôle
                $p99 = 0; $p10 = 0; $p12 = 0;
                $trouve = false;

                // 3. On ouvre le fichier CSV en mode lecture ('r')
                $fichier = fopen('ville.csv', 'r');
                
                // 4. On lit le fichier ligne par ligne
                while (($ligne = fgetcsv($fichier, 1000, ',')) !== FALSE) {
                    // On nettoie le nom de la ville lu dans la colonne n°1 du CSV
                    $ville_csv = strtolower(str_replace('-', ' ', $ligne[1]));
                    
                    // Si le nom correspond à la ville de l'étudiant
                    if ($ville_csv == $ville_recherchee) {
                        $p10 = (int)$ligne[3]; // Population 2010 (colonne 4)
                        $p99 = (int)$ligne[4]; // Population 1999 (colonne 5)
                        $p12 = (int)$ligne[5]; // Population 2012 (colonne 6)
                        $trouve = true;
                        break; // On a trouvé la ville, on sort de la boucle "while"
                    }
                }
                fclose($fichier); // On ferme le fichier pour libérer de la mémoire

                // 5. Calcul de l'évolution si la ville a été trouvée dans le CSV
                if ($trouve == true) {
                    $max = max($p99, $p10, $p12); // Le chiffre le plus haut
                    $min = min($p99, $p10, $p12); // Le chiffre le plus bas
                    $evolution = "Plane"; // Valeur par défaut
                    
                    // ALGORITHME POUR LES TENDANCES
                    if (($max - $min) <= 300) { 
                        $evolution = "Plane"; // Écart très faible
                    } elseif ($p10 > $p99 && $p12 > $p10) { 
                        $evolution = "Haussière"; // Ça ne fait que monter
                    } elseif ($p10 < $p99 && $p12 < $p10) { 
                        $evolution = "Baissière"; // Ça ne fait que descendre
                    } elseif ($p10 < $p99 && $p12 > $p10) { 
                        $evolution = "Cuvette"; // Baisse puis remonte
                    } elseif ($p10 > $p99 && $p12 < $p10) { 
                        $evolution = "Monticule"; // Monte puis redescend
                    }
                    ?>
                    
                    <ul>
                        <li>Population 1999 : <?= $p99 ?></li>
                        <li>Population 2010 : <?= $p10 ?></li>
                        <li>Population 2012 : <?= $p12 ?></li>
                    </ul>
                    <p>Évolution : <strong><?= $evolution ?></strong></p>
                    
                <?php } else { ?>
                    <p style="color:red;">Ville introuvable dans le fichier CSV.</p>
                <?php } ?>


                <?php if ($etu['ville_rs'] != '' && $etu['ville_rs'] != 'No data'): ?>
                    <hr> <h4>🏡 Résidence Secondaire : <?= $etu['ville_rs'] ?></h4>
                    
                    <?php
                    // On répète le même processus que pour la ville principale
                    $ville_recherchee_rs = strtolower(str_replace('-', ' ', $etu['ville_rs']));
                    $p99_rs = 0; $p10_rs = 0; $p12_rs = 0;
                    $trouve_rs = false;

                    $fichier = fopen('ville.csv', 'r');
                    while (($ligne = fgetcsv($fichier, 1000, ',')) !== FALSE) {
                        $ville_csv = strtolower(str_replace('-', ' ', $ligne[1]));
                        if ($ville_csv == $ville_recherchee_rs) {
                            $p10_rs = (int)$ligne[3];
                            $p99_rs = (int)$ligne[4];
                            $p12_rs = (int)$ligne[5];
                            $trouve_rs = true;
                            break;
                        }
                    }
                    fclose($fichier);

                    if ($trouve_rs == true) {
                        $max = max($p99_rs, $p10_rs, $p12_rs);
                        $min = min($p99_rs, $p10_rs, $p12_rs);
                        $evolution_rs = "Plane";
                        
                        if (($max - $min) <= 300) { $evolution_rs = "Plane"; }
                        elseif ($p10_rs > $p99_rs && $p12_rs > $p10_rs) { $evolution_rs = "Haussière"; }
                        elseif ($p10_rs < $p99_rs && $p12_rs < $p10_rs) { $evolution_rs = "Baissière"; }
                        elseif ($p10_rs < $p99_rs && $p12_rs > $p10_rs) { $evolution_rs = "Cuvette"; }
                        elseif ($p10_rs > $p99_rs && $p12_rs < $p10_rs) { $evolution_rs = "Monticule"; }
                        ?>
                        
                        <ul>
                            <li>Population 1999 : <?= $p99_rs ?></li>
                            <li>Population 2010 : <?= $p10_rs ?></li>
                            <li>Population 2012 : <?= $p12_rs ?></li>
                        </ul>
                        <p>Évolution : <strong><?= $evolution_rs ?></strong></p>
                        
                    <?php } else { ?>
                        <p style="color:red;">Ville introuvable dans le fichier CSV.</p>
                    <?php } ?>
                <?php endif; ?>

            </div> <?php endforeach; // Fin de la boucle sur tous les étudiants ?>

    <?php else: ?>
        <p style="color: red; text-align: center;">Aucun étudiant trouvé.</p>
    <?php endif; ?>

    <a href="index.php" class="retour">← Nouvelle recherche</a>
</div>

</body>
</html>
