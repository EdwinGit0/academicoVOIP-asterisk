#!/usr/bin/php -q
<?php
require_once "phpagi.php";
require_once "server.php";

ob_implicit_flush(true) ;
set_time_limit(30) ;

class academico extends server {

    //iniciar sesion para usuario
    public function login(){
        $agi = new AGI();
        $phone = $_SERVER['argv'][2];
        $clave = $_SERVER['argv'][3];
        
        $data = array(
            'phone' => $phone,
            'clave' => $clave
        );
        $jsonData = json_encode($data);
        
        $result = server::post($jsonData,'controlador/admin/ApiController.php?op=login');
        
        if($result){
            $result_data = json_decode($result,true);
            if($result_data['status'] == 'ok'){
                $token=$result_data['result']['token'];	
                $message = "Bienvenido al sistema de consulta de calificaciones";
                $agi->set_variable(message,$message);
                $agi->set_variable(token,$token);
                if($result_data['result']['user']==="family"){
                    $agi->set_variable(studentmss,"Ingrese el numero de carnet de identidad del estudiante");
                    exit();
                } 
                $agi->set_variable(studentmss,"student");
                exit();
            }
            if($result_data['status'] == 'error'){
                $agi->exec('Festival',"El\ numero");
                $agi->exec('SayDigits',$phone);
                $agi->exec('Festival',"No\ esta\ registrado\ en\ el\ sistema\ o\ la\ clave\ es\ incorrecta");
                $agi->hangup();
                exit();
            }
        }else{
            $agi->exec('Festival',"Error\ del\ servidor\ interno");
            $agi->hangup();
        }
    }

    //obtener calificaciones del estudiante
    public function qualification(){
        $agi = new AGI();
        $gestion = $_SERVER['argv'][2];
        $periodo = $_SERVER['argv'][3];
        $token = $_SERVER['argv'][4];
        $ci = $_SERVER['argv'][5];

        $data = array(
            'ci_alumno' => $ci,
            'gestion' => $gestion,
            'periodo' => $periodo,
            'token' => $token
        );

        $jsonData = json_encode($data);
    
        $result = server::post($jsonData,'controlador/admin/ApiController.php?op=get-qualification');
    
        if($result){
            $result_data = json_decode($result,true);
            if($result_data['status'] == 'error'){
                $message=$this->converterTXT($result_data['result']['error_msg']);
                $agi->exec('Festival',$message);
                $agi->hangup();
                exit();
            }
            foreach($result_data as $data){
                $nota = ( $data['NOTA'] ) ? $data['NOTA'] : "Sin\ calificar";
                $agi->exec('Festival', 'Materia\ '.$this->converterTXT($data['NOMBRE_AREA']));
                $agi->exec('Festival', 'Nota\ '.$nota );
            }
        }else{
            $agi->exec('Festival',"Error\ del\ servidor\ interno");
            $agi->hangup();
        }
    }

    //obtener calificaciones del estudiante
    public function information(){
        $agi = new AGI();
        $token = $_SERVER['argv'][2];

        $data = array(
            'token' => $token
        );

        $jsonData = json_encode($data);
        $result = server::post($jsonData,'controlador/admin/ApiController.php?op=get-infouser');
    
        if($result){
            $result_data = json_decode($result,true);
            if($result_data['status'] == 'error'){
                $message=$this->converterTXT($result_data['result']['error_msg']);
                $agi->exec('Festival',$message);
                $agi->hangup();
                exit();
            }
            $info=$result_data['result'];
            if($info['student']){
                $agi->exec('Festival',"Nombre\ ".$this->converterTXT($info['info']['NOMBRE_FA'])."\ ".$this->converterTXT($info['info']['APELLIDOP_FA'])."\ ".$this->converterTXT($info['info']['APELLIDOM_FA']));
                $agi->exec('Festival',"Es\ tutor\ de");
                foreach($info['student'] as $data){
                    $agi->exec('Festival',$this->converterTXT($data['NOMBRE_A'])."\ ".$this->converterTXT($data['APELLIDOP_A']));
                }
                exit();
            }
            $agi->exec('Festival',"Nombre\ ".$this->converterTXT($info['info']['NOMBRE_A'])."\ ".$this->converterTXT($info['info']['APELLIDOP_A'])."\ ".$this->converterTXT($info['info']['APELLIDOM_A']));
            $agi->exec('Festival',"Unidad\ Academica\ ".$this->converterTXT($info['info']['NOMBRE_UA']));
            $agi->exec('Festival',"Turno\ ".$this->converterTXT($info['info']['TURNO_CUR']));
            $agi->exec('Festival',"Curso\ ".$this->converterTXT($info['info']['CURSO_NAME']));
            exit();

        }else{
            $agi->exec('Festival',"Error\ del\ servidor\ interno");
            $agi->hangup();
        }
    }

