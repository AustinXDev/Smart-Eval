<?php  

namespace App\Services\Admin\PasswordResetServices;

class ForgotPasswordEmail
{

  public static function build(
    string $username,
    string $link,
  ){

    return "
    
     <div style='max-width: 480px; margin: 0 auto; padding: 2.5rem 2rem; font-family: -apple-system, Helvetica, Arial, sans-serif; color: #1a1a1a;'>

          <p style='font-size: 0.95rem; margin: 0 0 1rem;'>Hi " . ($username ?? 'Admin') . ",</p>

          <p style='font-size: 0.95rem; line-height: 1.5; margin: 0 0 1.5rem; color: #4a4a4a;'>
            You requested to reset your password
          </p>

          <a style='display: inline-block; background: #5e17eb; color: #ffffff; padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; border-radius: 0.375rem;' href='{$link}'>
            Reset Password
          </a>

          <p style='font-size: 0.8rem; line-height: 1.5; margin: 2rem 0 0; color: #8a8a8a;'>
            If you didn't request this, you can safely ignore this email.
          </p>

      </div>

    ";

  }

}

?>