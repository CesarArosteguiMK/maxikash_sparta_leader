<?php
$f = 'c:/xampp/htdocs/sparta___SPARTA_SECRET_REDACTED__/scripts/extraer_script_ticket_sabueso.php';
$c = file_get_contents('c:/xampp/htdocs/sparta___SPARTA_SECRET_REDACTED__/backend/controllers/Sabueso.php');
$c = substr($c, 0, 500);
file_put_contents($f, '<?php
chdir(dirname(__DIR__));
define("RAIZ", dirname(__DIR__)."/backend");
define("CONFIGURACION", parse_ini_file(RAIZ."/config/config.ini"));
define("CONTROLADORES", RAIZ."/controllers");
define("LIBRERIAS", RAIZ."/Libs");
define("MODELOS", RAIZ."/models");
define("VISTAS", RAIZ."/views");
define("COMPONENTES", RAIZ."/components");
define("LOGIN", "Login");
define("VISTA_DEFECTO", "Inicio");
define("METODO_DEFECTO", "index");
error_reporting(E_ERROR|E_PARSE);
require_once LIBRERIAS."/PhpSpreadsheet/vendor/autoload.php";
spl_autoload_register(function($a){
  if(strpos($a,"PhpOffice\\")===0||strpos($a,"ZipStream\\")===0||strpos($a,"Psr\\")===0)return;
  $r=RAIZ."/".str_replace("\\","/",$a).".php";
  if(!file_exists($r))throw new Exception("Autoload: ".$r);
  require_once $r;
});
require_once RAIZ."/config/config.php";
date_default_timezone_set("America/Mexico_City");
if(php_sapi_name()==="cli"){
  if(session_status()===PHP_SESSION_NONE)session_start();
  ' . '$_SESSION[\'login\']=true;$_SESSION[\'usuario_id\']=1;$_SESSION[\'modulos\']=[18];$_SESSION[\'persona_id\']=1;' . '
}
class SabuesoExtraerScript extends Controllers\Sabueso{
  private '."$scriptCapture".'="";
  public function set('."$v,$val".'){
    parent::set('."$v,$val".');
    if('."$v".'==="script")'."$this->scriptCapture".'='."$val".';
  }
  public function render('."$a,$t=false".'){
    '."$s=$this->scriptCapture".';
    if('."$s".'===""){echo "Error: no script\n";exit(1);}
    '."$dir=RAIZ.\"/storage\"".';if(!is_dir('."$dir".'))mkdir('."$dir".',0755,true);
    '."$out=$dir.\"/script_ticket_extraido.js\"".';
    file_put_contents('."$out,$s".');
    '."$lineas=substr_count($s,\"\\n\")+1".';
    echo "Guardado: ".'."$out".'." (".'."$lineas".'." lineas)\n";
    '."$arr=explode(\"\\n\",$s)".';
    if(count('."$arr".')>=1540)echo "Linea 1540: ".trim('."$arr[1539]".')."\n";
    exit(0);
  }
}
'."$x=new SabuesoExtraerScript();$x->ticket();".'
');
echo "OK";
