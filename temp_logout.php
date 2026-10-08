<?php

// your logout button should call this
require_once 'app/helpers/session.php';
logoutAdmin();
header('Location: /Smart-Eval/public/login');
