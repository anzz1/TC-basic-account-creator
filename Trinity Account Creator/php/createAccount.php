<?php

  require_once(dirname(__FILE__) . '/vars.php');
  require_once(dirname(__FILE__) . '/db.php');

  if (class_exists('db')) {
    $db = new db();
  }
  else {
    echo "-1"; // Unknown error occured.
    error_log("Error: Class db() could not be initialized.");
    return;
  }

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

  // username has 16 byte limit on TC server
  if (strlen($username) > 16) {
    echo "5"; // Username is too long.
    return;
  }

  if (!isset($password) || !is_string($password) || empty($password)) {
    echo "6"; // Password is empty.
    return;
  }

  // password has a 16 character limit on 3.3.5.12340 client even when SRP6 does not have such limitation
  if (strlen($password) > 64 || iconv_strlen($password, 'utf-8') > 16) {
    echo "7"; // Password is too long.
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

  $username = $db->strtoupper_az($username);
  $email = $db->strtoupper_az($email);

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

    $accountCreateQuery = "INSERT INTO account(username, salt, verifier, reg_mail, email) VALUES(?, ?, ?, ?, ?)";
    $accountCreateParams = array($username, $salt, $verifier, $email, $email);

    // Execute the query.
    $db->insertQuery($accountCreateQuery, $accountCreateParams);

    // Close connection to the database.
    $db->close();

    //error_log("Account created: '" . $username . "' '". $email . "'");

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
