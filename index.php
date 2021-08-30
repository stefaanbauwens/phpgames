<?php 
// index.php

session_start();
?>

<html>
  <head>
    <title>PHPgames</title>
    <link rel="stylesheet" type="text/css" href="css/common.css" />
  </head>
  <body>
    <div class="bodySpel">
      <div class="spelBord">
        
        <h2>PHPgames</h2>

      </div>
      <hr>
      <div class="spelBord">

        <table>
          <tr>
            <td>
              <a href="mijnenveger.php?action=new"><img class="menuImg" src="img/mijnenveger.png"></a>
            </td>
            <td class="menuGap"></td>
            <td>
              <a href="memory.php?action=new"><img class="menuImg" src="img/memory.png"></a>
            </td>
          </tr>
        </table>
      
      </div>
    </div>
  </body>
</html>
