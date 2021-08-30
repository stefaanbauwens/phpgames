<?php 
// memory.php

// Memory spel
// -----------
// $aantalRijen              = aantal rijen (minimum=2, altijd even getal)
// $aantalKolommen           = aantal kolommen (minimum=2)
// $aantalKaarten            = aantal verschillende kaarten
// $aantalVelden             = aantal velden
// $aantalPogingen           = aantal pogingen
// $spelBord[veld]["turned"] = kaart is gedraaid (0=nee 1=ja)
// $spelBord[veld]["card"]   = kaart nummer
// $spelOngedraaid           = aantal verschillende ongedraaide kaarten
// $gedraaidEerste           = veld nummer van eerste gedraaide kaart (-1 indien nog niet gedraaid)
// $gedraaidTweede           = veld nummer van tweede gedraaide kaart (-1 indien nog niet gedraaid)

session_start();

// veld geclicked
function actieTurn($turnVeld) {
  global $aantalVelden;
  global $aantalPogingen;
  global $gedraaidEerste;
  global $gedraaidTweede;
  global $spelOngedraaid;
  global $spelBord;

  if ($turnVeld >= 0 && $turnVeld < $aantalVelden) {
    if ($spelBord[$turnVeld]["turned"] == 0) {
      // draai geclicked veld
      $spelBord[$turnVeld]["turned"] = 1;

      if ($gedraaidEerste == -1) {
        // dit is de eerste kaart die wordt gedraaid
        $gedraaidEerste = $turnVeld;
      } elseif ($gedraaidTweede == -1) {
        // dit is de tweede kaart die wordt gedraaid
        $gedraaidTweede = $turnVeld;
        $aantalPogingen++;
        // controle gelijke kaarten
        if ($spelBord[$gedraaidEerste]["card"] == $spelBord[$gedraaidTweede]["card"]) {
          // vermijd dat kaarten worden terug gedraaid (gelijke kaarten)
          $gedraaidEerste = -1;
          $gedraaidTweede = -1;
          $spelOngedraaid--;
        }
      } else {
        // dit is de derde kaart die wordt gedraaid, draai beide vorige kaarten terug (verschillende kaarten)
        $spelBord[$gedraaidEerste]["turned"] = 0;
        $spelBord[$gedraaidTweede]["turned"] = 0;
        // zet eerste en tweede kaart die wordt gedraaid
        $gedraaidEerste = $turnVeld;
        $gedraaidTweede = -1;
      }
    }
  }
}

// lees/initialiseer aantal rijen
if (isset($_SESSION["memoryRows"])) {
  $aantalRijen = $_SESSION["memoryRows"];
} else {
  $aantalRijen = 3;
}

// lees/initialiseer aantal kolommen
if (isset($_SESSION["memoryColumns"])) {
  $aantalKolommen = $_SESSION["memoryColumns"];
} else {
  $aantalKolommen = 4;
}

// lees spel bord
if (isset($_SESSION["memoryBoard"])) {
  $spelBord = $_SESSION["memoryBoard"];
}

// lees spel ongedraaid
if (isset($_SESSION["memoryUnturned"])) {
  $spelOngedraaid = $_SESSION["memoryUnturned"];
}

// lees eerste gedraaid
if (isset($_SESSION["memoryFirst"])) {
  $gedraaidEerste = $_SESSION["memoryFirst"];
}

// lees tweede gedraaid
if (isset($_SESSION["memorySecond"])) {
  $gedraaidTweede = $_SESSION["memorySecond"];
}

// lees aantal pogingen
if (isset($_SESSION["memoryTry"])) {
  $aantalPogingen = $_SESSION["memoryTry"];
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

    // speler wijzigt aantal rijen/kolommen
    if (isset($_GET["size"])) {
      $aantalKolommen = (int) $_GET["size"];
      $aantalRijen = (int)($aantalKolommen / 100);
      $aantalKolommen = $aantalKolommen - ($aantalRijen * 100);
    }

    // speler wijzigt aantal kolommen
    if (isset($_GET["columns"])) {
      $aantalKolommen = $_GET["columns"];
    }

  } elseif (isset($_GET["cell"])) {
    // speler heeft een veld geclicked
    $actieVeld = $_GET["cell"];
  
  } else {
    // geen speler actie
    $spelActie = "";
  }
}

// controleer minimum/maximum rijen
if ($aantalRijen < 2) {
  $aantalRijen = 2;
}
if ($aantalRijen > 6) {
  $aantalRijen = 6;
}

// controleer minimum/maximum kolommen
if ($aantalKolommen < 2) {
  $aantalKolommen = 2;
}
if ($aantalKolommen > 10) {
  $aantalKolommen = 10;
}

// bereken aantal velden
$aantalVelden = $aantalRijen * $aantalKolommen;

// controleer limiet van 52 velden
if ($aantalVelden > 52) {
  $aantalRijen = 6;
  $aantalKolommen = 6;
  $aantalVelden = 36;
}
  
// bereken aantal verschillende kaarten
$aantalKaarten = $aantalVelden / 2;

