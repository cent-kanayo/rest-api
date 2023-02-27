<?php include("../config.php"); ?>
<?php 

function getCategories() {
     global $conn;
      $sql = "SELECT * FROM categories";
        $query = mysqli_query($conn, $sql);
        $categories = mysqli_fetch_all($query, MYSQLI_ASSOC);
        if(!$query){
        http_response_code(500);
        $message =  "Something went wrong, try again";
        $response = array("status" => "Fail", "message" => $message);
        return $response; 
        }
        if(count($categories) < 1){
        http_response_code(404);
        $message =  "No categories yet";
        $response = array("status" => "Fail", "message" => $message);
        return $response;
        }
        http_response_code(200);
        $response = array("status" => "Success", "data" => $categories);
        return $response;
}