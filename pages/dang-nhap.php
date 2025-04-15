<?php
session_start();
error_reporting(0);

include('../config.php');

// Simplified login check
if (isset($_SESSION['username']) && isset($_SESSION['level'])) {
    if ($_SESSION['must_change_password'] == 1) {
        // Redirect to password change page instead of login
        header("Location: doi-mat-khau.php");
    } else {
        header("Location: index.php?p=index&a=statistic");
    }
    exit();
}

// Handle login form submission
if (isset($_POST['login'])) {
    $error = [];
    $showMess = false;

    // Validate and process login
    if (!empty($_POST['user_name']) && !empty($_POST['password'])) {
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, user_name, mat_khau, user_quyen, trang_thai, must_change_password, ten_nv, phong_ban_id 
                               FROM nhanvien 
                               WHERE user_name = ? AND trang_thai = 1");
        $stmt->bind_param('s', $user_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['mat_khau'])) {
            $_SESSION['username'] = $user_name;
            $_SESSION['idNhanVien'] = $row['id'];
            $_SESSION['TenNhanVien'] = $row['ten_nv'];
            $_SESSION['phongBanID'] = $row['phong_ban_id'];
            $_SESSION['level'] = $row['user_quyen'];
            $_SESSION['must_change_password'] = $row['must_change_password'];

            if ($row['must_change_password'] == 1) {
                header("Location: doi-mat-khau.php");
            } else {
                header("Location: index.php?p=index&a=statistic");
            }
            exit();
        } else {
            $error['check'] = "Thông tin đăng nhập không chính xác";
        }
        $stmt->close();
    } else {
        $error['check'] = "Vui lòng nhập đầy đủ thông tin";
    }
}

?>

    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - VHE</title>
        <link rel="shortcut icon" href="../dist/images/favicon.ico" type="image/x-icon" />
        <style>
            /* Reset CSS */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #1d243d;
                background-size: 300% 300%;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            @keyframes gradient-animation {
                0% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }

                100% {
                    background-position: 0% 50%;
                }
            }
            #particles-js {
                position: fixed;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                z-index: -1; /* Đưa nó xuống dưới */
            }
            .login-container {
                background: rgba(255, 255, 255, 0.9);
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                width: 100%;
                max-width: 400px;
                text-align: center;
                backdrop-filter: blur(10px);
                
            }

            .login-form h2 {
                margin-bottom: 20px;
                color: #333333;
                font-size: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            .login-form h2 i {
                color: #6e8efb;
            }

            .input-group {
                margin-bottom: 20px;
                text-align: left;
                position: relative;
            }

            .input-group label {
                display: block;
                margin-bottom: 5px;
                color: #555555;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .input-group input {
                width: 100%;
                padding: 12px 10px 12px 35px;
                border: 2px solid #dddddd;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s ease;
                background: transparent;
            }

            .input-group input:focus {
                border-color: #6e8efb;
                box-shadow: 0 0 8px rgba(110, 142, 251, 0.5);
            }

            .input-group i {
                position: absolute;
                left: 10px;
                top: 67%;
                transform: translateY(-50%);
                color: #aaaaaa;
            }

            .login-button {
                width: 100%;
                padding: 12px;
                background: #6e8efb;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s ease, transform 0.3s ease;
            }

            .login-button:hover {
                background: #5a7de2;
                transform: scale(1.05);
            }

            .forgot-password {
                margin-top: 15px;
                font-size: 14px;
            }

            .forgot-password a {
                color: #6e8efb;
                text-decoration: none;
                transition: color 0.3s ease;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .forgot-password a:hover {
                color: #5a7de2;
            }
        </style>
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <!--<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>-->

    </head>

    <body >
        <?php
        if (isset($error)) {
            if ($showMess == false) {
                // echo "<div class='alert alert-danger alert-dismissible notify'";
                // echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true' ></button>";
                foreach ($error as $err) {
                    echo "<script>alert('". $err ."')</script>";
                }
                // echo "</div>";
            }
        }
        ?>
        <!--<i class="fas fa-user-circle"></i>-->
        <div id="particles-js"></div>
        <div class="login-container">
            <form class="login-form box" method="POST" name="form1" onsubmit="return checkStuff()" autocomplete="off">
                <h2> <img src="../uploads/VHE_Logo_border_small.png" style="height:60px;"></h2>
                <div class="input-group">
                    <span class="error animated tada" id="msg"></span>
                </div>
                    <div class="input-group">
                        <label for="username"><i class="fas fa-user"></i> Tên đăng nhập sdasdasdsd</label>
                        <input type="text" id="username" placeholder="Nhập tên đăng nhập" required name="user_name" autocomplete="off"
                        value="<?php echo isset($_POST['user_name']) ? $_POST['user_name'] : ''; ?>">
                    </div>
                    <div class="input-group">
                        <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                        <input type="password" name="password"  id="pwd" autocomplete="off" placeholder="Nhập mật khẩu" required>
                    </div>
                    <!-- <button type="submit" class="login-button">Đăng nhập</button> -->
                    <input type="submit" value="Đăng nhập" name="login" class="login-button">
                    <p class="forgot-password"><a href="#" onclick="alert('Liên hệ 0559-545483!')"><i class="fas fa-question-circle"></i> Quên mật khẩu?</a></p>
            </form>
        </div>
    <!-- jQuery 3 -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="../bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="../plugins/iCheck/icheck.min.js"></script>

        <script>
            function checkStuff() {
                var user_name = document.form1.user_name;
                var password = document.form1.password;
                var msg = document.getElementById('msg');

                console.log(user_name, password, msg)
                return

                if (user_name.value == "") {
                    msg.style.display = 'block';
                    msg.innerHTML = "Please enter your user_name";
                    user_name.focus();
                    return false;
                } else {
                    msg.innerHTML = "";
                }

                if (password.value == "") {
                    msg.innerHTML = "Please enter your password";
                    password.focus();
                    return false;
                } else {
                    msg.innerHTML = "";
                }
                var re =
                    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(user_name.value)) {
                    msg.innerHTML = "Please enter a valid user_name";
                    user_name.focus();
                    return false;
                } else {
                    msg.innerHTML = "";
                }
            }
            // ParticlesJS

    // ParticlesJS Config.
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 60,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "img/github.svg",
                    "width": 100,
                    "height": 100
                }
            },
            "opacity": {
                "value": 0.1,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 6,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.1,
                "width": 2
            },
            "move": {
                "enable": true,
                "speed": 1.5,
                "direction": "top",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": false,
                    "mode": "repulse"
                },
                "onclick": {
                    "enable": false,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 400,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
        </script>
    </body>

    </html>


