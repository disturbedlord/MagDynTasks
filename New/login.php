<head>
    <?php require "../Common/header.php" ?>
</head>
<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <!-- Card -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-6">

        <!-- Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-6">
            <button id="loginTab" class="w-1/2 py-2 text-sm font-medium rounded-md bg-white shadow text-gray-800">
                Login
            </button>
            <button id="registerTab" class="w-1/2 py-2 text-sm font-medium rounded-md text-gray-500">
                Register
            </button>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="space-y-4">

            <div>
                <label class="text-sm text-gray-600">Email</label>
                <input id="login_email" name="email" type="email" class="w-full mt-1 px-3 py-2 border rounded-md"
                    required>
            </div>

            <div>
                <label class="text-sm text-gray-600">Password</label>
                <input id="login_password" name="password" type="password"
                    class="w-full mt-1 px-3 py-2 border rounded-md" required>
            </div>

            <button class="w-full bg-blue-600 text-white py-2 rounded-md">
                Login
            </button>
        </form>

        <!-- REGISTER FORM -->

        <form id="registerForm" class="space-y-4 hidden">

            <div>
                <label class="text-sm text-gray-600">Name</label>
                <input id="reg_name" name="name" type="text" placeholder="Your name"
                    class="w-full mt-1 px-3 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="text-sm text-gray-600">Email</label>
                <input id="reg_email" name="email" type="email" placeholder="Enter email"
                    class="w-full mt-1 px-3 py-2 border rounded-md" required>
            </div>

            <div>
                <label class="text-sm text-gray-600">Password</label>
                <input id="reg_password" name="password" type="password" class="w-full mt-1 px-3 py-2 border rounded-md"
                    placeholder="Create password" required>
            </div>

            <button class="w-full bg-green-600 text-white py-2 rounded-md">
                Register
            </button>
        </form>
    </div>
</div>

<script>
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');

    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    loginTab.addEventListener('click', () => {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');

        loginTab.classList.add('bg-white', 'shadow', 'text-gray-800');
        registerTab.classList.remove('bg-white', 'shadow', 'text-gray-800');
        registerTab.classList.add('text-gray-500');
    });

    registerTab.addEventListener('click', () => {
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');

        registerTab.classList.add('bg-white', 'shadow', 'text-gray-800');
        loginTab.classList.remove('bg-white', 'shadow', 'text-gray-800');
        loginTab.classList.add('text-gray-500');
    });

    // document.getElementById('registerForm').addEventListener('submit', function (e) {
    // e.preventDefault();

    // const name = document.getElementById('reg_name').value.trim();
    // const email = document.getElementById('reg_email').value.trim();
    // const password = document.getElementById('reg_password').value.trim();

    // if (!name || !email || !password) {
    //     alert("All fields required");
    //     return;
    // }

    // fetch('../Modal/auth.php', {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/x-www-form-urlencoded'
    //     },
    //     body: `action=register&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    // })
    //     .then(res => res.json())
    //     .then(data => {
    //         if (data.status) {
    //             alert("Registered successfully!");
    //             document.getElementById('registerForm').reset();
    //         } else {
    //             alert(data.message || "Registration failed");
    //         }
    //     })
    //     .catch(err => {
    //         console.error(err);
    //         alert("Something went wrong");
    //     });
    // });
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const email = document.getElementById('login_email').value.trim();
        const password = document.getElementById('login_password').value.trim();

        if (!email || !password) {
            alert("Email and password required");
            return;
        }

        fetch('../Modal/auth.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    // redirect after login
                    window.location.href = "mobile_view.php";
                } else {
                    alert(data.message || "Invalid credentials");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Something went wrong");
            });
    });
</script>