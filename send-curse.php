<?php
// send-curse.php — Place this on InternetSatan.com cPanel server
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://internetsatan.com');
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
$recipientName  = htmlspecialchars($input['recipientName'] ?? 'You');
$recipientEmail = filter_var($input['recipientEmail'] ?? '', FILTER_VALIDATE_EMAIL);
$senderName     = htmlspecialchars($input['senderName'] ?? 'Someone');
$curseText      = htmlspecialchars($input['curseText'] ?? '');
$tier           = htmlspecialchars($input['tier'] ?? 'topical');
$landingUrl     = htmlspecialchars($input['landingUrl'] ?? 'https://internetsatan.com');

if (!$recipientEmail) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid recipient email']);
    exit;
}

$tierLabel = [
    'random'  => 'A Random Curse',
    'topical' => 'A Targeted Curse',
    'custom'  => 'A Custom Damnation'
][$tier] ?? 'A Curse';

$subject = "🔥 {$senderName} has cursed you";

$htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#1A0000;font-family:Georgia,serif;">
<div style="max-width:600px;margin:0 auto;background:#120800;">

  <!-- Header -->
  <div style="background:linear-gradient(135deg,#4A0000,#8B0000);padding:40px 32px;text-align:center;border-bottom:2px solid #CC3300;">
    <div style="font-size:36px;margin-bottom:8px;">🔥</div>
    <div style="font-family:Georgia,serif;font-size:24px;font-weight:bold;color:#FF9977;letter-spacing:0.04em;">
      Internet Satan
    </div>
    <div style="font-size:13px;color:rgba(255,153,119,0.7);margin-top:4px;font-style:italic;">
      {$tierLabel}
    </div>
  </div>

  <!-- Body -->
  <div style="padding:40px 32px;">
    <p style="font-size:16px;color:#D4C4A0;margin-bottom:8px;">
      To <strong style="color:#FF9977;">{$recipientName}</strong>,
    </p>
    <p style="font-size:16px;color:#A89070;margin-bottom:32px;line-height:1.7;">
      <strong style="color:#D4C4A0;">{$senderName}</strong> has dispatched a curse against you via Internet Satan. 
      Whether you deserve it is between you and the Dark One.
    </p>

    <!-- Curse box -->
    <div style="background:rgba(139,0,0,0.15);border-left:4px solid #8B0000;padding:24px 28px;margin-bottom:32px;border-radius:0 4px 4px 0;border:1px solid rgba(139,0,0,0.3);">
      <div style="font-size:11px;letter-spacing:0.15em;text-transform:uppercase;color:#CC3300;margin-bottom:12px;font-family:Georgia,serif;">
        Your Curse
      </div>
      <p style="font-style:italic;font-size:17px;color:#D4C4A0;line-height:1.8;margin:0;">
        {$curseText}
      </p>
    </div>

    <!-- CTA -->
    <div style="text-align:center;margin-bottom:32px;">
      <a href="{$landingUrl}"
         style="display:inline-block;background:#8B0000;color:#D4C4A0;text-decoration:none;
                padding:14px 32px;font-family:Georgia,serif;font-size:13px;
                letter-spacing:0.1em;text-transform:uppercase;border-radius:2px;
                border:1px solid #CC3300;">
        View Your Curse Online
      </a>
    </div>

    <p style="font-size:14px;color:#6B5040;text-align:center;font-style:italic;line-height:1.7;">
      Retaliate with a curse of your own — or take the high road with a prayer<br>
      at <a href="https://internetjesus.com" style="color:#6B5040;">InternetJesus.com</a>.
    </p>
  </div>

  <!-- Footer -->
  <div style="background:#0D0A06;padding:20px 32px;text-align:center;border-top:1px solid rgba(139,0,0,0.3);">
    <p style="font-size:11px;color:#4A3020;margin:0;">
      🔥 Internet Satan · For entertainment purposes · Curses not guaranteed to affect anyone
    </p>
    <p style="font-size:11px;color:#4A3020;margin-top:4px;">
      <a href="https://internetsatan.com" style="color:#4A3020;">internetsatan.com</a>
    </p>
  </div>

</div>
</body>
</html>
HTML;

$textBody = "To {$recipientName},\n\n{$senderName} has cursed you via Internet Satan.\n\n--- Your Curse ---\n\n{$curseText}\n\n---\n\nView your curse online: {$landingUrl}\n\nCurse them back: https://internetsatan.com\nOr send a prayer: https://internetjesus.com\n\n🔥 Internet Satan · For entertainment purposes";

$headers  = "From: Internet Satan <curses@internetsatan.com>\r\n";
$headers .= "Reply-To: curses@internetsatan.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "X-Mailer: InternetSatan/1.0\r\n";

$sent = mail($recipientEmail, $subject, $htmlBody, $headers);

if ($sent) {
    echo json_encode(['success' => true, 'slug' => $slug]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Mail delivery failed']);
}
?>
