<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stopwatch</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* Global Styles */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      font-family: 'Orbitron', sans-serif;
      background: url('images/PinkBgClear.png') no-repeat center center fixed;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff;
    }

    .stopwatch {
  position: relative;
  width: 700px;
  height: 400px;
  border-radius: 15px;
  background-color: rgba(0, 0, 0, 0.8); /* TaskFlow Gray with 80% opacity */
  box-shadow: 0 20px 100px rgba(0, 0, 0, 0.7); /* Softer shadow */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 20px;
  backdrop-filter: blur(10px); /* Adds a slight blur effect for a modern glassy look */
  margin-left: auto; /* Pushes the stopwatch to the right */
  margin-right: 300px; /* Adds spacing from the right edge */

   /* Background Image */
   background-image: url('images/PinkBG.png'); /* Replace with your actual image path */
   background-size: cover;  /* Cover the entire container */
   background-position: center; /* Center the image */
   background-repeat: no-repeat; /* Prevent repetition */
}


    .stopwatch .display {
      font-size: 3rem;
      display: flex;
      gap: 8px;
    }

    .digit {
      font-weight: 700;
      color:rgb(255, 255, 255);
      border-top: 2px solid #fff;
      border-bottom: 2px solid #fff;
      box-shadow: 0px 50px 10px rgba(0, 0, 0, 0.3);

    }

    .stopwatch__controls {
      margin-top: 80px;
      display: flex;
      justify-content: space-between;
      width: 100%;
      gap: 10px;
      
    }

    .stopwatch__button {
      flex: 1;
      padding: 10px 15px;
      border-radius: 8px;
      border: none;
      font-size: 1.2rem;
      color: #fff;
      cursor: pointer;
      transition: 0.2s;
    }

    #start {
      background: #007bff; /* TaskFlow Blue */
      box-shadow: 0px 50px 10px rgba(0, 0, 0, 0.3);
    }

    #start:hover {
      background: #0056b3;
    }

    #pause {
      background: #ffbb33; /* Orange */
      box-shadow: 0px 50px 10px rgba(0, 0, 0, 0.3);
    }

    #pause:hover {
      background: #ff8800;
    }

    #reset {
      background: #ff4444; /* TaskFlow Red */
      box-shadow: 0px 50px 10px rgba(0, 0, 0, 0.3);
    }

    #reset:hover {
      background: #cc0000;
    }

    .stopwatch__button:disabled {
      background: #444;
      cursor: not-allowed;
    }

    /* Timer Text */
.timer-title {
    position: absolute;
    top: 100px; /* Adjusted position to be just above the line */
    left: 300px; /* Aligns with the line */
    font-family: 'Archivo', sans-serif;
    font-size: 30px;
    font-weight: 700;
    color: #FFFFFF;
}

/* Line Under Timer */
.timer-line {
    position: absolute;
    top: 148px; /* Positioned below Home */
    left: 300px;
    width: 1200px;
    height: 0;
    border-width: 1px;
    border-color: #FFFFFF;
    border-style: solid;
}


.menu-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 256px;
    height: 100vh;
    background: #000000;
    border-radius: 0px 16px 16px 0px;
    box-shadow: 0px 17px 35px rgba(23, 26, 31, 0.24), 0px 0px 2px rgba(23, 26, 31, 0.12);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Logo */
.image {
    position: absolute;
    top: 58px;
    left: 11px;
    width: 240px;
    height: 53px;
}

/* Line */
.line {
    position: absolute;
    top: 146px;
    left: 8px;
    width: 238px;
    height: 0;
    border-width: 1px;
    border-color: #FFFFFF;
    border-style: solid;
}

/* Sidebar Menu */
.sidebar-menu {
    position: absolute;
    top: 185px;
    left: 15px;
    width: 224px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Sidebar Menu Item */
.sidebar-menu .sidebar-menu-item {
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: left;
    color: #FFFFFF;
    background: #000000;
    border-radius: 6px;
    gap: 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.3s ease-in-out;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
}

/* Sidebar Icons */
.sidebar-menu .sidebar-menu-item i {
    font-size: 18px;
    width: 25px;
}

/* Selected Item */
.sidebar-menu .sidebar-menu-item.selected {
    font-weight: 700;
    color: #A03794;
    background: #E9BFE4;
}

/* Sidebar Hover */
.sidebar-menu .sidebar-menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #FFFFFF;
}

.sidebar-menu a {
    text-decoration: none; /* Ensures no underline on links */
    display: flex; /* Maintain layout */
    align-items: center;
}

/* Bottom Line */
.line-bottom {
    position: absolute;
    bottom: 80px;
    left: 15px;
    width: 231px;
    height: 0px;
    border-width: 1px;
    border-color: #FFFFFF;
    border-style: solid;
}

