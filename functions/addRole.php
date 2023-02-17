<?php include("../config.php") ?>
<?php  

function addRole(){
global $conn;
$message = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
if(!isset($_SERVER["HTTP_AUTHORIZATION"])){
        http_response_code(401);
        $message = "Unauthorized";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    };
    $get_id = $_SERVER["HTTP_AUTHORIZATION"];
    $id = explode(" ", $get_id);
    $user_id = $id[1];

    $user_sql = "SELECT * FROM `roles` WHERE `user_id`=? LIMIT 1";
    $query = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($query, "i", $user_id);
    mysqli_stmt_execute($query);
    $stmt_result = mysqli_stmt_get_result($query);
    $user_result = mysqli_fetch_assoc($stmt_result);
    if(mysqli_num_rows($stmt_result) != 1){
        http_response_code(401);
        $message = "Unauthorized User";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    }
    $user_policies = $user_result["policies"];
    $user_policies = json_decode($user_policies);
    $policy_array = [];
    for($i = 0; $i < count($user_policies); $i++){
    $priv_sql = "SELECT * FROM `policies` WHERE id=?";
    $policy_query = mysqli_prepare($conn, $priv_sql);
    mysqli_stmt_bind_param($policy_query, "i", $user_policies[$i]);
    mysqli_stmt_execute($policy_query);
    $stmt_policy_result = mysqli_stmt_get_result($policy_query);
    $policy_result = mysqli_fetch_assoc($stmt_policy_result);
    array_push($policy_array, $policy_result["privileges"]);
    }
    
    if(!in_array("can-create-admin", $policy_array)){
        $message = "Unauthorized";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    }

        $data = json_decode(file_get_contents("php://input"));
        $user_id = $data->user_id;
        $policies = $data->policies;
        $name = $data->name;
        if(!$user_id || !$policies || !$name){
            $message = "All fields are required";
            $response = array("status" => "Fail", "message" => $message );
            return $response;
        }
        if(!count($policies)){
            $message = "Policies must be assigned to user";
            $response = array("status" => "Fail", "message" => $message );
            return $response; 
        }

        $user_sql = "SELECT * FROM `roles` WHERE `user_id`=? LIMIT 1";
        $query = mysqli_prepare($conn, $user_sql);
        mysqli_stmt_bind_param($query, "i", $user_id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $user_result = mysqli_fetch_assoc($stmt_result);
        if(mysqli_num_rows($stmt_result) == 1){
        http_response_code(401);
        $message = "User already has a role";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }

        $policy_id_arrays = [];
        for($x = 0; $x < count($policies); $x++){
            $sql = "SELECT id FROM policies WHERE privileges=?";
            $policies_query = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($policies_query, "s", $policies[$x]);
            mysqli_stmt_execute($policies_query);
            $stmt_result = mysqli_stmt_get_result($policies_query);
            $policy_result = mysqli_fetch_assoc($stmt_result);
            array_push($policy_id_arrays, $policy_result["id"]);
        }
        $policy_id_arrays = json_encode($policy_id_arrays);
        $create_role_sql = "INSERT INTO roles (`user_id`, `policies`, `name`) VALUES (?, ?, ?)";
        $role_query = mysqli_prepare($conn, $create_role_sql);
        mysqli_stmt_bind_param($role_query, "iss", $user_id, $policy_id_arrays, $name );
        mysqli_stmt_execute($role_query);        
        if($role_query){
            $message = "Role successfully created";
            $response = array("status" => "Success", "message" => $message );
            return $response;    
        }else{
            $message = "Something went wrong, try again";
            $response = array("status" => "Failed", "message" => $message );
            return $response; 
        }
    
}

            $message = "Bad request, Not the accepted request method";
            $response = array("status" => "Fail", "message" => $message );
            return $response; 

}