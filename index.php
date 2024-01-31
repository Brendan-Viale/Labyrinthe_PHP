<?php
session_start();
// On crée tous les tableaux de jeu possibles dans une seule variable
// 0 : vide, 1 : mur, 2 : sourie, 3 : joueur, 4 : brouillard
$boards = [
    [
        [0, 0, 1, 0, 1, 0, 0],
        [0, 0, 1, 0, 0, 0, 1],
        [1, 0, 0, 0, 1, 1, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 2, 1, 0, 1, 1, 0]
    ], [
        [0, 0, 1, 0, 1, 0, 0],
        [0, 0, 1, 0, 0, 0, 1],
        [1, 0, 0, 0, 1, 1, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 0, 1, 0, 0, 0, 0],
        [0, 0, 1, 0, 0, 2, 0],
        [0, 0, 1, 0, 1, 1, 0]
    ]
];


// Si on clique sur le bouton "reset"
if (isset($_POST["reset"])) {
    // On détruit la variable
    session_destroy();
    // On refresh la page pour éviter tout conflit et rediriger l'utilisateur dans notre else (instanciation de nos variables en SESSION)
    header("refresh:0");
} elseif (isset($_POST["direction"])) {
    // On détruit la variable en SESSION qui contenait notre erreur parce que nous utilisons la condition isset() lors de l'affichage
    // On le fait à chaque début de déplacement afin de ne pas afficher l'erreur précédente
    unset($_SESSION["error"]);
    // On crée des alias à nos variables en SESSION par soucis de lisibilité uniquement
    $playerPos = $_SESSION["player"];
    $board = $_SESSION["board"];
    // Tous nos input de flèches ont pour name "direction", seule leur value diffère, on réalise donc un switch sur la value du bouton de direction cliqué
    switch ($_POST["direction"]) {
        case "top":
            // On vérifie qu'il ne sorte pas du terrain vers le haut
            if ($playerPos[0] > 0) {
                // Si la case au-dessus est vide (0 étant une case vide)
                if ($board[$playerPos[0] - 1][$playerPos[1]] === 0)
                    // On met à jour la position du joueur
                    $_SESSION["player"] = [$playerPos[0] - 1, $playerPos[1]];
                // S'il s'agit d'un mur
                elseif ($board[$playerPos[0] - 1][$playerPos[1]] === 1)
                    // On crée la variable en SESSION "error" afin de stocker notre message d'erreur
                    $_SESSION["error"] = "Vous ne pouvez pas vous déplacer dans un mur";
                // S'il s'agit de la sourie
                elseif ($board[$playerPos[0] - 1][$playerPos[1]] === 2)
                    // On crée la variable en SESSION "win" afin de confirmer qu'on a bien gagné
                    $_SESSION["win"] = "Bravo ! Bon appétit.";
            } 
            // Si la ligne du joueur est la 1ère et qu'il souhaite monter, on lui stocke l'erreur toujours dans notre SESSION "error"
            else $_SESSION["error"] = "Vous ne pouvez pas sortir du terrain";
            break;
        // Exactement la même logique pour tous les déplacements
        case "bottom":
            // On compare la ligne à la dernière ligne du tableau (donc taille du tableau - 1), s'il est inférieur il peut se déplacer vers le bas, sinon il sort du terrain
            if ($playerPos[0] < count($board) - 1) {
                if ($board[$playerPos[0] + 1][$playerPos[1]] === 0)
                // On met à jour la position du joueur
                    $_SESSION["player"] = [$playerPos[0] + 1, $playerPos[1]];
                elseif ($board[$playerPos[0] + 1][$playerPos[1]] === 1)
                    $_SESSION["error"] = "Vous ne pouvez pas vous déplacer dans un mur";
                elseif ($board[$playerPos[0] + 1][$playerPos[1]] === 2)
                    $_SESSION["win"] = "Bravo ! Bon appétit.";
            } else $_SESSION["error"] = "Vous ne pouvez pas sortir du terrain";
            break;
        case "left":
            if ($playerPos[1] > 0) {
                if ($board[$playerPos[0]][$playerPos[1] - 1] === 0)
                // On met à jour la position du joueur
                    $_SESSION["player"] = [$playerPos[0], $playerPos[1] - 1];
                elseif ($board[$playerPos[0]][$playerPos[1] - 1] === 1)
                    $_SESSION["error"] = "Vous ne pouvez pas vous déplacer dans un mur";
                elseif ($board[$playerPos[0]][$playerPos[1] - 1] === 2)
                    $_SESSION["win"] = "Bravo ! Bon appétit.";
            } else $_SESSION["error"] = "Vous ne pouvez pas sortir du terrain";
            break;
        case "right":
            // On compare la colonne du joueur à la taille de la LIGNE, attention à bien spécifier de la ligne, dans le cas où votre plateau de jeu est rectangulaire c'est obligatoire
            if ($playerPos[1] < count($board[$playerPos[0]]) - 1) {
                if ($board[$playerPos[0]][$playerPos[1] + 1] === 0)
                // On met à jour la position du joueur
                    $_SESSION["player"] = [$playerPos[0], $playerPos[1] + 1];
                elseif ($board[$playerPos[0]][$playerPos[1] + 1] === 1)
                    $_SESSION["error"] = "Vous ne pouvez pas vous déplacer dans un mur";
                elseif ($board[$playerPos[0]][$playerPos[1] + 1] === 2)
                    $_SESSION["win"] = "Bravo ! Bon appétit.";
            } else $_SESSION["error"] = "Vous ne pouvez pas sortir du terrain";
            break;
    }
} else {
    // Création de nos variables en SESSION au 1ère affichage et à chaque reset
    // [i, j]
    // Position de départ du joueur
    $_SESSION["player"] = [0, 0];

    for($i = 0 ; $i < rand(2,8) ; $i++){
        array_push($boards, generateBoard());
    }
    // Choix aléatoire du plateau de jeu, on choisit aléatoirement grâce à l'index du tableau dans $boards
    $_SESSION["board"] = $boards[rand(0, count($boards) - 1)];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labyrinthe of the dead</title>
    <link href="style.css" rel="stylesheet" />
</head>

<body>
    <section>
        <h1>Labyrinthe of the dead</h1>
        <div id="container">
            <div id="boardContainer">
                <?php
                foreach (fogMap($_SESSION["board"]) as $i => $line) {
                    echo "<div class='line'>";
                    foreach ($line as $j => $cell) {
                        // $_SESSION["player"] = [0,0]
                        if ($i === $_SESSION["player"][0] && $j === $_SESSION["player"][1])
                            echo "<div class='cell cell3'></div>";
                        else
                            echo "<div class='cell cell$cell'></div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
            <form method="post">
                <?php if(!isset($_SESSION["win"])) : ?>
                    <div>
                        <input id="top" type="submit" name="direction" value="top" />
                        <div id="horArrows">
                            <input id="left" type="submit" name="direction" value="left" />
                            <input id="right" type="submit" name="direction" value="right" />
                        </div>
                        <input id="bottom" type="submit" name="direction" value="bottom" />
                    </div>
                <?php endif; ?>
                <button id="reset" type="submit" name="reset">Reset</button>
            </form>
        </div>
        <p class=<?= isset($_SESSION["error"]) ? "'error'" : "'win'" ?> id="prompt">
            <?= isset($_SESSION["error"]) ? "/!\\ " . $_SESSION["error"] : '' ?>
            <?= isset($_SESSION["win"]) ? $_SESSION["win"] : '' ?>
        </p>
    </section>
</body>
</html>

<?php
    function fogMap($board){
        $playerPos = $_SESSION["player"];
        foreach($board as $i => $line){
            foreach($line as $j => $cell){
                if(!(($i === $playerPos[0] && $j === $playerPos[1])
                    || ($i === $playerPos[0] + 1 && $j === $playerPos[1])
                    || ($i === $playerPos[0] - 1 && $j === $playerPos[1])
                    || ($i === $playerPos[0] && $j === $playerPos[1] + 1)
                    || ($i === $playerPos[0] && $j === $playerPos[1] -1)))
                    $board[$i][$j] = 4;
            }
        }
        return $board;
    }

    function generateBoard(){
        $lines = rand(3, 10);
        $columns = rand (3, 10);
        $mouse = [rand (2, $lines -1), rand (2, $columns -1)];
        $board = [];
        for($i=0 ; $i<$lines ; $i++){
            array_push($board, []);
            for($j=0 ; $j<$columns ; $j++){
                if($i === $mouse[0] && $j === $mouse[1])
                    array_push($board[$i], 2);
                else
                    array_push($board[$i], 1);
            }
        }
        return makePath($board, $_SESSION["player"]);
    }

    function makePath($board, $player){
        $board[$player[0]][$player[1]] = 0;
        // $player = [0,0]
        if($player[0] > 0){
            if($board[$player[0] - 1][$player[1]] === 2)
                return $board;
        }
        if($player[0] < count($board) - 1){
            if($board[$player[0] + 1][$player[1]] === 2)
                return $board;
        }
        if($player[1] > 0){
            if($board[$player[0]][$player[1] - 1] === 2)
                return $board;
        }
        if($player[1] < count($board[$player[0]]) - 1){
            if($board[$player[0]][$player[1] + 1] === 2)
                return $board;
        }

        $move = ["top", "right", "bottom", "left"];
        $randomMove = rand(0,3);
        switch($move[$randomMove]){
            case "top" :
                $player[0] = max(0, $player[0]-1);
                break;
            case "bottom" :
                $player[0] = min(count($board) - 1, $player[0]+1);
                break;
            case "left" :
                $player[1] = max(0, $player[1]-1);
                break;
            case "right" :
                $player[1] = min(count($board[$player[0]]) - 1, $player[1]+1);
                break;
        }
        
        return makePath($board, $player);
    }
?>