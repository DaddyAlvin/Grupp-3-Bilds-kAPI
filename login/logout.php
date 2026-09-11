<?php
// Clear the current session before returning to the home page.
session_start();
session_unset();
session_destroy();

header('Location: ../index.php');
exit;