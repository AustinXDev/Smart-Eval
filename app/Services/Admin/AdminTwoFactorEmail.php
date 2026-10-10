<?php 

namespace App\Services\Admin;

class AdminTwoFactorEmail
{
  
  public static function build(
    string $username,
    string $code,
    int $minutes
  ): string {
    $escapedUsername = htmlspecialchars(
      $username,
      ENT_QUOTES,
      'UTF-8'
    );
    $escapedCode = htmlspecialchars(
      $code,
      ENT_QUOTES,
      'UTF-8'
    );

    return "
      <!DOCTYPE html>
      <html lang=\"en\">
      <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <meta name=\"x-apple-disable-message-reformatting\">
        <title>Smart-Eval Admin Verification Code</title>
      </head>
      <body style=\"margin:0; padding:0; background-color:#F3F4F6; font-family:Arial, Helvetica, sans-serif; color:#16213E;\">
        <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-collapse:collapse; background-color:#F3F4F6;\">
          <tr>
            <td align=\"center\" style=\"padding:32px 16px;\">
              <table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background-color:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px;\">
                <tr>
                  <td style=\"padding:28px 32px 12px; border-top:5px solid #7C3AED; border-radius:12px 12px 0 0;\">
                    <p style=\"margin:0 0 8px; color:#5B21B6; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;\">Smart-Eval</p>
                    <h1 style=\"margin:0; color:#16213E; font-size:24px; line-height:1.3; font-weight:700;\">Admin Verification Code</h1>
                  </td>
                </tr>
                <tr>
                  <td style=\"padding:12px 32px 28px; color:#374151; font-size:15px; line-height:1.7;\">
                    <p style=\"margin:0 0 16px;\">Hello {$escapedUsername},</p>
                    <p style=\"margin:0 0 20px;\">Use the verification code below to complete your Smart-Eval login.</p>
                    <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-collapse:collapse; background-color:#F8F6FF; border:1px solid #E9D5FF;\">
                      <tr>
                        <td align=\"center\" style=\"padding:18px 12px; color:#5B21B6; font-size:30px; line-height:1.3; font-weight:bold; letter-spacing:8px;\">{$escapedCode}</td>
                      </tr>
                    </table>
                    <p style=\"margin:18px 0 0; color:#4B5563; font-size:14px;\">This code will expire in <strong>{$minutes} minutes</strong>.</p>
                    <p style=\"margin:20px 0 0; padding-top:16px; border-top:1px solid #E5E7EB; color:#6B7280; font-size:13px; line-height:1.6;\">If you did not request this code, please secure your account immediately.</p>
                    <p style=\"margin:20px 0 0; color:#6B7280; font-size:13px;\">Smart-Eval<br>Asian Institute of Technology and Education</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </body>
      </html>
    ";

  }

}

?>