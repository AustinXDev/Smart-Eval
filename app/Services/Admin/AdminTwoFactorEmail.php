<?php 

namespace App\Services\Admin;

class AdminTwoFactorEmail
{
  
  public static function build(
    string $username,
    string $code,
    int $minutes
  ): string {

    return "
      <div style='
            max-width:480px;
            margin:0 auto;
            padding:32px;
            font-family:Arial,sans-serif;
            color:#1a1a1a;
        '>

            <p>Hello {$username} </p>

            <p>
                Use the verification code below to complete
                your Smart-Eval login:
            </p>

            <div style='
                margin:24px 0;
                padding:16px;
                text-align:center;
                background:#f5f5f5;
                border-radius:8px;
                font-size:32px;
                font-weight:bold;
                letter-spacing:8px;
            '>
                {$code}
            </div>

            <p>
                This code will expire in
                " . $minutes . " minutes.
            </p>

            <p style='color:#777;'>
                If you did not request this code,
                please secure your account immediately.
            </p>

        </div>
    ";

  }

}

?>