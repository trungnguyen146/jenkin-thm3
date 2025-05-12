<?php
// del.php
require 'db_con.php';


// delete function
function deleteAllRows($tableName, PDO $pdo) {
    $sql = "DELETE FROM " . $tableName;
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute();
        echo "Done";
    } catch (PDOException $e) {
        echo "Error" . $e->getMessage() . "\n";
    }
}

// Delete in products
deleteAllRows("products", $pdo);

//Back to index.php
header('Location: index.php');
exit
?>
