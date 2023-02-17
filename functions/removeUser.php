<?php include("../config.php") ?>
<?php
function removeUser(){
    global $conn;
    if($_SERVER["REQUEST_METHOD"] == "DELETE"){
        if(!isset($_SERVER["HTTP_AUTHORIZATION"])){
        http_response_code(401);
        $message = "Unauthorized";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }
        $get_id = $_SERVER["HTTP_AUTHORIZATION"];
        $id = explode(" ", $get_id);
        $user_id = $id[1];
        $sql = "SELECT * FROM  `roles` WHERE `user_id` = ? LIMIT 1";
        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, "i", $user_id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $role = mysqli_fetch_assoc($stmt_result);
        if(mysqli_num_rows($stmt_result) != 1){
        http_response_code(401);
        $message = "Unauthorized";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }
        $user_policies = $role["policies"];
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
        if(!in_array("can-delete-user", $policy_array)){
        $message = "Unauthorized";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }
        $get_user_id = explode("/", $_SERVER["REQUEST_URI"]);
        if(!isset($get_id[2])){
        http_response_code(400);
        $message = "Bad request";
        $response = array("status" => "Fail", "message" => $message);
        return $response; 
        }
        $user_to_delete = $get_user_id[2];
        $sql = "SELECT * FROM `users` WHERE id=?";
        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, "i", $user_to_delete);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);
        if(mysqli_num_rows($result) != 1){
        http_response_code(404);
        $message = "No user with matching id";
        $response = array("status" => "Fail", "message" => $message);
        return $response;   
        }
        $delete_sql = "DELETE FROM `users` WHERE id=?";
        $delete_query = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_query, "i", $user_to_delete);
        mysqli_stmt_execute($delete_query);
        if(!$delete_query){
          http_response_code(500);
        $message = "Something went wrong";
        $response = array("status" => "Fail", "message" => $message);
        return $response;     
        }
        http_response_code(200);
        $message = "User successfully deleted";
        $response = array("status" => "Success", "message" => $message);
        return $response;
    }
}