<?php 

$poster_id = rex_request("poster_id","int",0);
$func = rex_request("func","string");

// ----- add link


// ----- edit link


// ----- output list

echo '<table class="rex-table">';
echo '<tr><th>Artikel ID</th><th>Sprach ID<th>Artikelname</th><th>Link</th><th>Funktionen</th></tr>';

$sql = rex_sql::factory();
$urls = $sql->getArray('SELECT * FROM rex_article WHERE yrewrite_url<>""');


foreach($urls as $url) {

  echo '<tr>';
  echo '<td>'.$url["id"].'</td>';
  echo '<td>'.$url["clang"].'</td>';
  echo '<td>'.$url["name"].'</td>';
  echo '<td>/'.rex_getUrl($url["id"],$url["clang"]).'</td>';
  echo '<td>[edit / delete]</td>';
  echo '</tr>';

}
echo '</table>';