<?php 
// mijnenveger.php

// Mijnenveger spel
// ----------------
// $aantalRijen                     = aantal rijen (minimum 10)
// $aantalKolommen                  = aantal kolommen (minimum 10)
// $aantalMijnen                    = aantal mijnen (minimum 20)
// $spelBord[rij][kolom]["mine"]    = mijn veld (0=nee 1=ja)
// $spelBord[rij][kolom]["count"]   = aantal omliggende mijnen
// $spelBord[rij][kolom]["clicked"] = ingevuld veld (0=nee 1=ja)
// $spelBord[rij][kolom]["flagged"] = vlag gezet veld (0=nee 1=ja)
// $spelVerloren                    = verloren (0=nee 1=ja)
// $spelOngevlagd                   = aantal niet gevlagde mijnen
// $spelOpen                        = aantal niet ingevulde velden

session_start();

// controleer omliggend veld
function actieVerify($verifyRij, $verifyKolom) {
  global $aantalRijen;
  global $aantalKolommen;
  global $spelBord;

  if (($verifyRij > 0) && ($verifyRij <= $aantalRijen) && ($verifyKolom > 0) && ($verifyKolom <= $aantalKolommen)) {
    if (($spelBord[$verifyRij][$verifyKolom]["flagged"] + $spelBord[$verifyRij][$verifyKolom]["clicked"]) == 0) {
      // simuleer veld geclicked
      actieClear($verifyRij, $verifyKolom);
    }
  }
}

// veld geclicked
function actieClear($clearRij, $clearKolom) {
  global $spelBord;
  global $spelVerloren;
  global $spelOpen;
      
  if ($spelBord[$clearRij][$clearKolom]["mine"] == 1) {
    // veld is mijn
    $spelBord[$clearRij][$clearKolom]["clicked"] = 1;
    $spelVerloren = 1;
  
  } elseif ($spelBord[$clearRij][$clearKolom]["clicked"] == 0) {
    // veld is nog niet ingevuld
    $spelBord[$clearRij][$clearKolom]["clicked"] = 1;
    $spelOpen--;
      
    if ($spelBord[$clearRij][$clearKolom]["count"] == 0) {
      // controleer omliggende velden
      actieVerify($clearRij - 1, $clearKolom - 1);
      actieVerify($clearRij - 1, $clearKolom);
      actieVerify($clearRij - 1, $clearKolom + 1);
      actieVerify($clearRij, $clearKolom - 1);
      actieVerify($clearRij, $clearKolom + 1);
      actieVerify($clearRij + 1, $clearKolom - 1);
      actieVerify($clearRij + 1, $clearKolom);
      actieVerify($clearRij + 1, $clearKolom + 1);
    }
  }
}

// zet flag (op niet ingevuld veld)
function actieFlag($flagRij, $flagKolom) {
  global $spelBord;
  global $spelOpen;
  global $spelOngevlagd;
  
  if ($spelBord[$flagRij][$flagKolom]["clicked"] == 0 && $spelBord[$flagRij][$flagKolom]["flagged"] == 0) {
    // ok voor zet vlag
    $spelBord[$flagRij][$flagKolom]["flagged"] = 1;
    $spelOpen--;
    $spelOngevlagd--;
  }
}

// verwijder flag (op niet ingevuld veld)
function actieUnflag($unflagRij, $unflagKolom) {
  global $spelBord;
  global $spelOpen;
  global $spelOngevlagd;
      
  if ($spelBord[$unflagRij][$unflagKolom]["clicked"] == 0 && $spelBord[$unflagRij][$unflagKolom]["flagged"] == 1) {
    // ok voor verwijder vlag
    $spelBord[$unflagRij][$unflagKolom]["flagged"]=0;
    $spelOpen++;
    $spelOngevlagd++;
  }
}

// terug naar index.php
if (isset($_POST["btnBack"])) {
  header("Location: index.php");
  exit;
}

// lees/initialiseer aantal rijen
if (isset($_SESSION["mijnenvegerRows"])) {
  $aantalRijen = $_SESSION["mijnenvegerRows"];
} else {
  $aantalRijen = 15;
}