    //obtener conferencias del mes
    public function conference(){
        $agi = new AGI();
        $token = $_SERVER['argv'][2];

        $data = array(
            'token' => $token
        );

        $jsonData = json_encode($data);
        $result = server::post($jsonData,'controlador/admin/ApiController.php?op=conference-month');
    
        if($result){
            $result_data = json_decode($result,true);
            if($result_data['status'] == 'error'){
                $message=$this->converterTXT($result_data['result']['error_msg']);
                $agi->exec('Festival',$message);
                $agi->hangup();
                exit();
            }
            $conference=$result_data['result']['conference'];
            if(sizeof($conference) > 0){
            $agi->exec('Festival',"Las\ conferencias\ programadas\ de\ este\ mes\ son");
                foreach($conference as $data){
                    $agi->exec('Festival',"Conferencia\ ".$this->converterTXT($data['TITULO_AG']));
                    $agi->exec('Festival',"Empieza\ el\ ".$this->converterTXT($this->converterDate($data['START_AG'])));
                    $agi->exec('Festival',"A\ horas\ ".$this->converterTXT($this->converterTime($data['START_AG'])));
                    $agi->exec('Festival',"Termina\ el\ ".$this->converterTXT($this->converterDate($data['END_AG'])));
                    $agi->exec('Festival',"A\ horas\ ".$this->converterTXT($this->converterTime($data['END_AG'])));
                    $agi->exec('Festival',"Numero\ de\ sala\ ");
                    $agi->exec('SayDigits',$this->converterTXT($data['SALA_AG']));
                }
                exit();
            }
            $agi->exec('Festival',"No\ tiene\ conferencias\ programadas\ para\ este\ mes");
            exit();
        }else{
            $agi->exec('Festival',"Error\ del\ servidor\ interno");
            $agi->hangup();
        }
    }

    //obtener conferencias del mes
    public function conference_join(){
        $agi = new AGI();
        $sala = $_SERVER['argv'][2];

        $data = array(
            'sala' => $sala
        );

        $jsonData = json_encode($data);
        $result = server::post($jsonData,'controlador/admin/ApiController.php?op=conference-s');
    
        if($result){
            $result_data = json_decode($result,true);
            if($result_data['status'] == 'error'){
                $message=$this->converterTXT($result_data['result']['error_msg']);
                $agi->exec('Festival',$message);
                $agi->hangup();
                exit();
            }

            if($result_data['result']['tipo'] == 'error' || $result_data['result']['tipo'] == 'timeout' ){
                $message=$this->converterTXT($result_data['result']['result']);
                $agi->exec('Festival',$message);
                $agi->set_variable(conference,"FALSE");
                exit();
            }

            $agi->set_variable(conference,"TRUE");
            exit();
        }else{
            $agi->exec('Festival',"Error\ del\ servidor\ interno");
            $agi->hangup();
        }
    }

    //convertir a un texto legible para festival
    private function converterTXT($text){
        $search = array("á", "é", "í", "ó", "ó", "Á", "É", "Í", "Ó", "Ú", "ñ", "Ñ", "-", ",", ".", ":");
        $replace = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "ni", "NI", "", "", "", "");
        $response = str_replace($search, $replace, $text);
        $response = str_replace(" ", "\ ", $response);
        return $response;
    }

    private function converterDate($date){
        setlocale(LC_TIME, "spanish");
        $values=explode(' ', $date);
        $date = str_replace("/", "-", $values[0]);			
        $new_date = date("d-m-Y", strtotime($date));				
        $month_year = strftime("%A, %d de %B de %Y", strtotime($new_date));
        return $month_year;
    }

    private function converterTime($date){
        setlocale(LC_TIME, "spanish");
        $valores=explode(' ', $date);
        $valores=explode(':', $valores[1]);
        $time = $valores[0].' y '.$valores[1];			
        return $time;
    }
}

$option = $_SERVER['argv'][1];
$ins_academico = new academico();

switch($option){
    case 1:
        $ins_academico->login();
    break;
    case 2:
        $ins_academico->qualification();
    break;
    case 3:
        $ins_academico->information();
    break;
    case 4:
        $ins_academico->conference();
    break;
    case 5:
        $ins_academico->conference_join();
    break;
}

?>
