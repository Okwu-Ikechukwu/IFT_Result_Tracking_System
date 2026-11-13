<?php
require_once __DIR__ . '/db.php';

function send_result_email(int $resultId, string $event): bool {
    $pdo=db();
    $s=$pdo->prepare('SELECT r.*, c.course_code,c.course_title, GROUP_CONCAT(l.email ORDER BY l.id SEPARATOR ",") lecturer_emails, GROUP_CONCAT(l.full_name ORDER BY l.id SEPARATOR ", ") lecturer_names FROM result_records r JOIN courses c ON c.id=r.course_id JOIN course_lecturers cl ON cl.course_id=c.id JOIN lecturers l ON l.id=cl.lecturer_id WHERE r.id=? GROUP BY r.id');
    $s->execute([$resultId]); $r=$s->fetch(); if(!$r || !$r['lecturer_emails']) return false;
    $subject='IFT Result Tracking: ' . $event;
    $body="Dear Lecturer(s),\n\nThe status of the result for {$r['course_code']} - {$r['course_title']} has been updated.\n\nEvent: {$event}\nTracking No: {$r['tracking_no']}\n\nTrack it here: " . app_url('public-tracking.php?token=' . $r['public_token']) . "\n\nDepartment of Information Technology\nIFT Result Tracking System";
    $sent=false; $error=null;
    $autoload=__DIR__.'/../../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        try {
            $mail=new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP(); $mail->Host=getenv('IFT_SMTP_HOST') ?: 'smtp.example.com'; $mail->SMTPAuth=true;
            $mail->Username=getenv('IFT_SMTP_USER') ?: ''; $mail->Password=getenv('IFT_SMTP_PASS') ?: '';
            $mail->SMTPSecure=PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; $mail->Port=(int)(getenv('IFT_SMTP_PORT') ?: 587);
            $mail->setFrom(getenv('IFT_FROM_EMAIL') ?: 'noreply@example.com', 'IFT Result Tracking');
            foreach(explode(',', $r['lecturer_emails']) as $email) $mail->addAddress(trim($email));
            $mail->Subject=$subject; $mail->Body=$body; $mail->send(); $sent=true;
        } catch(Throwable $e) { $error=$e->getMessage(); }
    } else { $error='PHPMailer is not installed/configured yet.'; }
    $pdo->prepare('INSERT INTO email_logs (result_id,recipient,event_name,subject,status,error_message) VALUES (?,?,?,?,?,?)')->execute([$resultId,$r['lecturer_emails'],$event,$subject,$sent?'Sent':'Failed',$error]);
    return $sent;
}
