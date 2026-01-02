echo "<pre>";
echo RAIZ . PHP_EOL;
var_dump(scandir(RAIZ));
var_dump(scandir(RAIZ . '/Controllers'));
echo "</pre>";
exit;
