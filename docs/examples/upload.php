<?php set_include_path(realpath('../../') . PATH_SEPARATOR . get_include_path()); ?>
<html>
<head>
<title>Csv Uploader</title>
</head>
<body>
<?php

if (!empty($_FILES['csv'])) {

require_once 'Csv/Exception/FileNotFound.php';
require_once 'Csv/Reader.php';
$filename = $_FILES['csv']['tmp_name'];
try {
echo "<table border='1'>";
$reader = new Csv_Reader($filename);
$row = $reader->getRow();
echo "<tr>";
foreach ($row as $header) printf("<th>%s</th>", $header);
echo "</tr>";
while ($row = $reader->getRow()) {
echo "<tr>";
foreach ($row as $col) printf("<td>%s</td>", $col);
echo "</tr>";
}
echo "</table>";
} catch (CSv_Exception_FileNotFound $e) {
printf("<p>%s</p>", $e->getMessage());
}

} else {

?>

<form method="post" action="./upload.php" enctype="multipart/form-data">
<input type="file" name="csv"> <input type="submit">
</form>

<?php

}

?>

</body>

</html>
