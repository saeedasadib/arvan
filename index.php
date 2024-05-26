<?php
date_default_timezone_set("Asia/Tehran");

$username = $_REQUEST['phonenumber'];
$couponCode = $_REQUEST['coupon-code'];
$message = '';
echo '<body style= "direction: rtl">';

echo '<form action="index.php" method="POST">';
echo '<p>';
echo 'شماره همراه: <input type="number" min="0" name="phonenumber" value="' . $username . '">';
echo '<input type="submit" value="بررسی کیف پول">';
echo '</p>';
echo '</form>';

$mysqli = new mysqli("localhost", "arvan", "arvan", "arvan_challenge", "3306");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($couponCode != null) {

    $checkCodeQuery = $mysqli->query("SELECT id, amount, status FROM coupons where code = $couponCode");
    $checkCodeQueryResult = $checkCodeQuery->fetch_assoc();

    if ($checkCodeQueryResult != null) {
        if ($checkCodeQueryResult['status'] == 'used') {
            $message = 'این کد قبلا استفاده شده!';
        } else {
            $CodeId = $checkCodeQueryResult['id'];

            $userQuery = $mysqli->query("SELECT id,username FROM users where username = $username and status = 'active'");
            $userQueryResult = $userQuery->fetch_assoc();
            $userId = $userQueryResult['id'];

            $checkBalanceQuery = $mysqli->query("SELECT id, balance FROM wallets WHERE user_id = $userId and status = 'active'");
            $checkBalanceQueryResult = $checkBalanceQuery->fetch_assoc();

            if ($checkBalanceQueryResult != null) {
                $newBalance = $checkBalanceQueryResult['balance'] + $checkCodeQueryResult['amount'];
                $walletId = $checkBalanceQueryResult['id'];
                $mysqli->query("UPDATE wallets SET balance = $newBalance WHERE id = $walletId");
                $mysqli->query("UPDATE coupons SET user_id = $userId WHERE id = $CodeId");
                $mysqli->query("UPDATE coupons SET status = 'used' WHERE id = $CodeId");
                $now = time();
                $mysqli->query("INSERT INTO transactions (user_id, coupon_id, status, created_at) VALUES ($userId, $CodeId, 'completed', $now)");
                $message = "اعتبار اضافه شد!";
            }
        }
    } else {
        $message = "کد وارد شده اشتباه است!";
    }
}

if ($username != null) {

    $userQuery = $mysqli->query("SELECT id,username FROM users where username = $username and status = 'active'");
    $userQueryResult = $userQuery->fetch_assoc();

    if ($userQueryResult != null) {
        $userId = $userQueryResult['id'];
        $username = $userQueryResult['username'];

        $walletQuery = $mysqli->query("SELECT balance FROM wallets where user_id = $userId and status = 'active'");
        $walletQueryResult = $walletQuery->fetch_assoc();
        
        if ($walletQueryResult != null) {
            $userBalance = $walletQueryResult["balance"];
        }
    } else {
        $mysqli->query("INSERT INTO users (username, status) VALUES ($username, 'active')");
        $userId = $mysqli->insert_id;
        $mysqli->query("INSERT INTO wallets (user_id,balance,status) VALUES ($userId, 0,'active')");
        $message = "کاربر جدید ایجاد شد!<br>";
        $userBalance = 0;
    }
    echo 'موجودی <b>' .  $username . '</b> مبلغ ' . number_format($userBalance) . ' تومان می‌باشد.';

    echo "<br><br><br>درصورتی که کد هدیه دارید آن را وارد نمایید!";

    echo '<form action="wallet.php" method="POST">';
    echo '<p>';
    echo 'کد هدیه: <input type="number" min="0" name="coupon-code">';
    echo '<input type="submit" value="بررسی">';
    echo '<input type="hidden" name="phonenumber" value="' . $username . '">';
    echo '</p>';
    echo '</form>';
    echo '<p style="color: red">' . $message . '</p>';
}
echo '</body>';

$mysqli->close();
