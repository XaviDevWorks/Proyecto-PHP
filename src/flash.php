<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['flash_message'])) {
    $message = htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES, 'UTF-8');
    $flash_msg = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    echo '<div id="flash-message" style="display:block !important; visibility:visible !important; opacity:1 !important; max-width:1040px; margin:12px auto 24px auto; padding:14px 18px; border-radius:12px; border:2px solid #22c55e; background: linear-gradient(135deg, #102419 0%, #1a2c22 100%); color: #d9fbe4; font-weight: bold; font-size: 16px; box-shadow: 0 4px 20px rgba(34, 197, 94, 0.4); text-align: center; position: relative; z-index: 100;">' . htmlspecialchars($flash_msg, ENT_QUOTES, 'UTF-8') . '</div>';
}
