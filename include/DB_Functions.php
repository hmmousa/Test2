<?php

class DB_Functions {
 
    private $conn;
 
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }
 
    // destructor
    function __destruct() {
         
    }
 
    /**
     * add new user
     * returns user details
     */
    public function storeUser($name, $email, $password, $street, $city, $state, $zip, $country) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
 
        $stmt = $this->conn->prepare("INSERT INTO users(UserName, Email, Password, Salt, Street, City, State, Zip, Country, JoinedDate, Status) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->bind_param("sssssssss", $name, $email, $encrypted_password, $salt, $street, $city, $state, $zip, $country);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            return $user;
        } else {
            return false;
        }
    }
	
	/**
     * add new product
     * returns product details
     */
    public function addProduct($name, $desc, $price, $category, $condition, $image, $soldBy) {

		$conditionID = 0;
		$categoryID = 0;
		
		//get the category ID
		$stmt = $this->conn->prepare("SELECT * FROM category WHERE CategoryName = ?");
 
        $stmt->bind_param("s", $category);
 
        if ($stmt->execute()) {
            $cat = $stmt->get_result()->fetch_assoc();
			
			$categoryID = $cat["CategoryID"];
			
            $stmt->close();
		}
		
		//get the condition ID
		$stmt = $this->conn->prepare("SELECT * FROM `condition` WHERE ConditionName = ?");
 
        $stmt->bind_param("s", $condition);
 
        if ($stmt->execute()) {
            $condi = $stmt->get_result()->fetch_assoc();
			
			$conditionID = $condi["ConditionID"];
			
            $stmt->close();
		}
		
        $stmt = $this->conn->prepare("INSERT INTO product(ProductName, PostedDate, Description, ConditionID, SoldBy, Price, Image, Status, CategoryID) VALUES(?, NOW(), ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("ssdddsd", $name, $desc, $conditionID, $soldBy, $price, $image, $categoryID);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful product
        if ($result) { 
            return true;
        } else {
            return false;
        }
    }
 
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
 
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE Email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
 
            // verifying user password
            $salt = $user['Salt'];
            $encrypted_password = $user['Password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }
	
	/**
     * Get categories
     */
    public function getCategories() {
 
        $query = "SELECT * FROM category";
 
		$result = $this->conn->query($query);
 
		while($row = $result->fetch_assoc())
		{
			$rows[] = $row;
		}
		
		return json_encode($rows);
    }
	
	/**
     * Get conditions
     */
    public function getConditions() {
 
        $query = "SELECT * FROM `condition`";
 
		$result = $this->conn->query($query);
 
		while($row = $result->fetch_assoc())
		{
			$rows[] = $row;
		}
		
		return json_encode($rows);
    }
	
	/**
     * Get products by category id
     */
    public function getProductsByCategoryID($CategoryName, $Search, $UserID) {
 
		/////////////////////////////////////////////////////////////////////////////////////
		//for customer
		$query = "SELECT City, State, Zip, Country from users where UserID = ".$UserID;		

		$result = $this->conn->query($query);
 
		$row = $result->fetch_assoc();
		
		$City = $row['City'];
		$State = $row['State'];
		$Zip = $row['Zip'];
		$Country = $row['Country'];
		
		/////////////////////////////////////////////////////////////////////////////////////
		
		$Search = '%'.$Search.'%';
		
        $query = "SELECT * FROM `product`, category, Users owner where `product`.CategoryID =  category.CategoryID 		
		and product.SoldBy = owner.UserID
		and category.CategoryName = '".$CategoryName."'".
		" and product.ProductName like '".$Search."'".	
		" and owner.City = '".$City."'".
		" and owner.State = '".$State."'".
		" and owner.Zip = '".$Zip."'".
		" and owner.Country = '".$Country."'";
 
		$result = $this->conn->query($query);
 
		$rows = [];
		
		while($row = $result->fetch_assoc())
		{
			$rows[] = $row;
		}
		
		return json_encode($rows);
    }
	
	/**
     * Get products by id
     */
    public function getProductByID($ProductID) {
 
 
        $query = "SELECT 
		`product`.`ProductID`,
		`product`.`ProductName`,
		`product`.`PostedDate`,
		`product`.`Description`,
		`product`.`ConditionID`,
		`product`.`SoldBy`,
		`product`.`Price`,
		`product`.`Image`,
		`product`.`Status`,
		`product`.`CategoryID`,
		`category`.`CategoryName`,
		users.UserName, `condition`.ConditionName		
		 FROM `product`, category, users, `condition`
		 where `product`.CategoryID =  category.CategoryID 
		 and product.SoldBy = users.UserID
		 and `product`.`ConditionID` = `condition`.ConditionID
		 and product.ProductID = ".$ProductID."";


		$result = $this->conn->query($query);
 
		$row = $result->fetch_assoc();
		
		return json_encode($row);
    }
 
    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT Email from users WHERE Email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }
 
    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    } 
}
 
?>