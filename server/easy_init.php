<?php 
    
    function db($dbname){
        $host="localhost";
        $user="root";
        $pass="";
        $pdo = new PDO("mysql:host=".$host.";dbname=mysql", $user, $pass);
        $sql="CREATE DATABASE IF NOT EXISTS ".$dbname;
        $req= $pdo->prepare($sql);
        $req->execute();        
        try { 
            $pdo = new PDO("mysql:host=".$host.";dbname=".$dbname, $user, $pass);
            print("Connected successfully to ".$dbname);
            $pwd=md5(time());
            $sql="CREATE USER 'public'@'localhost' IDENTIFIED BY '$pwd'";
            $req= $pdo->prepare($sql);
            $req->execute(); 
        }
        catch   (PDOException $pe){
            print("I cannot connect to the database " . $pe->getMessage()." Code ".$pe->getCode()."\n\n");
            if ($pe->getCode()==1049) {
                echo "Creation database\n";
                db("mysql");
            }
        }
    }
    db($argv[1]);
?>