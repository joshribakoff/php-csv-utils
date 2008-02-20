<html>
<head>
  <title>Csv Excel Reader</title>
</head>
<body>
    <table border="1">
<?php
/**
 * This smoke test aims to verify that the csv reader can properly read an excel-formatted csv file
 */
set_include_path(realpath(dirname(__FILE__) . "/../../") . PATH_SEPARATOR . get_include_path());
require_once 'Csv/Dialect/Excel.php';
require_once 'Csv/Reader.php';
$reader = new Csv_Reader("./data/excel.csv", new Csv_Dialect_Excel());
$header = $reader->getRow();
while ($row = $reader->getRow()) {

?>

  <tr>
    
    <?php foreach ($row as $col): ?>
    
      <td><?php echo empty($col) ? "&nbsp;" : htmlspecialchars($col, ENT_QUOTES); ?></td>
    
    <?php endforeach; ?>
    
  </tr>

<?php

}

?>
    </table>
</body>
</html>