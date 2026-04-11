<head>
    <?php require "../Common/header.php";

    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';

    session_start();

    if (isset($_SESSION['user_id'])) {
        header("Location: HomePage.php");
        exit;
    }

    ?>
    <script src="../Script/root.js"></script>
</head>

<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">

    <div class="absolute top-[20%]">
        <p class="text-3xl font-semibold">MagDyn ERP</p>
    </div>
    <!-- Card -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-md p-6">

        <!-- Tabs -->
        <div class="flex bg-gray-100 rounded-lg p-1 mb-6">
            <button id="loginTab" class="w-full py-2 text-sm font-medium rounded-md bg-white shadow text-gray-800">
                Login
            </button>
            <!-- <button id="registerTab" class="w-1/2 py-2 text-sm font-medium rounded-md text-gray-500">
                Register
            </button> -->
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="space-y-4">

            <div>
                <label class="text-sm text-gray-600">User Name</label>
                <input id="login_email" name="email" type="type" class="w-full mt-1 px-3 py-2 border rounded-md"
                    required>
            </div>

            <div>
                <label class="text-sm text-gray-600">Password</label>
                <input id="login_password" name="password" type="password"
                    class="w-full mt-1 px-3 py-2 border rounded-md" required>
            </div>

            <p id="loginError" class="text-red-500 text-sm hidden"></p>

            <button id="loginBtn"
                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center gap-2">
                <span id="loginText">Login</span>

                <!-- Spinner -->
                <svg id="loginSpinner" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                    <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z">
                    </path>
                </svg>
            </button>
        </form>

    </div>
    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>

</div>
<script type="module" src="../Script/Xoid.js"></script>

<script>
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');

    const loginForm = document.getElementById('loginForm');
    // const registerForm = document.getElementById('registerForm');

    loginTab.addEventListener('click', () => {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');

        loginTab.classList.add('bg-white', 'shadow', 'text-gray-800');
        registerTab.classList.remove('bg-white', 'shadow', 'text-gray-800');
        registerTab.classList.add('text-gray-500');
    });

    document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const email = document.getElementById('login_email').value.trim();
        const password = document.getElementById('login_password').value.trim();

        const btn = document.getElementById('loginBtn');
        const spinner = document.getElementById('loginSpinner');
        const text = document.getElementById('loginText');
        const errorBox = document.getElementById('loginError');

        // reset error
        errorBox.classList.add('hidden');
        errorBox.textContent = "";

        if (!email || !password) {
            errorBox.textContent = "Email and password required";
            errorBox.classList.remove('hidden');
            return;
        }

        // 🔄 Show spinner
        spinner.classList.remove('hidden');
        text.textContent = "Logging in...";
        btn.disabled = true;

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
                // Destroying any existing state
                window?.xoid?.destroyState();

                if (data.status) {
                    window.location.href = "HomePage.php";
                } else {
                    errorBox.textContent = data.message || "Invalid email or password";
                    errorBox.classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error(err);
                errorBox.textContent = "Something went wrong. Try again.";
                errorBox.classList.remove('hidden');
            })
            .finally(() => {
                // 🔁 Reset button
                spinner.classList.add('hidden');
                text.textContent = "Login";
                btn.disabled = false;
            });
    });

    const params = new URLSearchParams(window.location.search);

    if (params.get("reason") === "timeout") {
        showToast("Session expired. Please login again.", "info");

        // remove query param (clean URL)
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>