<?php
class PhpBackgroundProcess{
    public function __construct(){
		// check index.php for using Run and pass param
		// check JobToRunInBg.php for get param pass from Run
    }
    
    public function __destruct(){

    }

    public function Run($phpFilePath,$param=null){
        if(is_null($param))$param="";

        $paramIn64string =base64_encode(serialize($param));
    
        $cmd="php ".$phpFilePath." ".$paramIn64string;
        //new BackgroundProcess($cmd);
        $command = 'nohup '.$cmd.' > /dev/null 2>&1 & echo $!';
        exec($command ,$pross);
        return (int)$pross[0];
    }

    public function ParseParam($agrvContext){        
        $arrParam=[];
        foreach($agrvContext as $a){
            if(is_string($a)) {array_push($arrParam,$a);}
        }
        $larr=count($arrParam);

        $deseriallize=null;
         
        if($larr>0){
            if($larr>1){
                $deseriallize= $agrvContext[1];
            }else{            
                $deseriallize= $agrvContext[0];
            }
            if(!is_null($deseriallize) && !empty($deseriallize) && $deseriallize!=""){
                try{
                    $deseriallize=unserialize(base64_decode( $deseriallize));
                }
                catch(Exception $ex){                    
                }                
            }
        }      
        
        return $deseriallize;
    }
}
?>
