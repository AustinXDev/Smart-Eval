<?php 
namespace App\Services\RegistrationService;


class RegistrationEmail{

  public static function build(string $studentName, string $activationLink): string
    {
        return "
        <div style='max-width: 480px; margin: 0 auto; padding: 2.5rem 2rem; font-family: -apple-system, Helvetica, Arial, sans-serif; color: #1a1a1a;'>

          <p style='font-size: 0.95rem; margin: 0 0 1rem;'>Hi " . ($studentName ?? 'Student') . ",</p>

          <p style='font-size: 0.95rem; line-height: 1.5; margin: 0 0 1.5rem; color: #4a4a4a;'>
            Welcome to SMART-EVAL! Click the button below to activate your account and get started.
          </p>

          <a style='display: inline-block; background: #5e17eb; color: #ffffff; padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; border-radius: 0.375rem;' href='{$activationLink}'>
            Activate Account
          </a>
          
          <p style='font-size: 0.8rem; line-height: 1.5; margin: 2rem 0 0; color: #8a8a8a;'>
            If you didn't create an account with SMART-EVAL, you can safely ignore this email.
          </p>

        </div>";
    }

}

?>