// start nieuw spel indien nodig
if (($spelNieuw == 1) || (!isset($spelBord))) {
  // initialiseer gedraaide kaarten
  $spelOngedraaid = $aantalKaarten;
  $gedraaidEerste = -1;
  $gedraaidTweede = -1;

  // initialiseer aantal pogingen
  $aantalPogingen = 0;
  
  // maak lijst van alle kaarten
  $kaartVolgorde = "";
  for ($indexKaart = 0; $indexKaart < $aantalKaarten; $indexKaart++) {
    $kaartCode = chr(65 + $indexKaart);
    $kaartVolgorde .= $kaartCode . $kaartCode;
  }

  // maak nieuw spel bord
  unset($spelBord);
  srand(time());
  for ($indexVeld = 0; $indexVeld < $aantalVelden; $indexVeld++) {
    // selecteer willekeurige kaart uit lijst
    $indexKaart = rand(1,strlen($kaartVolgorde)) - 1;
    $kaartCode = substr($kaartVolgorde, $indexKaart, 1);
    $kaartVolgorde = substr($kaartVolgorde, 0, $indexKaart) . substr($kaartVolgorde, $indexKaart + 1);
    // vul spel bord in
    $spelBord[$indexVeld]["card"] = $kaartCode;
    $spelBord[$indexVeld]["turned"] = 0;
  }
}

// voer actie uit
switch($spelActie) {
  case "turn":
    actieTurn($actieVeld);
    break;
}

// zet status (naam status png)
if ($spelOngedraaid == 0) {
  $spelStatus = "shades";
} else {
  $spelStatus = "smile";
}

// bereken input waarde
$spelSize = $aantalRijen * 100 + $aantalKolommen;

// bewaar in sessie
$_SESSION["memoryRows"] = $aantalRijen;
$_SESSION["memoryColumns"] = $aantalKolommen;
$_SESSION["memoryTry"] = $aantalPogingen;
$_SESSION["memoryBoard"] = $spelBord;
$_SESSION["memoryUnturned"] = $spelOngedraaid;
$_SESSION["memoryFirst"] = $gedraaidEerste;
$_SESSION["memorySecond"] = $gedraaidTweede;
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
            <td class="tableHeader">Memory</td>
          </tr>
          <tr>
            <td class="tableStatus">
              <b><?php print("Aantal : $spelOngedraaid - Pogingen : $aantalPogingen"); ?></b>
              <br>
              <a href="memory.php?action=new">
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
                $indexVeld = 0;
                for ($indexRij = 0; $indexRij < $aantalRijen; $indexRij++) {
                  print("<tr>\n<td class=\"gridRij\"></td>");
                  for ($indexKolom = 1; $indexKolom <= $aantalKolommen; $indexKolom++) {
                    print("<td class=\"gridCell\">");

                    if ($spelBord[$indexVeld]["turned"] == 1) {
                      // kaar is gedraaid
                      print("<img class=\"cellMemory\" src=\"img/card" . $spelBord[$indexVeld]["card"] . ".png\">");
                    } else {
                      // kaart is niet gedraaid
                      print("<a href=\"memory.php?action=turn&cell=" . $indexVeld . "\"><img class=\"cellMemory\" src=\"img/back.png\">");
                    }
                
                    print("</td>\n");
                    $indexVeld++;
                  }
                  print("</tr>\n");
                }
                ?>

              </table>
            </td>
          </tr>
        </table>

      </div>
      <hr>
      <div class="spelBord">

        <h2>Configuratie</h2>
        <form method="get" action="memory.php">
          <table>
            <input type="hidden" name="action" value="new">
            <tr>
              <td>Grootte</td>
              <td>:</td>
              <td>
                <select name="size" id="size">
                  <option value="202"<?php print($spelSize == 202 ? " selected" : ""); ?>>2 rijen en 2 kolommen (2)</option>
                  <option value="203"<?php print($spelSize == 203 ? " selected" : ""); ?>>2 rijen en 3 kolommen (3)</option>
                  <option value="204"<?php print($spelSize == 204 ? " selected" : ""); ?>>2 rijen en 4 kolommen (4)</option>
                  <option value="205"<?php print($spelSize == 205 ? " selected" : ""); ?>>2 rijen en 5 kolommen (5)</option>
                  <option value="304"<?php print($spelSize == 304 ? " selected" : ""); ?>>3 rijen en 4 kolommen (6)</option>
                  <option value="207"<?php print($spelSize == 207 ? " selected" : ""); ?>>2 rijen en 7 kolommen (7)</option>
                  <option value="404"<?php print($spelSize == 404 ? " selected" : ""); ?>>4 rijen en 4 kolommen (8)</option>
                  <option value="306"<?php print($spelSize == 306 ? " selected" : ""); ?>>3 rijen en 6 kolommen (9)</option>
                  <option value="405"<?php print($spelSize == 405 ? " selected" : ""); ?>>4 rijen en 5 kolommen (10)</option>
                  <option value="406"<?php print($spelSize == 406 ? " selected" : ""); ?>>4 rijen en 6 kolommen (12)</option>
                  <option value="506"<?php print($spelSize == 506 ? " selected" : ""); ?>>5 rijen en 6 kolommen (15)</option>
                  <option value="606"<?php print($spelSize == 606 ? " selected" : ""); ?>>6 rijen en 6 kolommen (18)</option>
                  <option value="508"<?php print($spelSize == 508 ? " selected" : ""); ?>>5 rijen en 8 kolommen (20)</option>
                  <option value="607"<?php print($spelSize == 607 ? " selected" : ""); ?>>6 rijen en 7 kolommen (21)</option>
                  <option value="608"<?php print($spelSize == 608 ? " selected" : ""); ?>>6 rijen en 8 kolommen (24)</option>
                  <option value="510"<?php print($spelSize == 510 ? " selected" : ""); ?>>5 rijen en 10 kolommen (25)</option>
                </select>
              </td>
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
