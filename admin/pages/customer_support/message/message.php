<?php
$currentPage = 'messages'; // Để sidebar highlight đúng mục
// Mock data tin nhắn
$messages = [
    [
        'id' => 1,
        'customer' => 'Nguyen Van A',
        'status' => 'unread',
        'last_message' => 'Tôi cần hỗ trợ về đơn hàng #1234',
        'time' => '2024-06-01 10:00',
        'chat' => [
            ['from' => 'customer', 'text' => 'Tôi cần hỗ trợ về đơn hàng #1234', 'time' => '10:00'],
            ['from' => 'admin', 'text' => 'Chào bạn, bạn cần hỗ trợ gì?', 'time' => '10:01'],
            ['from' => 'customer', 'text' => 'Tôi muốn đổi địa chỉ nhận hàng.', 'time' => '10:02'],
        ]
    ],
    [
        'id' => 2,
        'customer' => 'Tran Thi B',
        'status' => 'read',
        'last_message' => 'Cảm ơn shop đã hỗ trợ!',
        'time' => '2024-06-01 09:30',
        'chat' => [
            ['from' => 'customer', 'text' => 'Shop ơi, đơn hàng của tôi khi nào giao?', 'time' => '09:00'],
            ['from' => 'admin', 'text' => 'Dự kiến ngày mai bạn nhé!', 'time' => '09:05'],
            ['from' => 'customer', 'text' => 'Cảm ơn shop đã hỗ trợ!', 'time' => '09:30'],
        ]
    ],
    // ... Thêm các đoạn chat khác ...
];
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : $messages[0]['id'];
$currentChat = null;
foreach ($messages as $msg) {
    if ($msg['id'] === $selectedId) $currentChat = $msg;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . ' Seconds';
    if ($diff < 3600) return floor($diff/60) . ' Minutes';
    if ($diff < 86400) return floor($diff/3600) . ' Hours';
    return floor($diff/86400) . ' Days';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="message.css"/>
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <!-- Sidebar -->
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>

    <!-- Content Start -->
    <div class="content">
        <!-- Navbar -->
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>

        <!-- Main content -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-white rounded p-4 mb-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Messages</h4>
                    <form class="d-flex" method="get" style="max-width:350px;">
                        <input type="text" class="form-control me-2" name="search" placeholder="Search customer...">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                </div>
                <div class="row" style="height: 60vh;">
                    <!-- Danh sách tin nhắn -->
                    <div class="col-md-4" style="overflow-y:auto;">
                        <h5 class="mb-3" style="color:#6c63ff; font-weight:700;">Chats</h5>
                        <ul class="list-unstyled">
                            <?php foreach($messages as $msg): ?>
                            <li class="d-flex align-items-center mb-4">
                                <!-- Avatar -->
                                <?php if (!empty($msg['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($msg['avatar']) ?>" class="rounded-circle me-3" style="width:48px;height:48px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#f3f6fb;color:#6c63ff;font-weight:700;font-size:1.1em;">
                                        <?= strtoupper(substr($msg['customer'],0,2)) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <span style="font-weight:600;"><?= htmlspecialchars($msg['customer']) ?></span>
                                        <span class="ms-auto text-muted small"><?= timeAgo($msg['time']) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= htmlspecialchars($msg['last_message']) ?></div>
                                </div>
                                <!-- Status dot -->
                                <span class="ms-2 align-self-start" style="font-size:1.1em;">
                                    <span class="dot-status" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= $msg['status']=='unread'?'#4caf50':'#d32f2f' ?>;"></span>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <!-- Khung chat -->
                    <div class="col-md-8 d-flex flex-column" style="height:100%;">
                        <!-- Header chat -->
                        <div class="d-flex align-items-center pb-3 mb-2" style="background:#fff;">
                            <?php
                            // Lấy avatar và tên khách hàng
                            $avatar = !empty($currentChat['avatar']) ? $currentChat['avatar'] : '';
                            $customer = $currentChat['customer'];
                            ?>
                            <?php if ($avatar): ?>
                                <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#f3f6fb;color:#6c63ff;font-weight:700;font-size:1em;">
                                    <?= strtoupper(substr($customer,0,2)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-weight:600;"><?= htmlspecialchars($customer) ?></div>
                                <div class="text-muted small">NextJS developer</div>
                            </div>
                            <span class="ms-auto"><span class="dot-status" style="background:#4caf50;"></span></span>
                        </div>
                        <!-- Chat messages -->
                        <div class="flex-grow-1 overflow-auto px-2" id="chat-box" style="background:#f8f9fa;">
                            <?php foreach($currentChat['chat'] as $chat): ?>
                                <div class="mb-2 d-flex <?= $chat['from']=='admin'?'justify-content-end':'' ?>">
                                    <?php if($chat['from']=='customer'): ?>
                                        <!-- Avatar khách -->
                                        <?php if ($avatar): ?>
                                            <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle me-2 align-self-end" style="width:32px;height:32px;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle me-2 d-flex align-items-center justify-content-center align-self-end" style="width:32px;height:32px;background:#f3f6fb;color:#6c63ff;font-weight:700;font-size:0.9em;">
                                                <?= strtoupper(substr($customer,0,2)) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="chat-bubble <?= $chat['from']=='admin'?'admin-bubble':'customer-bubble' ?>">
                                        <?= htmlspecialchars($chat['text']) ?>
                                        <div class="text-end small text-muted mt-1"><?= $chat['time'] ?></div>
                                    </div>
                                    <?php if($chat['from']=='admin'): ?>
                                        <!-- Avatar admin -->
                                        <img src="/WEB_MXH/admin/img/admin-avatar.png" class="rounded-circle ms-2 align-self-end" style="width:32px;height:32px;object-fit:cover;">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Input -->
                        <form class="d-flex align-items-center gap-2 mt-2 p-3" style="background:#fff;border-top:1px solid #eee;">
                            <input type="text" class="form-control" placeholder="Type your message here...">
                            <label class="btn btn-light mb-0" for="file-attach"><i class="fas fa-paperclip"></i></label>
                            <input type="file" id="file-attach" hidden>
                            <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
<script>
    // Tự động cuộn xuống cuối chat khi load
    window.onload = function() {
        var chatBox = document.getElementById('chat-box');
        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
</body>
</html>