/* Footer Text */
.text {
    position: absolute;
    bottom: 20px;
    left: 15px;
    width: 224px;
    text-align: center;
    font-size: 11px;
    color: #FFFFFF;
    font-family: 'Inter', sans-serif;
}

/* Main Content */
.content {
    margin-left: 280px;
    padding: 20px;
}

/* Logout Button */
.logout-btn {
    position: absolute;
    top: 25px;
    right: 50px; /* Adjusts to the right */
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: #000000;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    gap: 8px;
}


/* Logout Button Icon */
.logout-btn i {
    font-size: 18px;
}

/* Hover Effect */
.logout-btn:hover {
    background: #822D78;
}

/* Pressed Effect */
.logout-btn:active {
    background: #65235D;

}    
  </style>
</head>
<body>

  <div class="stopwatch">
    <div class="display">
      <div class="digit" id="minutes">00</div>
      <div class="digit">:</div>
      <div class="digit" id="seconds">00</div>
      <div class="digit">.</div>
      <div class="digit" id="milliseconds">00</div>
    </div>
    <div class="stopwatch__controls">
      <button id="start" class="stopwatch__button">Start</button>
      <button id="pause" class="stopwatch__button" disabled>Pause</button>
      <button id="reset" class="stopwatch__button" disabled>Reset</button>
    </div>
  </div>

  <script>
    let minutes = 0;
    let seconds = 0;
    let milliseconds = 0;
    let running = false;
    let timerInterval;
    const startButton = document.getElementById("start");
    const pauseButton = document.getElementById("pause");
    const resetButton = document.getElementById("reset");

    const updateDisplay = () => {
      document.getElementById("minutes").textContent = minutes.toString().padStart(2, '0');
      document.getElementById("seconds").textContent = seconds.toString().padStart(2, '0');
      document.getElementById("milliseconds").textContent = milliseconds.toString().padStart(2, '0');
    };

    const startTimer = () => {
      if (running) return;
      running = true;
      startButton.disabled = true;
      pauseButton.disabled = false;
      resetButton.disabled = false;

      timerInterval = setInterval(() => {
        milliseconds++;
        if (milliseconds >= 100) {
          milliseconds = 0;
          seconds++;
        }
        if (seconds >= 60) {
          seconds = 0;
          minutes++;
        }
        updateDisplay();
      }, 10);
    };

    const pauseTimer = () => {
      clearInterval(timerInterval);
      running = false;
      startButton.disabled = false;
      pauseButton.disabled = true;
    };

    const resetTimer = () => {
      clearInterval(timerInterval);
      running = false;
      minutes = 0;
      seconds = 0;
      milliseconds = 0;
      updateDisplay();
      startButton.disabled = false;
      pauseButton.disabled = true;
      resetButton.disabled = true;
    };

    startButton.addEventListener("click", startTimer);
    pauseButton.addEventListener("click", pauseTimer);
    resetButton.addEventListener("click", resetTimer);
  </script>


<!--Logout button-->
<button class="logout-btn" onclick="window.location.href='logout.php'">
    <i class="fas fa-sign-out-alt"></i> 
</button>



<!-- timer and line-->
<div class="timer-container">
<div class="timer-title">Timer</div>
<div class="timer-line"></div>
</div>


<!-- Sidebar -->
<div class="menu-container">
    <div class="logo">
        <img src="images/logo.png" alt="TaskFlow Logo" class="image">
    </div>

    <hr class="line">


    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
    <a href="Dashboard.php" class="sidebar-menu-item ">
        <i class="fas fa-home"></i> Home
    </a>
    <a href="Calendar.php" class="sidebar-menu-item">
        <i class="fas fa-calendar-alt"></i> Calendar
    </a>
    <a href="Tasks.php" class="sidebar-menu-item">
        <i class="fas fa-tasks"></i> Tasks
    </a>
    <a href="ArchivedTasks.php" class="sidebar-menu-item">
        <i class="fas fa-box-archive"></i> Archived Tasks
    </a>
    <a href="Notifications.php" class="sidebar-menu-item">
        <i class="fas fa-bell"></i> Notification
    </a>
    <a href="Timer.php" class="sidebar-menu-item selected">
        <i class="fas fa-clock"></i> Timer
    </a>
    <a href="Settings.php" class="sidebar-menu-item">
        <i class="fas fa-cog"></i> Settings
    </a>
</div>

    <!-- Additional Line -->
    <hr class="line-bottom">

    <!-- Footer Text -->
    <div class="text">
        TaskFlow © 2025. All rights reserved.<br>
        Made with ❤️ by Nouf.<br>
        Fictional University Project
    </div>
</div>
</body>
</html>
