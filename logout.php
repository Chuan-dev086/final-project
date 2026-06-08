<?php
session_start();    // 1. 找到当前的储物柜
session_destroy();  // 2. 把储物柜直接砸了（销毁所有数据）

header("Location: login-form.php"); // 3. 踢回登录页
exit; 

?> 
