<html>
<head></head>

<body>

<h1>Hello world!</h1>
<hr>

<ul>
  <li>
    <b>whoami</b>:
    <?= `whoami`; ?>
  </li>
  <li>
    <b>Serving from</b>:
    <?= __FILE__ ?>
  </li>
</ul>
<form> 
<input type="button" value="sign in" onclick="window.location.href='https://connect.squareup.com/oauth2/authorize?client_id=YOUR_APPLICATION_ID&scope=MERCHANT_PROFILE_READ'""
</form>
</body>
</html>