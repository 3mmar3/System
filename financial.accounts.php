<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Animated Page</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');

  body {
    margin: 0;
    height: 100vh;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Poppins', sans-serif;
    color: white;
    overflow: hidden;
  }

  .container {
    text-align: center;
    max-width: 90vw;
  }

  h1 {
    font-size: 2.8rem; /* تصغير الحجم */
    margin-bottom: 0.3em;
    animation: slideIn 1.2s ease forwards;
    opacity: 0;
  }

  .animated-text {
    font-size: 1.6rem;
    height: 2rem;
    overflow: hidden;
    position: relative;
  }

  .animated-text span {
    position: absolute;
    width: 100%;
    left: 0;
    top: 0;
    opacity: 0;
    animation: fadeSlide 6s infinite;
  }

  .animated-text span:nth-child(1) {
    animation-delay: 0s;
  }
  .animated-text span:nth-child(2) {
    animation-delay: 2s;
  }
  .animated-text span:nth-child(3) {
    animation-delay: 4s;
  }

  /* Animations */
  @keyframes slideIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
  }

  @keyframes fadeSlide {
    0% {opacity: 0; transform: translateY(20px);}
    10% {opacity: 1; transform: translateY(0);}
    30% {opacity: 1; transform: translateY(0);}
    40% {opacity: 0; transform: translateY(-20px);}
    100% {opacity: 0;}
  }
</style>
</head>
<body>

<div class="container">
  <h1>Wait soon the technical team is working</h1>
  <div class="animated-text">
    <span>Effortless Shift Management</span>
    <span>Track Attendance Seamlessly</span>
    <span>Stay Updated in Real-Time</span>
  </div>
</div>

</body>
</html>
