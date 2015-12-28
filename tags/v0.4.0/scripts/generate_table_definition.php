<?
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??): missing author

// Pouzitie: php generate_table_definitions < html_vystup
// Program zoberie lubovolne html vygenerovane AISom ktore obsahuje nejaku tabulku
// a pre kazdu tabulku ktoru dany vystup obsahuje vygeneruje
// definicie jej stlpcov.
$data = file_get_contents("php://stdin");
$pattern='@index\=\'(?P<index>[0-9]+)\' shortname\=\'(?P<short>[a-zA-Z0-9]+)\'@';
if (!preg_match_all($pattern, $data, $matches, PREG_SET_ORDER)) {
  die("ERROR PARSING HTML");
}
$START = "    return array(\n";
$END = "    );\n";
$first = true;
echo $START;
foreach ($matches as $row) {

  if ($row['index']==0) {
    if (!$first) echo $END."\n".$START; 
    $first=false;
  }
  echo "      ".$row['short'].",\n";
}
echo $END;
