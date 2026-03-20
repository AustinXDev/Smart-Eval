<?php 

function anonymousEmail($email) {
  if (empty($email) || !str_contains($email, '@')) {
        return 'N/A';
  }


  $parts = explode('@', $email);
  $local = $parts[0];
  $domain = $parts[1];
  $visible = substr($local, 0, 2);
  $hidden = str_repeat('*',  strlen($local) - 2);
  return $visible . $hidden . '@' . $domain;
}


?>