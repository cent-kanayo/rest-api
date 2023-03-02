<?php include("../config.php") ?>
<?php 

$paths = explode("/", $_SERVER['REQUEST_URI']);
function getAllBlogs(){
    global $conn;
      $blog_sql = "SELECT * FROM blogs WHERE published=1";
        $blog_result = mysqli_query($conn, $blog_sql);
        $blogs = mysqli_fetch_all($blog_result, MYSQLI_ASSOC);
        $data = [];
        if(count($blogs) < 1){
        http_response_code(404);
        $message =  "No blogs yet";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }
        foreach($blogs as $blog){

            $user = getAuthor($blog["author"]);
            $blog["username"] = $user["fname"] . " " . $user["lname"];
            array_push($data, $blog);
        }
        http_response_code(200);
        $response = array("status" => "Success", "data" => $data);
        return $response;

}
// mysqli_close($conn);

function getSingleBlog() {
    global $conn;
    $paths = explode("/", $_SERVER['REQUEST_URI']);
    if(isset($paths[3])){
    $query_id = $paths[3];

    if(isset($_SERVER["HTTP_AUTHORIZATION"])){   
    $get_id = $_SERVER["HTTP_AUTHORIZATION"];
    $id = explode(" ", $get_id);
    $id = $id[1];
    $sql = "SELECT * FROM  roles WHERE id=? LIMIT 1";
    $query = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($query, 'i', $id);
    mysqli_stmt_execute($query);
    $stmt_result = mysqli_stmt_get_result($query);
    $role = mysqli_fetch_assoc($stmt_result);
    if($role["name"] == "Super-admin" || $role["name"] == "Editor-admin"){
        $sql = "SELECT * FROM blogs WHERE id=?";
        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, 'i', $query_id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $result = mysqli_fetch_assoc($stmt_result);
        if(!$query){
            $message =  "Something went wrong";
            http_response_code(500);
            $response = array("status" => "Fail", "message" => $message);
            return $response;
        }
        if(mysqli_num_rows($stmt_result) < 1){
            $message =   "No blog with matching id";
            $response = array("status" => "Fail", "message" => $message);
            return $response;
        }
            $user = getAuthor($result["author"]);
            $result["username"] = $user["fname"] . " " . $user["lname"];
            $comments = getBlogsComments($query_id);
            http_response_code(200);
            $response = array("status" => "Success", "blog" => $result, "comments" => $comments);
            return $response;


    }
       
}
        $sql = "SELECT * FROM blogs WHERE id= ? AND published=1 LIMIT 1";

        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, 'i', $query_id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $result = mysqli_fetch_assoc($stmt_result);
        if(!$query){
            $message =   "Something went wrong";
            $response = array("status" => "Fail", "message" => $message);
            return $response;
        }
        if(mysqli_num_rows($stmt_result) < 1){
            $message =   "Not authorized to this blog";
            $response = array("status" => "Fail", "message" => $message);
            return $response;
        }
            
            $user = getAuthor($result["author"]);
            $result["username"] = $user["fname"] . " " . $user["lname"];
            $comments = getBlogsComments($query_id);
            http_response_code(200);
            $response = array("status" => "Success", "blog" => $result, "comments" => $comments);
            return $response;
    }       
            http_response_code(404);
            $message = "Couldn't find the resource you are looking for";
            $response = array("status" => "Fail", "message" => $message);
            return $response;
}

function getAuthor($id){
   global $conn;
        $sql = "SELECT `fname`, `lname` FROM users WHERE id= ? ";
        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, 'i', $id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $result = mysqli_fetch_assoc($stmt_result);
        return $result;
}

function getBlogsComments ($id){
        global $conn;
        $sql = "SELECT `id`, `comment`, `author` FROM `comments` WHERE blog_id= ? ";
        $query = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($query, 'i', $id);
        mysqli_stmt_execute($query);
        $stmt_result = mysqli_stmt_get_result($query);
        $result = mysqli_fetch_all($stmt_result);
        if(!$query){
            http_response_code(500);
            $message =   "Something went wrong";
            $response = array("status" => "Fail", "message" => $message);
            return $response;
        }
        if(count($result) < 1){
            http_response_code(200);
            $message = "No comments found for this blog yet";
            $response = array("status" => "Fail", "message" => $message);
            return $response;  
        }
        $comments = [];

        foreach($result as $comment){
            $comment_author = getAuthor($comment["author"]);
            $comment["username"] = $comment_author["fname"] . " " . $comment_author["lname"];
            array_push($comments, $comment);
        }

        return $comments;
    }