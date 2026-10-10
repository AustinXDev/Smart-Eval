<?php  

namespace App\Services\Admin\PasswordResetServices;

class ForgotPasswordEmail
{

  public static function build(
    string $username,
    string $link,
  ): string {
    $escapedUsername = htmlspecialchars(
      $username,
      ENT_QUOTES,
      'UTF-8'
    );
    $escapedLink = htmlspecialchars(
      $link,
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
        <title>Reset Your Admin Password</title>
      </head>
      <body style=\"margin:0; padding:0; background-color:#F3F4F6; font-family:Arial, Helvetica, sans-serif; color:#16213E;\">
        <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-collapse:collapse; background-color:#F3F4F6;\">
          <tr>
            <td align=\"center\" style=\"padding:32px 16px;\">
              <table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; max-width:600px; border-collapse:separate; border-spacing:0; background-color:#FFFFFF; border:1px solid #E5E7EB; border-radius:12px;\">
                <tr>
                  <td style=\"padding:28px 32px 12px; border-top:5px solid #7C3AED; border-radius:12px 12px 0 0;\">
                    <p style=\"margin:0 0 8px; color:#5B21B6; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase;\">Smart-Eval</p>
                    <h1 style=\"margin:0; color:#16213E; font-size:24px; line-height:1.3; font-weight:700;\">Reset Your Admin Password</h1>
                  </td>
                </tr>
                <tr>
                  <td style=\"padding:12px 32px 28px; color:#374151; font-size:15px; line-height:1.7;\">
                    <p style=\"margin:0 0 16px;\">Hello {$escapedUsername},</p>
                    <p style=\"margin:0 0 24px;\">You requested to reset your Smart-Eval admin password. Use the button below to continue.</p>
                    <table role=\"presentation\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:separate; border-spacing:0;\">
                      <tr>
                        <td align=\"center\" bgcolor=\"#7C3AED\" style=\"background-color:#7C3AED; border-radius:8px;\">
                          <a href=\"{$escapedLink}\" style=\"display:inline-block; padding:13px 22px; border:1px solid #7C3AED; border-radius:8px; color:#FFFFFF; font-size:14px; line-height:1.2; font-weight:bold; text-decoration:none;\">Reset Password</a>
                        </td>
                      </tr>
                    </table>
                    <p style=\"margin:24px 0 0; color:#6B7280; font-size:13px; line-height:1.6;\">If you didn't request this, you can safely ignore this email.</p>
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