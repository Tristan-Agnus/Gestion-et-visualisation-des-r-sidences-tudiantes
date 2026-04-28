<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche Étudiant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Recherche Domicilomètre</h1>
    <p>Remplissez <strong>au moins un critère</strong> pour chercher un étudiant ou un groupe.</p>

    <form id="form-recherche" action="resultat.php" method="POST">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" placeholder="Ex: SOFTIC">

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" placeholder="Ex: Faris">

        <label for="groupe">Groupe (numéro) :</label>
        <input type="number" id="groupe" name="groupe" placeholder="Ex: 6">

        <button type="submit">Chercher</button>
    </form>
    
    <div id="message-erreur">Veuillez remplir au moins un champ !</div>
</div>

<script>
document.getElementById('form-recherche').addEventListener('submit', function(event) {
    let nom = document.getElementById('nom').value.trim();              
    let prenom = document.getElementById('prenom').value.trim();
    let groupe = document.getElementById('groupe').value.trim();
    
    // Si TOUS les champs sont vides, on bloque l'envoi
    if (nom === "" && prenom === "" && groupe === "") {
        event.preventDefault(); 
        document.getElementById('message-erreur').style.display = "block"; 
    } else {
        document.getElementById('message-erreur').style.display = "none";
    }
});
</script>

</body>
</html>
