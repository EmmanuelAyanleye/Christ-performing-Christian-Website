<?php
// Use this script to generate a secure password hash.
// Best to delete this file after you're done.

$passwordToHash = 'admin1';

// Generate the hash
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);

echo "Password: " . htmlspecialchars($passwordToHash) . "<br>";
echo "Hashed Password: <strong>" . htmlspecialchars($hashedPassword) . "</strong>";
?>
