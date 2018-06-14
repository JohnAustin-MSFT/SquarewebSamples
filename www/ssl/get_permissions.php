
<?php
  $applicationId = 'sq0idp-4ycGUGUddwYgxxyNydVRiw';
  $permissions = urlencode("MERCHANT_PROFILE_READ EMPLOYEES_READ TIMECARDS_READ");
  echo "<a href=\"https://connect.squareup.com/oauth2/authorize?client_id=$applicationId&scope=$permissions\">Click here</a> to authorize the application.";

  ?>

