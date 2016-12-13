<?php
    class Token
    {
        public function getRandomString(int $lenght)
        {
            return bin2hex(openssl_random_pseudo_bytes($lenght));
        }
    }

?>

