<?php include("../config.php"); ?>
<?php
function addComment() {
    if($_SERVER["REQUEST_METHOD"] = "POST"){
    global $conn;
    if(!isset($_SERVER["HTTP_AUTHORIZATION"])){
        return "Unauthorized";
    };
    $get_id = $_SERVER["HTTP_AUTHORIZATION"];
    $id = explode(" ", $get_id);
    $user_id = $id[1];
    $user_sql = "SELECT * FROM users WHERE id=? LIMIT 1";
    $user_query = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_query, 'i', $user_id);
    mysqli_stmt_execute($user_query);
    $stmt_user_result = mysqli_stmt_get_result($user_query);
    if(mysqli_num_rows($stmt_user_result) != 1){
        http_response_code(401);
        $message = "Unauthorized User";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    }
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    $comment = $data->comment;
    $blog_id = $data->blogId;
    if(!$comment){
        http_response_code(400);
        $message = "All fields are required";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    }
    $comment = esc($data->comment);
    $blog_id = esc($data->blogId);
    $sql = "INSERT INTO comments (`comment`, `author`, `blog_id`) VALUES (?,?,?)";
    $query = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($query, "sii", $comment, $user_id, $blog_id);
    mysqli_stmt_execute($query);
    if(!$query){
        http_response_code(500);
        $message = "Something went wrong, try again";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
    }
        http_response_code(201);
        $message = "Comment added created successfully";
        $response = array("status" => "Success", "message" => $message);
        return $response;
}else{
        http_response_code(400);
        $message = "Bad request";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
}
}

function esc(String $value)
	{	
		// bring the global db connect object into function
		global $conn;

		$val = trim($value); // remove empty space sorrounding string
		$val = mysqli_real_escape_string($conn, $value);
        return $val;

    }