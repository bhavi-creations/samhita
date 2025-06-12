<?php

// This file is located at samhita/public/test_if.php

$testVariable = "put"; // This string simulates the method string

echo "Value of testVariable: " . $testVariable . "<br>";

// This is a basic 'if' statement test
if ($testVariable === "put") {
    echo "YES! The 'if' condition evaluated to TRUE.<br>";
} else {
    echo "NO! The 'if' condition evaluated to FALSE.<br>";
}

die("End of test."); // This stops PHP execution here

?>