// lees/initialiseer aantal kolommen
if (isset($_SESSION["mijnenvegerColumns"])) {
  $aantalKolommen = $_SESSION["mijnenvegerColumns"];
} else {
  $aantalKolommen = 20;
}

// lees/initialiseer aantal mijnen
if (isset($_SESSION["mijnenvegerMines"])) {
  $aantalMijnen = $_SESSION["mijnenvegerMines"];
} else {
  $aantalMijnen = 30;
}

// lees spel bord
if (isset($_SESSION["mijnenvegerBoard"])) {
  $spelBord = $_SESSION["mijnenvegerBoard"];
}

// lees spel verloren
if (isset($_SESSION["mijnenvegerLost"])) {
  $spelVerloren = $_SESSION["mijnenvegerLost"];
}

// lees spel mijnen
if (isset($_SESSION["mijnenvegerUnflagged"])) {
  $spelOngevlagd = $_SESSION["mijnenvegerUnflagged"];
}

// lees spel onbekend
if (isset($_SESSION["mijnenvegerOpen"])) {
  $spelOpen = $_SESSION["mijnenvegerOpen"];
}

// initialiseer spel nieuw/actie
$spelNieuw = 0;
$spelActie = "";

// controleer actie
if (isset($_GET["action"])) {
  $spelActie = $_GET["action"];

  if ($spelActie == "new") {
    // speler start nieuw spel
    $spelNieuw = 1;

    // speler wijzigt aantal rijen
    if (isset($_GET["rows"])) {
      $aantalRijen = $_GET["rows"];
    }

    // speler wijzigt aantal kolommen
    if (isset($_GET["columns"])) {
      $aantalKolommen = $_GET["columns"];
    }

    // speler wijzigt aantal mijnen
    if (isset($_GET["mines"])) {
      $aantalMijnen = $_GET["mines"];
    }

  } elseif (isset($_GET["row"]) && isset($_GET["column"])) {
    // speler heeft een veld geclicked
    $actieRij = $_GET["row"];
    $actieKolom = $_GET["column"];
  
  } else {
    // geen speler actie
    $spelActie = "";
  }
}

// controleer aantal rijen
if ($aantalRijen < 10) {
  $aantalRijen = 10;
  $spelNieuw = 1;
}

// controleer aantal kolommen
if ($aantalKolommen < 10) {
  $aantalKolommen = 10;
  $spelNieuw = 1;
}

// controleer rijen/kolommen limiet
if (($aantalRijen * $aantalKolommen) > 1000) {
  $aantalKolommen = 20;
  $aantalRijen = 15;
  $spelNieuw = 1;
}

// controleer aantal mijnen
if ($aantalMijnen < 20) {
  $aantalMijnen = 20;
  $spelNieuw = 1;
}

// controleer mijnen limiet
if ($aantalMijnen > ($aantalKolommen * $aantalRijen)) {
  $aantalMijnen = $aantalKolommen * $aantalRijen;
  $spelNieuw = 1;
}

