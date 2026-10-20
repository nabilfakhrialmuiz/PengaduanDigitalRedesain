<?php
function generate_two_factor_code($length = 6) {
    // Generate a cryptographically secure random code
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= random_int(0, 9);
    }
    return $code;
}