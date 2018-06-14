
<?php
  $applicationId = 'YOUR APPLICATION ID';
  $permissions = 'MERCHANT_PROFILE_READ';

  echo "<a href=\'https://connect.squareup.com/oauth2/authorize?client_id=$applicationId\&scope=$permissions'>Click here</a> to authorize the application.";
?>

