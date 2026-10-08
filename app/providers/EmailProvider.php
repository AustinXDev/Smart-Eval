<?php 
namespace App\providers;

require_once __DIR__ . '/../init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailProvider{

  private $mail;

  public function __construct()
  {

    $this->mail = new PHPMailer();

    $this->mail->isSMTP();
    $this->mail->Host = $_ENV['SMTP_HOST'];
    $this->mail->SMTPAuth = true;
    $this->mail->Username = $_ENV['SMTP_USERNAME'];
    $this->mail->Password = $_ENV['SMTP_PASSWORD'];
    $this->mail->SMTPSecure = 
      $_ENV['SMTP_ENCRYPTION'] === 'tls' 
      ? PHPMailer::ENCRYPTION_STARTTLS 
      : PHPMailer::ENCRYPTION_SMTPS;
    $this->mail->Port = (int) $_ENV['SMTP_PORT'];

    $this->mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
    $this->mail->isHTML(true);

  }

  public function send($to, $subject, $body){

    try{

      //prevent sending duplicate mail
      $this->mail->clearAddresses();

      $this->mail->addAddress($to);
      $this->mail->Subject = $subject;
      $this->mail->Body = $body;

      return $this->mail->send();

    } catch (Exception $e) {

      return false;

    }

  }

}
?>