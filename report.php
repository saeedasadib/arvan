<?php
date_default_timezone_set("Asia/Tehran");

$mysqli = new mysqli("localhost", "arvan", "arvan", "arvan_challenge", "3306");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$transactionQuery = $mysqli->query("SELECT user_id, coupon_id, created_at FROM transactions where status ='completed'");

echo '<body style= "direction: rtl">';
if ($transactionQuery->num_rows != 0) {

    echo '<table>';
    echo '<tr>';
    echo '<th>ردیف</th>';
    echo '<th>نام کاربری</th>';
    echo '<th>مبلغ (تومان)</th>';
    echo '<th>تاریخ</th>';
    echo '</tr>';

    $i = 1;
    while ($row = $transactionQuery->fetch_assoc()) {
        $userId = $row['user_id'];
        $userQuery = $mysqli->query("SELECT username FROM users where id = $userId");
        $userQueryResult = $userQuery->fetch_assoc();

        $couponId = $row['coupon_id'];
        $couponQuery = $mysqli->query("SELECT amount FROM coupons where id = $couponId");
        $couponQueryResult = $couponQuery->fetch_assoc();

        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $userQueryResult['username'] . '</td>';
        echo '<td>' . number_format($couponQueryResult['amount']) . '</td>';
        echo '<td>' . date('Y-m-d H:i', $row['created_at']) . '</td>';
        echo '</tr>';
        $i++;
    }
    echo '</table>';
} else {
    echo '<p>رکوردی برای نمایش وجود ندارد!</p>';
}
echo '</body>';

$mysqli->close();
