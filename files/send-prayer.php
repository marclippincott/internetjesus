<?php
// send-prayer.php — Place this on InternetJesus.com cPanel server
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://internetjesus.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$slug           = htmlspecialchars($input['slug'] ?? '');
$recipientName  = htmlspecialchars($input['recipientName'] ?? 'Friend');
$recipientEmail = filter_var($input['recipientEmail'] ?? '', FILTER_VALIDATE_EMAIL);
$senderName     = htmlspecialchars($input['senderName'] ?? 'Someone');
$prayerText     = htmlspecialchars($input['prayerText'] ?? '');
$tier           = htmlspecialchars($input['tier'] ?? 'topical');
$landingUrl     = htmlspecialchars($input['landingUrl'] ?? 'https://internetjesus.com');

if (!$recipientEmail) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid recipient email']);
    exit;
}

$tierLabel = [
    'random'  => 'A Random Blessing',
    'topical' => 'A Chosen Prayer',
    'custom'  => 'A Personal Blessing'
][$tier] ?? 'A Prayer';

$subject = "✝ {$senderName} has sent you a prayer";

$htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#F5EDD8;font-family:Georgia,serif;">
<div style="max-width:600px;margin:0 auto;background:#FDF8F0;">

  <!-- Header -->
  <div style="background:linear-gradient(135deg,#8B6914,#C9A84C);padding:40px 32px;text-align:center;">
    <div style="font-size:36px;margin-bottom:8px;">✝</div>
    <div style="font-family:Georgia,serif;font-size:24px;font-weight:bold;color:#FFFEF9;letter-spacing:0.04em;">
      Internet Jesus
    </div>
    <div style="font-size:13px;color:rgba(255,254,249,0.8);margin-top:4px;font-style:italic;">
      {$tierLabel}
    </div>
  </div>

  <!-- Body -->
  <div style="padding:40px 32px;">
    <p style="font-size:16px;color:#5C3D2E;margin-bottom:8px;">
      Dear <strong>{$recipientName}</strong>,
    </p>
    <p style="font-size:16px;color:#5C3D2E;margin-bottom:32px;line-height:1.7;">
      <strong>{$senderName}</strong> has sent you a prayer from Internet Jesus. 
      May it find you in good health and open spirit.
    </p>

    <!-- Prayer box -->
    <div style="background:#F5EDD8;border-left:4px solid #C9A84C;padding:24px 28px;margin-bottom:32px;border-radius:0 4px 4px 0;">
      <div style="font-size:11px;letter-spacing:0.15em;text-transform:uppercase;color:#8B6914;margin-bottom:12px;font-family:Georgia,serif;">
        Your Prayer
      </div>
      <p style="font-style:italic;font-size:17px;color:#2C1810;line-height:1.8;margin:0;">
        {$prayerText}
      </p>
    </div>

    <!-- CTA -->
    <div style="text-align:center;margin-bottom:32px;">
      <a href="{$landingUrl}" 
         style="display:inline-block;background:#8B6914;color:#FFFEF9;text-decoration:none;
                padding:14px 32px;font-family:Georgia,serif;font-size:13px;
                letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;">
        View Your Prayer Online
      </a>
    </div>

    <p style="font-size:14px;color:#8B6914;text-align:center;font-style:italic;line-height:1.7;">
      You can also send a prayer back — or if you're feeling less generous,<br>
      a curse is available at <a href="https://internetsatan.com" style="color:#8B6914;">InternetSatan.com</a>.
    </p>
  </div>

  <!-- Footer -->
  <div style="background:#F5EDD8;padding:20px 32px;text-align:center;border-top:1px solid rgba(201,168,76,0.3);">
    <p style="font-size:11px;color:#8B6914;margin:0;">
      ✝ Internet Jesus · For entertainment purposes · Prayers not guaranteed to reach God
    </p>
    <p style="font-size:11px;color:#8B6914;margin-top:4px;">
      <a href="https://internetjesus.com" style="color:#8B6914;">internetjesus.com</a>
    </p>
  </div>

</div>
</body>
</html>
HTML;

$textBody = "Dear {$recipientName},\n\n{$senderName} has sent you a prayer from Internet Jesus.\n\n--- Your Prayer ---\n\n{$prayerText}\n\n---\n\nView your prayer online: {$landingUrl}\n\nSend one back: https://internetjesus.com\nOr a curse: https://internetsatan.com\n\n✝ Internet Jesus · For entertainment purposes";

$headers  = "From: Internet Jesus <prayers@internetjesus.com>\r\n";
$headers .= "Reply-To: prayers@internetjesus.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "X-Mailer: InternetJesus/1.0\r\n";

$sent = mail($recipientEmail, $subject, $htmlBody, $headers);

if ($sent) {
    echo json_encode(['success' => true, 'slug' => $slug]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Mail delivery failed']);
}
?>