// start nieuw spel indien nodig
if (($spelNieuw == 1) || (!isset($spelBord))) {
  // initialiseer spel status
  $spelOpen = $aantalRijen * $aantalKolommen;
  $spelOngevlagd = $aantalMijnen;
  $spelVerloren = 0;

  // maak leeg spel bord
  unset($spelBord);
  for ($indexRij = 0; $indexRij <= ($aantalRijen + 1); $indexRij++) {
    for ($indexKolom = 0; $indexKolom <= ($aantalKolommen + 1); $indexKolom++) {
      $spelBord[$indexRij][$indexKolom]["mine"] = 0;
      $spelBord[$indexRij][$indexKolom]["clicked"] = 0;
      $spelBord[$indexRij][$indexKolom]["flagged"] = 0;
    }
  }

  // voeg mijnen toe
  srand(time());
  for ($indexMijn = 0; $indexMijn < $aantalMijnen; $indexMijn++) {
    $testRij = rand(1,$aantalRijen);
    $testKolom = rand(1,$aantalKolommen);
    if ($spelBord[$testRij][$testKolom]["mine"] == 1) {
      $indexMijn--;
    } else {
      $spelBord[$testRij][$testKolom]["mine"]=1;
    }
  }

  // tel aantal omliggende mijnen voor elk veld
  for ($indexRij = 1; $indexRij <= $aantalRijen; $indexRij++) {
    for ($indexKolom = 1; $indexKolom <= $aantalKolommen; $indexKolom++) {
      $spelBord[$indexRij][$indexKolom]["count"] =
        $spelBord[$indexRij - 1][$indexKolom - 1]["mine"] +
        $spelBord[$indexRij - 1][$indexKolom]["mine"] +
        $spelBord[$indexRij - 1][$indexKolom + 1]["mine"] +
        $spelBord[$indexRij][$indexKolom - 1]["mine"] +
        $spelBord[$indexRij][$indexKolom + 1]["mine"] +
        $spelBord[$indexRij + 1][$indexKolom - 1]["mine"] +
        $spelBord[$indexRij + 1][$indexKolom]["mine"] +
        $spelBord[$indexRij + 1][$indexKolom + 1]["mine"];
    }
  }
}

// voer actie uit
switch($spelActie) {
  case "clear":
    actieClear($actieRij, $actieKolom);
    break;
  case "flag":
    actieFlag($actieRij, $actieKolom);
    break;
  case "unflag":
    actieUnflag($actieRij, $actieKolom);
    break;
  case "new":
    break;
  default:
  
    // directe webpage oproep wordt omgeleid naar index.php
    header("Location: index.php");
    exit;
}

// zet status (naam status png)
if ($spelOpen == 0) {
  $spelStatus = "shades";
} elseif ($spelVerloren == 1) {
  $spelStatus = "dead";
} else {
  $spelStatus = "smile";
}

// bewaar in sessie
$_SESSION["mijnenvegerRows"] = $aantalRijen;
$_SESSION["mijnenvegerColumns"] = $aantalKolommen;
$_SESSION["mijnenvegerMines"] = $aantalMijnen;
$_SESSION["mijnenvegerBoard"] = $spelBord;
$_SESSION["mijnenvegerLost"] = $spelVerloren;
$_SESSION["mijnenvegerUnflagged"] = $spelOngevlagd;
$_SESSION["mijnenvegerOpen"] = $spelOpen;
?>

