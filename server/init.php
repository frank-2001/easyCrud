<!-- EasyCrud Beta -->
<?php
    print_r("EasyCrud\n");
    
    // Complete by your db information
    $host='localhost';
    $dbname='jeb_easy';
    $user='root';
    $pass='';

    function connect(){
        try { 
            $bdd = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->user, $this->pass);
            return $bdd;
        }
        catch   (PDOException $pe){
            die ("I cannot connect to the database " . $pe->getMessage());
            return null;
        }
    }
    function listTable(){
        $sql="SHOW TABLES";
        $req= $this->connect()->prepare($sql);
        $req->execute();
        return $req->fetchAll();
    }

    // From here Don't change nothing
    if (!is_dir("classes")) {
        mkdir("classes");
        mkdir("controllers");
        $code='
            class bdd{
                var $host="'.$host.'";
                var $dbname="'.$dbname.'";
                var $user="'.$user.'";
                var $pass="'.$pass.'";
                function connect(){
                    try { 
                        $bdd = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->user, $this->pass);
                        return $bdd;
                    }
                    catch   (PDOException $pe){
                        die ("I cannot connect to the database " . $pe->getMessage());
                        return null;
                    }
                }
                function listTable(){
                    $sql="SHOW TABLES";
                    $req= $this->connect()->prepare($sql);
                    $req->execute();
                    return $req->fetchAll();
                }
            }
            $bdd=new bdd();

            class tables extends bdd{
                var $table;
                var $state=false;
                var $data=[];
                function return(String $sql,String $fun,Array $values=[]):array {
                    $req= $this->connect()->prepare($sql);
                    return ["message"=>$fun." ".$this->table,"state"=>$req->execute($values),"data"=>$req->fetchAll()];
                }
                function all():array{
                    $sql="SELECT * FROM ".$this->table;
                    return $this->return($sql,__FUNCTION__);
                }

                function new($data){
                    $keys=implode(",",array_keys($data));
                    $values=array_values($data);
                    $sign="";
                    for ($i=0; $i < count($data)-1 ; $i++) { 
                        $sign=$sign."?,";
                    }
                    $sign=$sign."?";
                    $sql = "INSERT INTO ".$this->table." (".$keys.") VALUES (".$sign.")";
                    return $this->return($sql,__FUNCTION__,$values);
                }
                function byId($id){
                    $sql="SELECT * FROM ".$this->table." where id=".$id;
                    return $this->return($sql,__FUNCTION__);
                }
                function update($id,$data){
                    $struc="";
                    foreach ($data as $key=>$value) { 
                        $struc=$struc."".$key."=\'".$value."\',";
                    }
                    $struc=substr($struc,0,-1);
                    print_r($struc."\n");
                    $sql="UPDATE ".$this->table." set ".$struc." where id=".$id;
                    return $this->return($sql,__FUNCTION__);      
                }
                function delete($id){
                    $sql="DELETE FROM ".$this->table." where id=".$id;
                    return $this->return($sql,__FUNCTION__); 
                }
                // Search in any table
                function search($data){
                    $demand="";
                    foreach ($data as $key => $value1) {
                        $demand.=" ".$key." LIKE \'%".$value1."%\' OR";
                        if ($key=="all_column") {
                            $demand="";
                            $sql="DESCRIBE ".$this->table;
                            foreach ($this->return($sql,"")["data"] as $key => $value2) {
                                $demand.=" ".$value2["0"]." LIKE \'%".$value1."%\' OR";
                            }
                            break;
                        }
                    }
                    $demand=substr($demand,0,-2);
                    $sql="SELECT * FROM ".$this->table." where ".$demand;
                    return $this->return($sql,__FUNCTION__);
                }
                function by($data,$param1="*",$param2=""){
                    $demand="";
                    foreach ($data as $key => $value1) {
                        $demand.=" ".$key." = \'".$value1."\' AND";
                    }
                    $demand=substr($demand,0,-3);
                    $sql="SELECT ".$param1." FROM ".$this->table." ".$param2." where ".$demand;
                    return $this->return($sql,__FUNCTION__);
                }
            }
    ';
    file_put_contents("classes/bdd.class.php",'<?php'.$code.'?>');
    echo "classes/bdd.php : connexion to your database \n";
    echo "classes.php : collect all classes from classes directory\n";
    file_put_contents("classes.php","<?php\nrequire 'classes/bdd.class.php';\n");
}

    require "classes/bdd.class.php";

    // Creates classes by the bdd
    echo "We found ".count($bdd->listTable())." tables in your database\n";
    foreach ($bdd->listTable() as $key) {
        $key=$key["0"];
        if (!file_exists("classes/".$key.".class.php")) {
            $class='
                class '.$key.' extends tables{        
                    public function __construct(){
                        $this->table="'.$key.'";
                    }
                }
                $'.$key.'=new '.$key.'(); 
            ';
            $controller='
                if (isset($_GET[$'.$key.'->table."-all"])) {
                    $output=$'.$key.'->All();
                }
                if (isset($_GET[$'.$key.'->table."-new"])) {
                    $output=$'.$key.'->new($_POST);
                }
                if (isset($_GET[$'.$key.'->table."-byId"])) {
                    $output=$'.$key.'->byId($_GET[$'.$key.'->table."-byId"]);
                }
                if (isset($_GET[$'.$key.'->table."-update"])) {
                    $output=$'.$key.'->update($_GET[$'.$key.'->table."-update"],$_POST);
                }
                if (isset($_GET[$'.$key.'->table."-delete"])) {
                    $output=$'.$key.'->delete($_GET[$'.$key.'->table."-delete"]);
                }
                if (isset($_GET[$'.$key.'->table."-search"])) {
                    $output=$'.$key.'->search($_POST);
                }
                if (isset($_GET[$'.$key.'->table."-by"])) {
                    $output=$'.$key.'->by($_POST);
                }
            ';
            mkdir("classes/$key");
            file_put_contents("classes/$key/class.php","<?php\n".$class);
            file_put_contents("classes/$key/controller.php","<?php\n".$controller);
            echo "classes/$key/class.php : Generated\n";
            echo "controllers/$key/class.php : Generated\n\n";

        }    
    }
    $requereDir = ["classes","controllers"];
    file_put_contents("requirement.php","<?php\n");
    foreach($requereDir as $dir){
        $files = glob($dir . "/*.php");
        foreach ($files as $file) {
            file_put_contents("requirement.php",file_get_contents("requirement.php")."\trequire_once  '".$file."';\n");
        } 
    }

    if (!file_exists("index.php")) {
        $code='
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            $output=array("message"=>"Aucune requete","state"=>true,"data"=>[]);
            require "classes/bdd.class.php";
            require "requirement.php";
            echo json_encode($output);
        ';
        file_put_contents("index.php","<?php\n".$code);
        echo "index.php : generated";
    }