<!-- EasyCrud Beta -->
<?php
    echo "EasyCrud <br>\n";
    
    // Complete by your db information
    $host='localhost';
    $dbname='easy_crud';
    $user='root';
    $pass='';


    $new_=(isset($_GET["new"]) || isset($argv[1]) && $argv[1]=="new");
    echo "operation : ";
    if ($new_) {
        echo "Reinitialisation...<br>\n";
    }else{
        echo "Update...<br>\n";
    }
    // From here Don't change nothing
    // Make bdd.class.bdd
    if (!is_dir("classes") || $new_) {
        if (!is_dir("classes")) {
            mkdir("classes");
        }
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
                function query($prompt){
                    $sql = $prompt;
                    $req = $this->connect()->prepare($sql);
                    $req->execute();
                    return $req->fetchAll();
                }
            }

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
                function tables(){
                    $sql="SHOW TABLES";
                    $req= $this->connect()->prepare($sql);
                    $req->execute();
                    return $this->return($sql,__FUNCTION__);
                }

                function uploader($image,$level,$prefix,$dir){
                    // Image
                    $newName = $prefix.".webp";
                    if(!is_dir($dir)){
                        mkdir($dir);
                    }
                    // Create and save
                    $imgInfo=getimagesize($image);
                    $mime=$imgInfo["mime"];
                    #Create a new image from file
                    switch ($mime) {
                        case "image/jpeg":
                            $img =imagecreatefromjpeg($image);
                            break;
                        case "image/png":
                            $img =imagecreatefrompng($image);
                            break;
                        case "image/gif":
                            $img =imagecreatefromgif($image);
                            break;
                        default:
                            $name = basename($image);
                            move_uploaded_file($image, "$dir.$name");
                            $output = [
                                "origin"=>[
                                    "path"=>$image,
                                    "size"=> " Mb"
                                ],
                                "final"=>[
                                    "path"=>$dir.$name,
                                    "size"=>" Mb",
                                    "name"=>$name
                                ],
                                "result"=>"File uploaded!"
                            ];
                            return $output;
                            break;
                    }
            
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    imagewebp($img, $dir . $newName, $level);
                    imagedestroy($img);
                    $origin=round(filesize($image)/(1024*1024),2);
                    $final=round(filesize($dir.$newName)/(1024*1024),2);
                    $red=$origin-$final;
                    $rate=($red*100)/$origin;
            
                    $output = [
                        "origin"=>[
                            "path"=>$image,
                            "size"=> $origin. " Mb"
                        ],
                        "final"=>[
                            "path"=>$dir.$newName,
                            "size"=>$final. " Mb",
                            "name"=>$newName
                        ],
                        "result"=>$red." Mb Reduced or ".$rate."%"
                    ];
                    return $output;
                }
            }
            $bdd=new bdd();
            if(isset($_GET["db-info"])){
                $output="'.$dbname.'";
            }
            if(isset($_GET["db-query"])){
                $output=$bdd->query($_POST["query"]);
            }
        ';
        file_put_contents("classes/bdd.class.php",'<?php'.$code.'?>');
        echo "classes/bdd.php : connexion to your database < $dbname > <br>\n ";
    }
    // Import Bdd
    require "classes/bdd.class.php";
    // Create an admin account
    if ($new_) {
        $bdd->query("
            CREATE TABLE IF NOT EXISTS easy_admin
            (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL,
                password VARCHAR(250) NOT NULL
            );
            TRUNCATE easy_admin;
            INSERT INTO easy_admin (username, password)
            VALUES ('admin', '".md5('admin')."');
        ");
    }
    // Creates classes by the bdd
    echo "We found ".count($bdd->listTable())." tables in your database <br>\n";
    function setMenu($tables){
        $menu="
            <div class=\"text-xl text-white h-16 p-4 bg-black\">Tables</div>
            <button onclick=\"lacrea_load('#app','apps/Home/')\" class=\"text-left p-4 bg-green-500 mx-4 rounded hover:bg-white focus:bg-white \">Home</button>\n
        ";
        echo "Tables :  <br>\n";
        foreach ($tables as $table) {
            
            $table=$table[0];
            $menu=$menu."<button class=\"text-left p-4 bg-green-500 mx-4 rounded hover:bg-white focus:bg-white \" onclick=\"lacrea_load('#app','apps/$table/')\">$table</button>\n";
            echo "\t>$table<br>\n";
        }
        $menu=$menu."<div class=\"mb-8\"></div>";
        if (!is_dir("../admin/apps/Menu")) {
            mkdir("../admin/apps/Menu");
        }
        file_put_contents("../admin/apps/Menu/index.html",$menu);
    }
    setMenu($bdd->listTable());
    foreach ($bdd->listTable() as $key) {
        $key=$key["0"];
        // Make class
        if (!is_dir("classes/$key") || $new_) {
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
                    $file_path="classes/'.$key.'/files/";
                    // Test if exist
                    foreach ($_FILES as $k => $v) {
                        // Recuperation names of files
                        $tmps=$_FILES[$k]["tmp_name"];
                        if (is_array($tmps)) {
                            $_POST[$k]="";
                            foreach ($tmps as $tmp) {
                                $_POST[$k].=$'.$key.'->uploader($tmp,40,time(),$file_path)["final"]["path"].",";
                            }
                        }else{
                            $_POST[$k]=$'.$key.'->uploader($tmps,40,time(),$file_path)["final"]["path"];
                        }
                    }
                    if(isset($_POST["password"])){
                        $_POST["password"]=md5($_POST["password"]);
                    }
                    if(isset($_POST["pass"])){
                        $_POST["pass"]=md5($_POST["pass"]);
                    }
                    $output=$'.$key.'->new($_POST);
                }
                if (isset($_GET[$'.$key.'->table."-byId"])) {
                    $output=$'.$key.'->byId($_GET[$'.$key.'->table."-byId"]);
                }
                if (isset($_GET[$'.$key.'->table."-update"])) {
                    if(isset($_POST["password"])){
                        $_POST["password"]=md5($_POST["password"]);
                    }
                    if(isset($_POST["pass"])){
                        $_POST["pass"]=md5($_POST["pass"]);
                    }
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
                if (isset($_GET[$'.$key.'->table."-connect"])) {
                    if(isset($_POST["password"])){
                        $_POST["password"]=md5($_POST["password"]);
                    }
                    if(isset($_POST["pass"])){
                        $_POST["pass"]=md5($_POST["pass"]);
                    }
                    $output=$'.$key.'->by($_POST);
                }
            ';
            if (!is_dir("classes/$key")) {
                mkdir("classes/$key");
            }
            if (!is_dir("../admin/apps/$key")) {
                mkdir("../admin/apps/$key");
            }
            file_put_contents("classes/$key/classes.php","<?php\n".$class);
            file_put_contents("classes/$key/controllers.php","<?php\n".$controller);
            file_put_contents("../admin/apps/$key/index.html",controler($key,$bdd->query("DESCRIBE $key")));
            file_put_contents("../admin/apps/$key/popup.html",popup($key,$bdd->query("DESCRIBE $key")));
            echo "Class : classes/$key : Generated <br>\n";
            echo "Controller : admin/apps/$key : Generated <br>\n";
        }
    }
    $requereDir = ["classes","controllers"];
    file_put_contents("requirement.php","<?php\n");
    foreach($requereDir as $dir){
        $files = glob("classes/*",GLOB_ONLYDIR);
        foreach ($files as $file) {
            file_put_contents("requirement.php",file_get_contents("requirement.php")."require_once  '$file/$dir.php';\n");
        } 
    }

    // Create index file
    if (!file_exists("index.php") || $new_) {
        $code='
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json");
            $output=array("message"=>"Aucune requete","state"=>true,"data"=>[]);
            require "classes/bdd.class.php";
            require "requirement.php";
            echo json_encode($output);
        ';
        file_put_contents("index.php","<?php\n$code");
        echo "index.php : generated<br>\n";
    }


    // Code controler of tables
    function controler($table,$cols){
        $strCol="<th class=\"p-2\">No</th>\n";
        foreach ($cols as $col) {
            $col=$col["Field"];
            $strCol=$strCol."<th>$col</th>\n";
        }
        return "
        <header class=\"text-xl h-16 text-white p-4 bg-black/70 flex justify-between items-center\">
        <span>$table</span>
        <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" class=\"w-8 h-8\" onclick=\"popup('.new_')\">
            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" />
          </svg>
        </header>
        <div class=\"w-full overflow-auto h-full\">
            <table class=\"w-full bg-white table-auto p-4 h-full \">
                <thead class=\"border-2\">
                    
                    <tr class=\"text-left\">
                        $strCol
                    </tr>
                </thead>
                <tbody class=\"\">
                    
                </tbody>
            </table>
        </div>
        <div class=\"h-20\"></div>  
        
        <script>
            function getData(){
                console.log(\"?$table-all\")
                $.get(\"../server/?$table-all\",
                    function (data, textStatus, jqXHR) {
                        $('tbody').html('');
                        data.data.forEach((e,c) => { 
                            dat=Object.values(e);
                            strDat=''
                            for (let i = 0; i < dat.length/2; i++) {
                                strDat+=`<td>\${dat[i]}</td>`;
                            }
                            $('tbody').append(`
                                    <tr class='bg-black/10 border-2 border-b-white py-4'>
                                        <td class='p-4'>\${c+1}</td> 
                                        \${strDat}   
                                        <td class='grid grid-cols-2 gap-1 content-center items-center justify-center h-full text-center'>
                                            <button onclick=\"popup('.edit_','\${e.id}','$table')\" class='bg-green-500 px-2 py-1 rounded text-white flex gap-1 text-center justify-center'>
                                                <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' class='w-6 h-6'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125' />
                                                </svg>
                                            </button>  
                                            <button onclick='deleteArticle(\${e.id})' class='bg-red-500 px-2 py-1 rounded text-white flex gap-1 text-center justify-center '>
                                                <svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' class='w-6 h-6'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0' />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                `
                            )

                        });  
                    },
                    \"json\"
                ).fail((e)=>{
                    console.log(e)
                    alert(\"Erreur : Nous n'avons pas pu envoyer la requete au serveur\")
                });
            }
            getData()
            function deleteArticle(id){
                if(confirm(\"Cet article sera definitivement supprimé !!\")){
                    $.get(\"../server/?$table-delete=\"+id,
                        function (data, textStatus, jqXHR) {
                            alert(\"$table supprimé(e) avec succes!\")
                        }
                    ).fail((e)=>{
                        console.log(e)
                        alert(\"Erreur : Nous n'avons pas pu envoyer la requete au serveur\")
                    }).always((e)=>{
                        getData()
                    })
                }
            }

            
            
        </script>
        ";
    }
    function popup($table,$cols){
        $updates="";
        foreach ($cols as $col) {
            $type="text";
            if (str_contains($col["Field"], 'image') || str_contains($col["Field"], 'fichier') || str_contains($col["Field"], 'file') || str_contains($col["Field"], 'photo')) {
                continue;
                $type="hidden";
            }
            elseif (str_contains($col["Type"], 'varchar')) {
                $type="text";
            }elseif(str_contains($col["Type"], 'double') || str_contains($col["Type"], 'int') || str_contains($col["Type"], 'float')){
                $type="number";
            }
            
            $requir="";
            if ($col["Key"]=="PRI") {
                continue;
                $type="hidden";
            }

            if(str_contains($col["Type"], 'text')){
                $updates.='
                    <fieldset class="grid bg-white/10 p-4">
                        <span class="">'.$col["Field"].'</span>
                        <textarea cols="100" rows="5" class="text-black p-2 rounded" placeholder="'.$col["Field"].'" name="'.$col["Field"].'" '.$requir.'></textarea>
                    </fieldset>
                ';
            }else{
                $updates.='
                    <fieldset class="grid bg-white/10 p-4">
                        <span class="">'.$col["Field"].'</span>
                        <input type="'.$type.'" class="text-black p-2 rounded" step="0.1" placeholder="'.$col["Field"].'" name="'.$col["Field"].'" '.$requir.'>
                    </fieldset>
                ';
            }            
        }

        $news="";
        $requir="";
        foreach ($cols as $col) {
            $type="text";
            if (str_contains($col["Field"], 'image') || str_contains($col["Field"], 'fichier') || str_contains($col["Field"], 'file') || str_contains($col["Field"], 'photo')) {
                $type="file";
                if (str_contains($col["Field"], 'images') || str_contains($col["Field"], 'fichiers') || str_contains($col["Field"], 'files') || str_contains($col["Field"], 'photos')) {
                    $col["Field"].="[]";
                    $requir="multiple";
                }
            }
            elseif (str_contains($col["Type"], 'varchar')) {
                $type="text";
            }elseif(str_contains($col["Type"], 'double') || str_contains($col["Type"], 'int') || str_contains($col["Type"], 'float')){
                $type="number";
            }
            
            
            if ($col["Null"]=="NO") {
                $requir="required";
            }
            if ($col["Key"]=="PRI") {
                continue;
            }

            if(str_contains($col["Type"], 'text')){
                $news.='
                    <fieldset class="grid bg-white/10 p-4">
                        <span class="">'.$col["Field"].'</span>
                        <textarea cols="100" rows="5" class="text-black p-2 rounded" placeholder="'.$col["Field"].'" name="'.$col["Field"].'" '.$requir.'></textarea>
                    </fieldset>
                ';
            }else{
                $news.='
                    <fieldset class="grid bg-white/10 p-4">
                        <span class="">'.$col["Field"].'</span>
                        <input type="'.$type.'" class="text-black p-2 rounded" step="0.1" placeholder="'.$col["Field"].'" name="'.$col["Field"].'" '.$requir.'>
                    </fieldset>
                ';
            }            
        }
        return '
            
            <form action="/" enctype="multipart/form-data" method="post" class="edit_ grid grid-cols-3 gap-5 bg-green-900 p-8 text-white rounded hidden">
                <span class="text-xl text-center mb-2 col-span-3"> Mis à jour </span>
                '.$updates.'
                <button class="border-2 border-white p-2 rounded hover:bg-yellow-500 col-span-3">Valider</button>
            </form>
            
            <form action="" class="w-full hidden new_ grid grid-cols-3 gap-2 bg-green-900 text-white p-8 rounded">
                <span class="text-xl text-center mb-2 col-span-3"> Nouvel article</span>
                '.$news.'
                <button class="border-2 border-white p-2 rounded hover:bg-yellow-500 col-span-3">Valider</button>
            </form>
            <script>
                $("#popup>.pop>.new_").submit(function (e) { 
                    e.preventDefault();
                    form = new FormData(this)
                    alert("Operation en cours")
                    popup()
                    $.ajax({
                        type: "POST",
                        url: "../server/?'.$table.'-new",
                        data: form,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            alert("'.$table.' posté(e) avec succes!")
                            getData()
                        },fail:function (er) { 
                            alert("Erreur : '.$table.' non posté(e)")
                        }
                    });
                });
                
                updated=[]
                $(".edit_>*>*").keydown(function (e) { 
                    if (!updated.includes(e.target.name)) {
                        updated.push(e.target.name)   
                    }
                });

                $("#popup>.pop>.edit_").submit(function (e) { 
                    e.preventDefault();
                    let form = new FormData(this)
                    let delets=[]
                    for(var i of form.entries()){
                        if(!updated.includes(i[0])){
                            delets.push(i[0])
                        }
                    }
                    delets.forEach(d => {
                        form.delete(d)
                    });
                    $.post("../server/?'.$table.'-update="+db.get(\'id_edit\'), formToDic(form),
                        function (data, textStatus, jqXHR) {
                            alert("'.$table.' updated avec succes!")
                        },
                        ""
                    ).fail(err=>{
                        alert("Erreur : '.$table.' non updated")
                    }).always(r=>{
                        getData()
                        popup()
                    });
                });
            </script>

        ';
    }
    
    