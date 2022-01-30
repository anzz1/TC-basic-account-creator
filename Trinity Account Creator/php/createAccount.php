<?php

  require_once(dirname(__FILE__) . '/db.php');

  $db = new db();
  
  if (!$db->isOpen()) {
    echo "2"; // Connection failed
    return;
  }
  
  // Get POST data and validate.
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
  }
  
  if (!isset($username) || !is_string($username) || empty($username)) {
    echo "3"; // Username is empty.
    return;
  }
  $username = validateInput($username);
  if (!isset($username)) {
    echo "4"; // Username is invalid.
    return;
  }
  if (strlen($username) > 32) {
    echo "5"; // Username is too long.
    return;
  }
  
  if (!isset($password) || !is_string($password) || empty($password)) {
    echo "6"; // Password is empty.
    return;
  }
  if (strlen($password) > 255) {
    echo "7"; // Password is invalid.
    return;
  }
  
  if (!isset($email)) {
    echo "8"; // Email is empty.
    return;
  }
  if (strlen($email) > 255) {
    echo "9"; // Email is invalid.
    return;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "9"; // Email is invalid.
    return;
  }

  try {
    
    // First, we need to check if the account name already exists.
    $accountCheckQuery = "SELECT * FROM account WHERE username = ?";
    $accountCheckParams = array($username);
    
    $results = $db->queryMultiRow($accountCheckQuery, $accountCheckParams);
    
    if ($db->getRowCount($results) > 0) {
      
      // Account already exists, inform user and stop transaction.
      echo "1";
      
      // Close connection to the database.
      $db->close();
      
      return;
    }
    
    // If no account exists, create a new one.
    
    // Get the SRP6 salt and verifier tokens
    list($salt, $verifier) = $db->getRegistrationData($username, $password);
    
    $accountCreateQuery = "INSERT INTO account(username, salt, verifier, email) VALUES(?, ?, ?, ?)";
    $accountCreateParams = array($username, $salt, $verifier, $email);
    
    // Execute the query.
    $db->insertQuery($accountCreateQuery, $accountCreateParams);
    
    // Close connection to the database.
    $db->close();
    
    // Return successful to AJAX call.
    echo "0"; // Account created successfully!
    
  }
  catch(PDOException $e) {
    echo "-1"; // Unknown error occured.
    error_log("Database error: " . $e->getMessage());
  }
  catch (Exception $e) {
    echo "-1"; // Unknown error occured.
    error_log("Unknown error: " . $e->getMessage());
  }
  
  // Validates POST input data.
  function validateInput($param) { 
    $valid = stripslashes($param);
    $valid = htmlspecialchars($valid, ENT_QUOTES);
    $valid = preg_replace('/\s+/', '', $valid);
    
    return ($param == $valid) ? $param : null;
  }

?>
