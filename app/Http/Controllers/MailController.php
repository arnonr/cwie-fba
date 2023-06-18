<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailController extends Controller
{
    public function send($receiver = null)
    {
        $mail = new PHPMailer(true);
        $sender = "arnon.r@technopark.kmutnb.ac.th";
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $sender ;
            $mail->Password   = 'xxxx';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom($sender , 'CWIE-FBA');
            $mail->addAddress($receiver, 'Receiver');
            $mail->addReplyTo($sender , 'CWIE-FBA');
            $mail->addCC('arnon.r@technopark.kmutnb.ac.th');
            // $mail->addBCC('bcc@example.com');

            // Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Test mail from Laravel';
            $mail->Body    = 'Test mail from Laravel : This is the HTML message body in bold!';
            $mail->AltBody = 'Test mail from Laravel : This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
            } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
