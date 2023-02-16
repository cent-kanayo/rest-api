<?php include("../config.php") ?>
<?php 
function registerUser (){
    global $conn;

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $sql = "SELECT * FROM users";
        $query = mysqli_query($conn, $sql);
        $result = mysqli_fetch_all($query, MYSQLI_ASSOC);
        if(count($result) < 1){ 
                $data = file_get_contents("php://input");
                $data = json_decode($data);
                $email = $data->email;
                $password = $data->password;
                $cpassword = $data->cpassword;
                $lname = $data->lname;
                $fname = $data->fname;
                if(!$email || !$cpassword || !$password || !$lname || !$fname){
                    http_response_code(400);
                    $message = "All fields are required";
                    $response = array("status" => "Fail", "message" => $message);
                    return $response;
                }
                $email = esc($data->email);
                $password = esc($data->password);
                $cpassword = esc($data->cpassword);
                $lname = esc($data->lname);
                $fname = esc($data->fname);
                $role = "Super-admin";
                if($password != $cpassword){
                    http_response_code(400);
                    $message = "Password mismatch";
                    $response = array("status" => "Fail", "message" => $message);
                    return json_encode($response);
                }
                $password = md5($password);
                $sql = "INSERT INTO users (`email`, `password`, `role`, `lname`, `fname`) VALUES (?,?,?,?,?)";
                $query = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($query, "sssss", $email, $password, $role, $lname, $fname);
                mysqli_stmt_execute($query);
            if($query){
                $user_id = mysqli_insert_id($conn);
                $sql = "SELECT `id`, `email`, `role` FROM users WHERE id= ? LIMIT 1";
                $getUser = mysqli_prepare($conn, $sql); 
                mysqli_stmt_bind_param($getUser, "i", $user_id);
                mysqli_stmt_execute($getUser);
                http_response_code(201);
                $message = "User Super admin user created";
                $response = array("status" => "Fail", "message" => $message, "data" => $result);
                return json_encode($response);
            }else{
                $message = "Something went wrong try again";
                $response = array("status" => "Fail", "message" => $message);
                return $response;
            }
        }else{
            $data = file_get_contents("php://input");
            $data = json_decode($data);
             $password = $data->password;
            $cpassword = $data->cpassword;
            $email = $data->email;
            $lname = $data->lname;
            $fname = $data->fname;

             if(!$email || !$cpassword || !$password || !$lname || !$fname){
                    http_response_code(400);
                    $message = "All fields are required";
                    $response = array("status" => "Fail", "message" => $message);
                    return $response;
            }

            $password = esc($data->password);
            $cpassword = esc($data->cpassword);
            $email = esc($data->email);
            $lname = esc($data->lname);
            $fname = esc($data->fname);
            $sql = "SELECT * FROM users WHERE email= ?";
            $query = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($query, "s", $email);
            mysqli_stmt_execute($query);
            $getUser = mysqli_stmt_get_result($query);
            $result = mysqli_fetch_assoc($getUser);
            if(mysqli_num_rows($getUser) == 1){
                    http_response_code(400);
                    $message =  "User with this email already exists";
                    $response = array("status" => "Fail", "message" => $message);
                    return $response;
            }
            $role = "user";
            if($password != $cpassword){
                http_response_code(400);
                $message = "Password mismatch";
                $response = array("status" => "Fail", "message" => $message);
                return $response;
            }
           
            $password = md5($password);
            $sql = "INSERT INTO `users` (`email`, `password`, `role`, `lname`, `fname`) VALUES (?,?,?,?,?)";
            $query = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($query, "sssss", $email, $password, $role, $lname, $fname);
            mysqli_stmt_execute($query);
            if($query){
                $user_id = mysqli_insert_id($conn);
                $sql = "SELECT `id`, `email`, `role` FROM users WHERE id=? LIMIT 1";
                $user_result = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($user_result, "i", $user_id);
                mysqli_stmt_execute($user_result);
                $stmt_user_result = mysqli_stmt_get_result($user_result);
                $result = mysqli_fetch_assoc($stmt_user_result);
                http_response_code(201);
                $message = "User created successfully";
                $response = array("status" => "Success", "message" => $message, "data" => $result);
                return $response;
                
            } 

     }
    }

        $message = "Bad request method";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    
    }


function loginUser () {
    global $conn;
    if($_SERVER["REQUEST_METHOD"]  == "POST"){
        $data = file_get_contents("php://input");
        $data = json_decode($data);
         $email = $data->email;
         $password = $data->password;
         if(!$email || !$password){
                http_response_code(400);
                $message = "All fields are required";
                $response = array("status" => "Fail", "message" => $message);
                return $response;
         }
        $email = esc($data->email);
        $password = esc($data->password);
        $sql = "SELECT * FROM `users` WHERE `email`=? LIMIT 1";
        $result = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($result, "s", $email);
        mysqli_stmt_execute($result);
        $stmt_result = mysqli_stmt_get_result($result);
        $user = mysqli_fetch_assoc($stmt_result);
        
        if(mysqli_num_rows($stmt_result) == 1){
            if($user["password"] != md5($password)){
                $message = "Invalid Credentials";
                $response = array("status" => "Fail", "message" => $message);
                return $response;
            }
            $user_sql = "SELECT `email`, `id`, `role` FROM `users` WHERE `email`= ? LIMIT 1";
             $user_result = mysqli_prepare($conn, $user_sql);
             mysqli_stmt_bind_param($user_result, "s", $email);
             mysqli_stmt_execute($user_result);
             $stmt_user_result = mysqli_stmt_get_result($user_result);
             $user_detail = mysqli_fetch_assoc($stmt_user_result);
             http_response_code(200);
                $message = "User login successfully";
                $response = array("status" => "Success", "message" => $message, "data" => $user_detail);
                return $response;
        }
                $message = "Something went wrong try again";
                $response = array("status" => "Fail", "message" => $message);
                return $response;
    }
        $message = "Bad request method";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
}

function esc(String $value)
	{	
		// bring the global db connect object into function
		global $conn;

		$val = trim($value); // remove empty space sorrounding string
		$val = mysqli_real_escape_string($conn, $value);

		return $val;
	}