<html>
  <head>
    <title>PHPgames - Mijnenveger</title>
    <link rel="stylesheet" type="text/css" href="css/common.css" />
  </head>
  <body>
    <div class="bodySpel">
      <div class="spelBord">

        <h2>PHPgames</h2>
        <form method="post" action="index.php">
          <input type="submit" class="inputBack" name="btnBack" value="Spel keuze"></td>
        </form>

      </div>
      <hr>
      <div class="spelBord">

        <table class="bordTable">
          <tr>
            <td class="tableHeader">Mijnenveger</td>
          </tr>
          <tr>
            <td class="tableStatus">
              <b><?php print("$spelOngevlagd");?></b>
              <br>
              <a href="mijnenveger.php?action=new">
                <img class="statusImg" src="img/button<?php print("$spelStatus");?>.png">
              </a>
            </td>
          </tr>
          <tr>
            <td class="tableVak">
              <table class="vakGrid">

                <?php 
                print("<tr>\n<td class=\"gridHoek\"></td>\n");
                for ($indexKolom = 1; $indexKolom <= $aantalKolommen; $indexKolom++) {
                  print("<td class=\"gridKolom\"></td>\n");
                }
                print("</tr>\n");
                for ($indexRij = 1; $indexRij <= $aantalRijen; $indexRij++) {
                  print("<tr>\n<td class=\"gridRij\"></td>");
                  for ($indexKolom = 1; $indexKolom <= $aantalKolommen; $indexKolom++) {
                    print("<td class=\"gridCell\">");

                    if ($spelBord[$indexRij][$indexKolom]["flagged"] == 1) {
                      if ($spelVerloren == 1) {
                        if ($spelBord[$indexRij][$indexKolom]["mine"] == 1) {
                            // vlag gezet op veld met mijn en spel verloren (toon vlag)
                            print("<img src=\"img/flag.png\">");
                        } else {
                            // vlag gezet op veld zonder mijn en spel verloren (toon fout)
                            print("<img src=\"img/wrong.png\">");
                        }
                      } else {
                            // vlag gezet en spel niet verloren (toon vlag)
                            print("<a href=\"mijnenveger.php?action=unflag&row=" . $indexRij . "&column=" . $indexKolom . "\"><img src=\"img/flag.png\"></a>");
                      }
                    } elseif ($spelBord[$indexRij][$indexKolom]["clicked"] == 0) {
                      if ($spelVerloren == 1) {
                        if ($spelBord[$indexRij][$indexKolom]["mine"] == 1) {
                            // niet ingevuld veld met mijn en spel verloren (toon zwarte mijn)
                            print("<img src=\"img/mine.png\">");
                        } else {
                            // niet ingevuld veld zonder mijn en spel verloren (toon leeg zonder acties)
                            print("<img class=\"cellHelft\" src=\"img/clear0.png\">");
                            print("<img class=\"cellHelft\" src=\"img/clear1.png\">");
                        }
                      } else {
                            // niet ingevuld veld en niet verloren (toon leeg met acties)
                            print("<a href=\"mijnenveger.php?action=clear&row=" . $indexRij . "&column=" . $indexKolom . "\"><img class=\"cellHelft\" src=\"img/clear0.png\"></a>");
                            print("<a href=\"mijnenveger.php?action=flag&row=" . $indexRij . "&column=" . $indexKolom . "\"><img class=\"cellHelft\" src=\"img/clear1.png\"></a>");
                      }
                    } elseif ($spelBord[$indexRij][$indexKolom]["mine"] == 1) {
                            // ingevuld veld met mijn (toon rode mijn)
                            print("<img src=\"img/redmine.png\">");
                    } elseif ($spelBord[$indexRij][$indexKolom]["count"] == 0) {
                            // ingevuld veld zonder mijn en zonder omliggende mijnen
                            print("<img src=\"img/empty.png\">");
                    } else {
                            // ingevuld veld zonder mijn en met omliggende mijnen
                            print("<img src=\"img/num" . $spelBord[$indexRij][$indexKolom]["count"] . ".png\">");
                    }

                    print("</td>\n");
                  }
                  print("</tr>\n");
                }
                ?>

              </table>
            </td>
          </tr>
        </table>
      
        <table>
          <tr>
            <th>Click op</th>
            <td></td>
            <th>Actie</th>
          </tr>
          <tr>
            <td>linker helft</td>
            <td>: </td>
            <td>veeg mijnen</td>
          </tr>
          <tr>
            <td>rechter helft</td>
            <td>: </td>
            <td>vlag plaatsen</td>
          </tr>
        </table>

      </div>
      <hr>
      <div class="spelBord">

        <h2>Configuratie</h2>
        <form method="get" action="mijnenveger.php">
          <table>
            <input type="hidden" name="action" value="new">
            <tr>
              <td>Aantal kolommen</td>
              <td>: </td>
              <td><input class="inputVeld" type="number" name="columns" min="10" max="30" value="<?php print("$aantalKolommen")?>"></td>
            </tr>
            <tr>
              <td>Aantal rijen</td>
              <td>: </td>
              <td><input class="inputVeld" type="number" name="rows" min="10" max="30" value="<?php print("$aantalRijen")?>"></td>
            </tr>
            <tr>
              <td>Aantal mijnen</td>
              <td>: </td>
              <td><input class="inputVeld" type="number" name="mines" min="20" value="<?php print("$aantalMijnen")?>"></td>
            </tr>
            <tr>
              <td class="inputNieuw" colspan="3"><input type="submit" value="Start nieuw spel"></td>
            </tr>
          </table>
        </form>
      
      </div>
    </div>
  </body>
</html>
