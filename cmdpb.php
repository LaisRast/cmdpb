<?php

/**
 * cmdpb: A simple private command-line pastebin that uses HTTP basic
 * authentication and MySQL.
 */

function isSecure() {
    /* check if the connection is secture (https) */
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

function url() {
    /* return url of the current page */
    $protocol = isSecure()? "https": "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// set content-type to plain text
header("Content-Type: text/plain");

// check https
if (!isSecure()){echo "please use https\n"; exit; }

// secrets
include 'secrets.php';

// authenticate
if (!isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW']) ||
    ($_SERVER['PHP_AUTH_USER'] !== $USERNAME) ||
    ($_SERVER['PHP_AUTH_PW'] !== $PASSWORD)) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must login first\n";
    exit;
}

// connect to database
try {
    $conn = new PDO("mysql:host=$DBSERVER;dbname=$DBNAME", $DBUSER, $DBPASS);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// create table if does not exist
$conn -> query("CREATE TABLE IF NOT EXISTS " . $TABLE_NAME . " (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content LONGTEXT NOT NULL
)");

/* Requests */
if (isset($_GET["id"])) {
    $id  = $_GET["id"];
    // GET
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $stmt = $conn->prepare("SELECT * FROM ". $TABLE_NAME . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() != 1){
            header("HTTP/1.0 404 Not Found");
            echo "id " . $id . " does not exist\n";
        } else {
            $one = $stmt->fetch();
            echo $one["content"];
        }
    }
    // DELETE
    elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        $stmt = $conn->prepare("DELETE FROM " . $TABLE_NAME . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            header("HTTP/1.0 404 Not Found");
            echo "id " . $id . " does not exist\n";
        } else {
            echo "id " . $id . " has been deleted\n";
        }
    }
    // UPDATE
    elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["c"])) {
            $content = $_POST["c"];
        } elseif (isset($_FILES["c"])) {
            $content = file_get_contents($_FILES['c']['tmp_name']);
        } else {
            echo "please provide a value for the field c\n";
            exit;
        }
        $stmt = $conn->prepare("UPDATE " . $TABLE_NAME . " SET content = :content WHERE id = :id");
        $stmt->execute(array(":content" => $content, ":id" => $id));
        if ($stmt->rowCount() != 1) {
            echo "id " . $id . " did not change or does not exist\n";
        } else {
            echo "id " . $id . " has been updated\n";
        }
    }
    exit;
}

// INDEX
if ($_SERVER["REQUEST_METHOD"] == "GET"){
    $query = $conn->query("SELECT * FROM " . $TABLE_NAME . " ORDER BY id ASC");
    foreach($query->fetchAll() as $row) {
        echo url() . "?id=" . $row["id"] . "\n";
        $content = substr($row["content"], 0, 100);
        if (substr($content, -1) != "\n") { $content .= "\n"; }
        echo "\t" . str_replace("\n", "\n\t", $content) . "\n";
    }
    exit;
}

// POST
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST["c"])) {
        $content = $_POST["c"];
    } elseif (isset($_FILES["c"])) {
        $content = file_get_contents($_FILES['c']['tmp_name']);
    } else {
        echo "please provide a value for the field c\n";
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO " . $TABLE_NAME . " (content) VALUES (:content)");
    $stmt->bindParam(':content', $content);
    $stmt->execute();
    echo url() . "?id=" . $conn->lastInsertId() . "\n";
    exit;
}
