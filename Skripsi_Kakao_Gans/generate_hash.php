<?php
// Tentukan password yang Anda inginkan (misalnya: 'secretadmin123')
$plaintext_password = 'admin123'; 

// Generate hash menggunakan algoritma BCRYPT (standar yang aman)
$hashed_password = password_hash($plaintext_password, PASSWORD_DEFAULT);

echo "<h2>Password Hashing Result</h2>";
echo "<strong>Password Plaintext Anda:</strong> " . $plaintext_password . "<br><br>";
echo "<strong>Password HASH yang siap dipakai:</strong> <br>";
echo "<textarea rows='5' cols='60' readonly>" . $hashed_password . "</textarea><br><br>";
echo "<em>Copy seluruh teks di atas (termasuk karakter \$ dan spasi) dan tempelkan ke database Anda.</em>";
?>
