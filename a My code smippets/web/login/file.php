<?php
    $current_file = $_SERVER['SCRIPT_FILE'];
    require("core.inc.php");
    
    if(isset($_POST['submit'])){
      
        //prepare a query
        $query = "INSERT INTO avalues (name, sname) VALUES (?, ?)";
        
        if($prepair = $mysqli->prepare($query)){
            
            $bind = $prepair->bind_param("ss", $name, $sname);
            
            $name = $_POST['name'];
            $sname = $_POST['sname'];
            
            if($prepair->execute()){
            echo"success";
            }
             // Close query
            $prepair->close();
            
        } else{
            echo "ERROR: Could not prepare query: $sql. " . $mysqli->error;
        }

        // Close connection
        $mysqli->close();
    }
?>

<html>
    <head>
        
    </head>
    <body>
        <form action="<?php $current_file;?>" method="POST">
                <table>
                    <tr>
                        <td>name<td>
                        <td><input type="text" name="name"><td>
                    </tr>
                    <tr>
                        <td>second name<td>
                        <td><input type="text" name="sname"><td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" name="submit"></tr>
                    </tr>
                </table>
            </div>
        </form>
    </body>
</html>