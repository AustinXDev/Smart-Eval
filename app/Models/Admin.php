<?php 

namespace App\Models;

class Admin 
{
  
  public function __construct(
    public int $adminId,
    public string $username,
    public string $email,
    public string $role,
    public string $passwordHash
  )
  {
  }

  public static function fromArray( 
    array $data
  ): self {

    return new self(
      (int) $data['admin_id'],
      $data['username'],
      $data['email'],
      $data['role'],
      $data['password_hash']
    );

  }

}
?>