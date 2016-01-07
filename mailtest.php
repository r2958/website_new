<?php

# -=-=-=- PHP FORM VARIABLES (add as many as you would like)

//$name = $_POST["name"];
//$email = $_POST["email"];
$email = "andrew@temcocontrols.com";
$name = "andrew";
//$invoicetotal = $_POST["invoicetotal"];
$invoivetotal = 50;

# -=-=-=- MIME BOUNDARY

$mime_boundary = "----Your_Company_Name----".md5(time());

# -=-=-=- MAIL HEADERS

$to = "$email";
$subject = "This text will display in the email's Subject Field";

$headers = "From: Our Company <maurice@temcocontrols.com>\n";
$headers .= "Reply-To: Our Company <maurice@temcocontrols.com>\n";
$headers .= "MIME-Version: 1.0\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";

# -=-=-=- TEXT EMAIL PART

$message = "--$mime_boundary\n";
$message .= "Content-Type: text/plain; charset=UTF-8\n";
$message .= "Content-Transfer-Encoding: 8bit\n\n";

$message .= "$name:\n\n";
$message .= "This email is to confirm that \"temcocontrols\" has received your order.\n";
$message .= "Please send payment of $invoicetotal to our company address.\n\n";
$message .= "Thank You.\n\n";

# -=-=-=- HTML EMAIL PART

$message .= "--$mime_boundary\n";
$message .= "Content-Type: text/html; charset=UTF-8\n";
$message .= "Content-Transfer-Encoding: 8bit\n\n";

$message .= "<html>\n";
$message .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:14px; color:#666666;\">\n";
$message .= "$name:<br>\n";
$message .= "<br>\n";
$message .= "This email is to confirm that \"Temco controls\" has received your order.<br>\n";
$message .= "Please send payment of $invoicetotal to our company address.<br>\n\n";
$message .= "<br>\n";
$message .= "Thank You.<br>\n\n";
$message .= "</body>\n";
$message .= "</html>\n";

# -=-=-=- FINAL BOUNDARY

$message .= "--$mime_boundary--\n\n";

# -=-=-=- SEND MAIL

$mail_sent = @mail( $to, $subject, $message, $headers );
echo $mail_sent ? "Mail sent" : "Mail failed";

?>
