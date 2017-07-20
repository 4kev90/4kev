<?php
    $boards = array("technology",
                    "television",
                    "science",
                    "philosophy",
                    "international",
                    "art",
                    "literature",
                    "videogames",
                    "music",
                    "politics",
                    "fitness",
                    "politics",
                    "anime",
                    "random",
                    "feels",
                    "cyberpunk");

function createBoard($nm) {
    $myFile     = 'boards/' . $nm . '.php';
    $fh         = fopen($myFile, 'w') or die("can't open file");
    $stringData = file_get_contents( "boards/test.php" );
    fwrite($fh, $stringData);
    echo "board $nm created<br>";
};

foreach ($boards as &$newBoard) {
    createBoard("$newBoard");
}

?>
