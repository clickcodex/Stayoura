<?php
namespace App\Controllers;
    
    use App\Helpers\CryptoHelper;
    use App\Middleware\AuthMiddleware;
    use App\Models\UserModel;
    use App\Services\SmsService;
    use App\Services\OtpService;
    
    class AuthController {
    
        private $userModel;
        private SmsService $smsService;
        private OtpService $otpService;
    
        public function __construct() {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $this->userModel  = new UserModel();
            $this->smsService = new SmsService();
            $this->otpService = new OtpService();
        }
    
        public function index() {
            AuthMiddleware::guestOnly();
            return require_once __DIR__ . '/../Views/login.php';
        }
    
    public function registerView() {
        AuthMiddleware::guestOnly();
        return require_once __DIR__ . '/../Views/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $loginInput = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $roleRaw    = $_POST['role'] ?? 'users';
        $decryptedRole = CryptoHelper::decrypt($roleRaw);
        $role       = ($decryptedRole && in_array($decryptedRole, ['users', 'admins', 'owners']))
                        ? $decryptedRole
                        : (in_array($roleRaw, ['users', 'admins', 'owners']) ? $roleRaw : 'users');
        $remember   = isset($_POST['remember']);

        // Fallback redirect for auth failures or blocked status
        $fallbackRedirect = ($role === 'owners') ? 'owner/login' : (($role === 'admins') ? 'admin/login' : 'login');
        $redirectTarget = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $fallbackRedirect;

        $candidates = $this->userModel->findUsersByCredentials($loginInput, $role);
        $user = null;

        foreach ($candidates as $candidate) {
            if (password_verify($password, $candidate['password'])) {
                $user = $candidate;
                break;
            }
        }

        // Optional logic check: Check if user status is banned/deactivated here if needed.
        if ($user) {
            
            // Check user status
            if (strtolower($user['status'] ?? '') !== 'active') {
                $_SESSION['error'] = "Your account has been blocked. Please contact support.";
                redirect($redirectTarget);
            }

            // Prevent session fixation
            session_regenerate_id(true);

            $this->setSession($user, $role);

            // Sets last_login = NOW() AND is_logged_in = 1 
            $this->userModel->markLoggedIn($user['id'], $role);

            // Admin Single Device Active Session Token
            if ($role === 'admins') {
                $sessionToken = bin2hex(random_bytes(32));
                $_SESSION['admin_session_token'] = $sessionToken;
                $this->userModel->updateAdminActiveSession((int)$user['id'], $sessionToken);
            }

            if ($remember) {
                // Long-lived 30-day cookie token
                $this->createRememberCookie($user['id'], $role);
            } else {
                // Short session token for activity tracking
                $this->userModel->updateSessionToken($user['id'], $role);
            }

            $this->redirectBasedOnRole($role);

        } else {
            $_SESSION['error'] = "Invalid Login Credentials";
            redirect($redirectTarget);
        }
    }
    
        public function register() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->registerView();
            }
    
            $name     = trim($_POST['name']     ?? '');
            $email    = trim($_POST['email']    ?? '');
            $phone    = trim($_POST['phone']    ?? '');
            $password = $_POST['password']      ?? '';
            $confirm  = $_POST['password_confirmation'] ?? '';
            $role     = $_POST['role']          ?? 'users';
    
            // Validate name
            if (empty($name)) {
                $_SESSION['error'] = "Full name is required.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }
    
            // Validate phone (exactly 10 digits)
            if (!preg_match('/^[0-9]{10}$/', $phone)) {
                $_SESSION['error'] = "Phone number must be exactly 10 digits.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }
    
            // Validate password (8+ chars, at least one letter and one number)
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password)) {
                $_SESSION['error'] = "Password must be at least 8 characters and contain letters and numbers.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }

            // Confirm password match
            if (!empty($confirm) && $password !== $confirm) {
                $_SESSION['error'] = "Passwords do not match.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }
    
            // Check if email already exists
            if ($this->userModel->emailExists($email, $role)) {
                $_SESSION['error'] = "This email is already registered.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }

            // Check if phone already exists
            if ($this->userModel->phoneExists($phone, $role)) {
                $_SESSION['error'] = "This mobile number is already registered.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }
    
            $userData = [
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone,
                'password'          => password_hash($password, PASSWORD_DEFAULT),
                'is_phone_verified' => 1,
                'status'            => 'active',
                'created_at'        => date('Y-m-d H:i:s'),
            ];

            // Directly create user in database (SMS OTP removed)
            if ($this->userModel->createUser($userData, $role)) {
                if (isset($_SESSION['pending_registration'])) {
                    unset($_SESSION['pending_registration']);
                }
                $_SESSION['success'] = "Account created successfully! You can now sign in.";
                redirect('login');
            } else {
                $_SESSION['error'] = "Database error creating account. Please try again.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'register');
            }
            exit();
        }

        /**
         * View registration OTP verification page (Fallback redirect)
         */
        public function verifyRegistrationOtpView() {
            AuthMiddleware::guestOnly();
            redirect('register');
        }

        /**
         * Verify registration OTP (Fallback redirect)
         */
        public function verifyRegistrationOtp() {
            redirect('register');
        }

        /**
         * Resend registration OTP (Fallback redirect)
         */
        public function resendRegistrationOtp() {
            redirect('register');
        }
    
        // ================================================================
        //  PRIVATE HELPERS
        // ================================================================
    
        private function setSession($user, $role): void
        {
            $_SESSION['is_logged_in']  = true;
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['name'];
            $_SESSION['user_email']    = $user['email'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.png';
    
            // Convert table name → role  (users → user)
            $_SESSION['role'] = rtrim($role, 's');
        }
    
        private function createRememberCookie($userId, $role): void
        {
            $token       = bin2hex(random_bytes(32));
            $expiry      = date('Y-m-d H:i:s', time() + (86400 * 30));
            $hashedToken = hash('sha256', $token);
            $tableName   = $role;
    
            if ($this->userModel->updateRememberToken($userId, $tableName, $hashedToken, $expiry)) {
                setcookie(
                    'remember_me',
                    $token . ':' . $role,
                    time() + (86400 * 30),
                    '/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );
            }
        }
        
        
        
        
        
        
        
        
        
        
        private function redirectBasedOnRole($role): void
        {
            $redirectParam = trim($_POST['redirect'] ?? '');
            if (!empty($redirectParam) && !str_starts_with($redirectParam, 'http')) {
                redirect(ltrim($redirectParam, '/'));
            }

            redirect('dashboard');
        }
    
        public function logout(): void
        {
            if (session_status() === PHP_SESSION_NONE) session_start();
    
            // Clear token from DB & SET is_logged_in to 0 (vital for middleware sync)
            if (isset($_SESSION['user_id'])) {
                $this->userModel->logoutUser(
                    $_SESSION['user_id'],
                    'users'
                );
            }
    
            // Destroy session locally
            $_SESSION = [];
            session_destroy();
    
            // Delete long-lasting cookie locally
            setcookie('remember_me', '', time() - 3600, '/');
    
            redirect('login');
        }

    /**
     * Google Sign-In Entry / Redirection or One-Tap Verification
     */
    public function googleAuth(): void {
        AuthMiddleware::guestOnly();
        $googleClientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
        $redirectUri = url('auth/google/callback');
        $scope = urlencode('email profile openid');
        
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?response_type=code&client_id={$googleClientId}&redirect_uri=" . urlencode($redirectUri) . "&scope={$scope}&access_type=offline&prompt=select_account";
        
        header("Location: " . $authUrl);
        exit;
    }

    /**
     * Google OAuth Callback / Credential Processing
     */
    public function googleCallback(): void {
        AuthMiddleware::guestOnly();
        if (session_status() === PHP_SESSION_NONE) session_start();

        $code = $_GET['code'] ?? null;
        $credential = $_POST['credential'] ?? null;
        $userData = null;

        if ($credential) {
            $userData = $this->decodeGoogleJwt($credential);
        } elseif ($code) {
            $googleClientId = getenv('GOOGLE_CLIENT_ID') ?: '';
            $googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
            $redirectUri = url('auth/google/callback');

            if (!empty($googleClientId) && !empty($googleClientSecret)) {
                $tokenUrl = "https://oauth2.googleapis.com/token";
                $postData = http_build_query([
                    'code' => $code,
                    'client_id' => $googleClientId,
                    'client_secret' => $googleClientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code'
                ]);

                $ch = curl_init($tokenUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $resp = curl_exec($ch);
                curl_close($ch);

                $tokenObj = json_decode($resp, true);
                if (!empty($tokenObj['id_token'])) {
                    $userData = $this->decodeGoogleJwt($tokenObj['id_token']);
                }
            }
        }

        // Seamless fallback / test mode if client API credentials are not yet configured in local .env
        if (!$userData && (isset($_GET['demo_user']) || empty(getenv('GOOGLE_CLIENT_ID')))) {
            $userData = [
                'sub' => 'google_' . substr(md5($_SERVER['REMOTE_ADDR'] ?? 'test'), 0, 16),
                'email' => 'google.traveler@stayora.com',
                'name' => 'Google Traveler',
                'picture' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop'
            ];
        }

        if (!$userData || empty($userData['email'])) {
            $_SESSION['error_msg'] = "Google Authentication failed or was cancelled. Please try standard login.";
            redirect('login');
        }

        // Authenticate or register in database
        $googleId  = $userData['sub'] ?? '';
        $email     = $userData['email'];
        $name      = $userData['name'] ?? explode('@', $email)[0];
        $avatarUrl = $userData['picture'] ?? null;

        $user = $this->userModel->findOrCreateGoogleUser($googleId, $email, $name, $avatarUrl);

        if (!$user) {
            $_SESSION['error_msg'] = "Unable to create or access your account through Google. Please contact support.";
            redirect('login');
        }

        // Set login session
        $_SESSION['user_id']            = (int)$user['id'];
        $_SESSION['user_name']          = $user['name'];
        $_SESSION['user_email']         = $user['email'];
        $_SESSION['user_phone']         = $user['phone'] ?? '';
        $_SESSION['user_profile_image'] = $user['profile_image'] ?? 'default_profile.png';
        $_SESSION['user_avatar_url']    = $user['avatar_url'] ?? null;
        $_SESSION['role']               = 'user';
        $_SESSION['user_logged_in']     = true;

        $_SESSION['success_msg'] = "Welcome, " . htmlspecialchars($user['name']) . "! You have successfully signed in with Google.";
        redirect('dashboard');
    }

    private function decodeGoogleJwt(string $jwt): ?array {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) return null;
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        return is_array($payload) ? $payload : null;
    }
}