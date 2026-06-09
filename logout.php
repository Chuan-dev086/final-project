<?php
// connect the user session in server 
session_start();    
// unconnect and clear the user session 
session_destroy(); 
// redirect to login-form 
header("Location: login-form.php"); 
exit; 

?> 
