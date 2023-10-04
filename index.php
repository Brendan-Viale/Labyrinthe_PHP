<?php
    session_start();
    // 0 = vide, 1 = mur, 2 = joueur, 3 = arrivée, 4 = brouillard
    $boards=[
        [
            [2,0,0,1,0,3],
            [1,1,0,1,0,0],
            [0,1,0,1,1,0],
            [0,0,0,0,1,0],
            [1,1,1,0,1,0],
            [0,0,0,0,0,0]
        ],
        [
            [2,0,0,1,1,0,1,0],
            [0,1,0,1,0,0,0,0],
            [0,0,0,1,1,0,1,1],
            [1,1,0,0,0,0,1,3],
            [1,0,0,1,1,0,1,0],
            [0,0,1,0,1,0,0,0],
            [1,0,0,0,1,0,0,0]
        ],
        [
            [2,0,1,3,1,0],
            [1,0,1,0,1,0],
            [0,0,0,0,0,0]
        ]
    ];

    for($i=0 ; $i<7 ; $i++){
        $boards[] = generateBoard(rand(2,9),rand(2,9));
    }
    

    // On stocke le tableau en session s'il n'a pas déjà été défini
    if(!isset($_SESSION['board'])){
        $randBoard = rand(0,count($boards)-1);
        $_SESSION['board'] = $boards[$randBoard];  
    } 

    // On récupère les différentes variables de notre fonction
    [$directions, $fogBoard, $playerPosition] = whereToMove();

    // On stockera notre message d'erreur ou de réussite ici
    $message = "";

    // Si on appuie sur le bouton Recommencer ou sur une flèche
    if(isset($_POST)){
        // Si le joueur souhaite se déplacer en dehors des bordures, donc que la direction n'est pas dans la variable $directions, on affichera une erreur
        $directionAvailable = 0;
        foreach($directions as $direction => $index){
            // Si le joueur souhaite se déplacer sur une case adjacente
            if(isset($_POST[$direction])){
                $directionAvailable=1;
                if($_SESSION['board'][$index[0]][$index[1]] === 0){
                    $_SESSION['board'][$index[0]][$index[1]] = 2;
                    $_SESSION['board'][$playerPosition[0]][$playerPosition[1]] = 0;
                }
                else if($_SESSION['board'][$index[0]][$index[1]] === 3){
                    $message = "Gagné !";
                }
                else $message = "Impossible d'avancer dans le mur";
                // On réinitialise les positions maintenant qu'on les a modifiées pour ne pas avoir à recharger à nouveau la page
                [$directions, $fogBoard, $playerPosition] = whereToMove();
            }
        }
        // Si on n'a appuyé sur le bouton Recommencer, cela signifie qu'on cherche à faire un mouvement non présent dans le champ des possibles
        if(!$directionAvailable && !empty($_POST) && !isset($_POST["clear"])){
            $message = "Déplacement impossible en dehors du terrain !";
        }
        // Si on appuie sur Recommencer, on vide la session
        else if(isset($_POST["clear"])){
            session_destroy();
            header("refresh:0");
        }
    }

    // On enlève le brouillard sur les cases voisines au joueur
    foreach($directions as $direction => $index){
        if($direction === "left" || $direction === "right")
            $fogBoard[$playerPosition[0]][$index[1]] = $_SESSION['board'][$playerPosition[0]][$index[1]];
        else
            $fogBoard[$index[0]][$playerPosition[1]] = $_SESSION['board'][$index[0]][$playerPosition[1]];
    }
    $fogBoard[$playerPosition[0]][$playerPosition[1]] = $_SESSION['board'][$playerPosition[0]][$playerPosition[1]];


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Laragon</title>
        <link href="main.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <h1>The Ultimate F*cking Greatest Maze</h1>
        <section id="game">
            <table>
                <tbody>
                    <?php
                        foreach($fogBoard as $line){
                            echo "<tr>";
                            foreach($line as $value){
                                switch($value){
                                    case 0 :
                                        echo "<td class='available'></td>";
                                        break;
                                    case 1 :
                                        echo "<td class='wall'></td>";
                                        break;
                                    case 2 :
                                        echo "<td class='player'></td>";
                                        break;
                                    case 3 :
                                        echo "<td class='finish'></td>";
                                        break;
                                    default :
                                        echo "<td class='fog'></td>";
                                }
                            }
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
            <form id="move" action="" method="POST">
                <button type="submit" name="top" class="moveButton top"></button>
                <div>
                    <button type="submit" name="left" class="moveButton left"></button>
                    <button type="submit" name="right" class="moveButton right"></button>
                </div>
                <button type="submit" name="bottom" class="moveButton bottom"></button>
            </form> 
        </section>
        <form id="clear" method="POST">
            <button type="submit" name="clear">Recommencer</button>
        </form>
        <div id="prompts">
            <p>
                <?= $message; ?>
            </p>
        </div>
    </body>
</html>

<?php
    // FONCTIONS
    function generateBoard($rows, $columns){
        $board = [];
        if($rows>0 && $columns >0){
            // On remplit le plateau de murs
            for($i=0 ; $i<$rows ; $i++){
                array_push($board, []);
                for($j=0 ; $j<$columns ; $j++){
                    array_push($board[$i], rand(0,1));
                }
            }
            // Case de départ du joueur
            $board[0][0] = 2;
            // Case d'arrivée aléatoire
            $randRow = rand(1,$rows);
            $randColumn = rand(1, $columns);
            // Si le nombre aléatoire est le nombre max de lignes ou de colonne, on le diminue pour un index possible dans le tableau
            if($randRow===$rows) $randRow-=1;
            if($randColumn===$columns) $randColumn-=1;
            // Si la souris apparait au 0, 0, on change à nouveau les coordonnées et on force une coordonnée à 1 pour être sûr de la validité
            if($randRow===0 && $randColumn===0){
                $randRow = rand(1,$rows);
                if($randRow===$rows) $randRow-=1;
                $randColumn+=1;
            }
            $board[$randRow][$randColumn] = 3;
            // On effectue des mouvements aléatoires pour tracer des routes jusqu'à la souris
            $board = randomMoves($board, [0,0]);
        }
        $board[0][0] = 2;
        return $board;
    }

    function randomMoves($board, $playerPosition){  
        // On récupère les voisins du joueur
        $voisins = getVoisins($board, $playerPosition);
        // On change le board pour que l'endroit où se trouve le joueur ne soit pas un mur
        $board[$playerPosition[0]][$playerPosition[1]] = 0;
        $isFinished=0;
        // Si la souris se trouve dans mes voisins, je définis isFinished à 1
        foreach($voisins as $voisin){
            if($voisin[2]===3) $isFinished=1;
        }
        // Si isFinished est égal à 0 on se déplace sur un voisin aléatoire
        if(!$isFinished){
            $board = randomMoves($board, $voisins[rand(0,count($voisins)-1)]);
        }
        // On retourne le board propre une fois tout terminé
        return $board;
    }

    // Récupère les voisins d'un élément dans un tableau
    function getVoisins($board, $position){
        $voisins = [];
        // On récupère la ligne, la colonne et la valeur des voisins s'ils existent, sinon 0
        if($position[0]-1 >= 0) $voisins[] = [$position[0]-1, $position[1], $board[$position[0]-1][$position[1]]];
        if($position[0]+1 < count($board)) $voisins[] = [$position[0]+1, $position[1], $board[$position[0]+1][$position[1]]];
        if($position[1]-1 >= 0) $voisins[] = [$position[0], $position[1]-1, $board[$position[0]][$position[1]-1]];
        if($position[1]+1 < count($board[0])) $voisins[] = [$position[0], $position[1]+1, $board[$position[0]][$position[1]+1]];
        return $voisins;
    }

    function whereToMove (){
        $playerPosition = [];
        $fogBoard = [];
        $directions = [];
        for($i=0 ; $i<count($_SESSION['board']) ; $i++){
            array_push($fogBoard, []);
            // On définit les voisins à afficher, le reste sera du brouillard de guerre
            $key = array_search(2, $_SESSION['board'][$i]);
            if($key!==false){
                switch($key){
                    // Si le joueur est sur la 1ère case de sa ligne
                    case 0 :
                        // S'il est sur la 1ère ligne, on n'affiche que sa droite et en bas
                        if($i==0)
                            $directions = ["right"=>[$i, $key+1], "bottom"=>[$i+1, $key]];
                        // S'il est sur la dernière ligne, on n'affiche que sa droite et en haut
                        else if($i==count($_SESSION['board'])-1)
                            $directions = ["right"=>[$i, $key+1], "top"=>[$i-1, $key]];
                        // S'il est sur une autre ligne on affiche tout sauf sa gauche
                        else 
                            $directions = ["right"=>[$i, $key+1], "top"=>[$i-1, $key], "bottom"=>[$i+1, $key]];
                        break;
                    // Si le joueur est sur la dernière case de sa ligne
                    case count($_SESSION['board'][$i])-1 :
                        if($i==0)
                            $directions = ["left"=>[$i, $key-1], "bottom"=>[$i+1, $key]];
                        else if($i==count($_SESSION['board'])-1)
                            $directions = ["left"=>[$i, $key-1], "top"=>[$i-1, $key]];
                        else 
                            $directions = ["left"=>[$i, $key-1], "top"=>[$i-1, $key], "bottom"=>[$i+1, $key]];
                        break;
                    // Si le joueur n'est pas sur une bordure
                    default :
                        if($i==0)
                            $directions = ["right"=>[$i, $key+1], "left"=>[$i, $key-1], "bottom"=>[$i+1, $key]];
                        else if($i==count($_SESSION['board'])-1)
                            $directions = ["right"=>[$i, $key+1], "left"=>[$i, $key-1], "top"=>[$i-1, $key]];
                        else 
                            $directions = ["right"=>[$i, $key+1], "left"=>[$i, $key-1], "top"=>[$i-1, $key], "bottom"=>[$i+1, $key]];
                }
                // On stocke toujours la ligne et la colonne ciblée
                $playerPosition = [$i, $key];
            }
            // On reproduit la carte de base, en remplaçant toutes les cases par du brouillard
            for($j=0 ; $j<count($_SESSION['board'][$i]) ; $j++){
                array_push($fogBoard[$i], 4);
            }
        }
        // On renvoie les directions dans lesquelles le joueur peut aller, le plateau dans le brouillard et la position du joueur
        return [$directions, $fogBoard, $playerPosition];
    }
?>