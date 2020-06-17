<?php 

require 'vendor/autoload.php';
use Curl\Curl;
use SimpleCrud\Database;
use Psr\Log\LogLevel;


abstract class Base {
   

    protected $logger;
    protected $console = false;
    protected $db_config = [

        'dsn' => 'mysql:host=localhost;dbname=stand_alone',
        'user'=>  'root',
        'pass'=> 'cmst,1.07'
    ];

    protected $db;


    function  console($text, $level)
    {
        if ($this->console) 
        {
        
            echo "[" . date('Y-m-d G:i:s.u') . "] [" . $level . "] " . $text . "\n"; 

        } 
    }


    function getApiData($url)
    {

        $this->logger->info('Realizando llamada curl');
        $this->console('Realizando llamada curl', 'info');
        $this->logger->debug('Llamada curl: ' . $url);  
        $this->console('Llamada curl: ' . $url, 'debug');
        $curl = new Curl();
       
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
        $curl->setOpt(CURLOPT_HTTPHEADER,array('content-type:application/json;charset=UTF-8'));
        $curl->get($url);

        if ($curl->error)
        {
           
            $this->logger->info('Se ha producido un error en la llamada CURL: ' . $curl->errorMessage);
            $this->console('Se ha producido un error  en la llamada CURLs: ' . $curl->errorMessage, 'error');
            exit();
        }
        else
        {

           
            $response = $curl->response;


            $this->logger->info('LLamada realizada correctamente');
            $this->console('LLamada realizada correctamente', 'info');

            $this->logger->debug('Obtenidos ' . count($response->resultados) . ' resultados');
            $this->console('Obtenidos ' . count($response->resultados) . ' resultados', 'debug');

    
            return $response;
          
        }
        
    }

    
    function setConsole ($console) {
        $this->console = $console;

    }


    abstract function dbOperations($data);


    function start()
    {
       $data = $this->getApiData($this->url);
       $this->dbOperations($data);

       $this->logger->info('Proceso Finalizado');
       $this->console('Proceso Finalizado','info');
    }

}



class aempsPSuminitros extends Base
{
     protected $url= "https://cima.aemps.es/cima/rest/psuministro?pagesize=50000&finalizados=1&activos=1";
     

     protected $log_config =  array(
        'prefix' => 'aemps_psuministros_log_'

    );


     function dbOperations($data)
     {
        try
        {
        
        $this->logger->info('Conectando a BBDD');
        $this->console('Connectado a BBDD','info');
        $this->logger->debug( 'Host:' . $this->db_config['dsn'] . ", User: "  . $this->db_config['user']  );
        $this->console( 'Host:' . $this->db_config['dsn'] . ", User: "  . $this->db_config['user'],'debug');
        
           
        $pdo = new PDO($this->db_config['dsn'], $this->db_config['user'], $this->db_config['pass']);
        $this->db = new Database($pdo);


    
      
        $this->db->getConnection()->logQueries(true);


        $this->logger->info('Conexion Correcta');
        $this->console('Conexion Correcta','info');


        $this->logger->info('Limpiando tabla');
        $this->console('Limpiando tabla','info');
        $aemps_psuministros = $this->db->aemps_psuministros;
        $aemps_psuministros->delete()->get();

        $queries = $this->db->getConnection()->getQueries();

      

        
        $this->logger->debug($queries[count($queries)- 1]['statement']);
        $this->console($queries[count($queries)- 1]['statement'],'debug');


        $this->logger->info('Insertando datos...');
        $this->console('Insertando datos...','info');

        foreach ($data->resultados as $clave => $valor) {
    
             
            $fields = (array) $valor;

           $json_string = json_encode($fields);
            
         @$fields['nombre'] = utf8_decode($fields['nombre']);
         @$fields['observ'] = utf8_decode($fields['observ']);
          

        
            $id = $aemps_psuministros
            ->insert($fields)
            ->get();

            $queries = $this->db->getConnection()->getQueries();  

          
            $this->logger->debug(preg_replace("/\r|\n|\t/","", $queries[count($queries)- 1]['statement']) . " " . $json_string);
            $this->console(preg_replace("/\r|\n|\t/","", $queries[count($queries)- 1]['statement']) .  " " . $json_string,'debug');
       
        
        }






        }
        catch (Exception $ex)
        {
         $this->logger->error('Se ha producido un error en dbOperations(Linea: ' . $ex->getLine() .  '): ' . $ex->getMessage());
         $this->console('Se ha producido un error en dbOperations(Linea: ' . $ex->getLine() .  '): ' . $ex->getMessage(), 'error');
         exit();
       }
     }


     function __construct($args)
     {
 
         if (isset($args[1]) && $args[1] === 'console') 
         {
             $this->setConsole(true);
         }
 
         
         $this->logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',LogLevel::DEBUG,$this->log_config);
         $this->logger->info('--------- Problemas de Suministros ---------');
         $this->console('--------- Problemas de Suministros ---------', 'info');
     }


    
}


class aempsNSeguridad extends Base
{
     protected $url= "https://cima.aemps.es/cima/rest/notas?ndias=10000&pagesize=2000";
     
     protected $log_config =  array(
         'prefix' => 'aemps_nseguridad_log_'

     );


     function dbOperations($data)
     {
        try
        {
        

          
        $this->logger->info('Conectando a BBDD');
        $this->console('Connectado a BBDD','info');
        $this->logger->debug( 'Host:' . $this->db_config['dsn'] . ", User: "  . $this->db_config['user']  );
        $this->console( 'Host:' . $this->db_config['dsn'] . ", User: "  . $this->db_config['user'],'debug');
        
           
        $pdo = new PDO($this->db_config['dsn'], $this->db_config['user'], $this->db_config['pass']);
        $this->db = new Database($pdo);


    
      
        $this->db->getConnection()->logQueries(true);


        $this->logger->info('Conexion Correcta');
        $this->console('Conexion Correcta','info');


        $this->logger->info('Limpiando tabla');
        $this->console('Limpiando tabla','info');
        $aemps_nseguridad = $this->db->aemps_nseguridad;
        $aemps_nseguridad->delete()->get();

        $queries = $this->db->getConnection()->getQueries();

      

        
        $this->logger->debug($queries[count($queries)- 1]['statement']);
        $this->console($queries[count($queries)- 1]['statement'],'debug');


        $this->logger->info('Insertando datos...');
        $this->console('Insertando datos...','info');

        foreach ($data->resultados as $clave => $valor) {
    
             
            $fields = (array) $valor;

           $json_string = json_encode($fields);
            
         @$fields['asunto'] = utf8_decode($fields['asunto']);
      
          

        
            $id = $aemps_nseguridad
            ->insert($fields)
            ->get();

            $queries = $this->db->getConnection()->getQueries();  

          
            $this->logger->debug(preg_replace("/\r|\n|\t|'    '/","", $queries[count($queries)- 1]['statement']) . " " . $json_string);
            $this->console(preg_replace("/\r|\n|\t|'    '/","", $queries[count($queries)- 1]['statement']) .  " " . $json_string,'debug');
       
        
        }






        }
        catch (Exception $ex)
        {
         $this->logger->error('Se ha producido un error en dbOperations(Linea: ' . $ex->getLine() .  '): ' . $ex->getMessage());
         $this->console('Se ha producido un error en dbOperations(Linea: ' . $ex->getLine() .  '): ' . $ex->getMessage(), 'error');
         exit();
       }
     }


     function __construct($args)
     {
 
         if (isset($args[1]) && $args[1] === 'console') 
         {
             $this->setConsole(true);
         }
 
         
         $this->logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',LogLevel::DEBUG,$this->log_config);
         $this->logger->info('--------- Notas de Seguridad ---------');
         $this->console('---------  Notas de Seguridad ---------', 'info');
     }


    
}


$psum_process = new aempsPSuminitros($argv);
$psum_process->start();

$nseg_process = new aempsNSeguridad($argv);
$nseg_process->start();
