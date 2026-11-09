{{-- Auth App Script specifically for handling captchas --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('authModal', () => ({
            num1: 0,
            num2: 0,
            userAnswer: '',
            
            init() {
                this.generateCaptcha();
            },
            
            generateCaptcha() {
                this.num1 = Math.floor(Math.random() * 9) + 1;
                this.num2 = Math.floor(Math.random() * 9) + 1;
                this.userAnswer = '';
            },
            
            validateCaptcha(e) {
                if (parseInt(this.userAnswer) !== (this.num1 + this.num2)) {
                    e.preventDefault();
                    alert('Jawaban captcha salah! Silakan hitung kembali.');
                    this.generateCaptcha();
                    return false;
                }
                return true;
            }
        }));
    });
</script>

{{-- Login Modal --}}
<div x-show="loginModalOpen" 
     class="fixed inset-0 z-[100] overflow-y-auto" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        {{-- Background overlay --}}
        <div x-show="loginModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="loginModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal panel --}}
        <div x-show="loginModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-data="authModal"
             class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-4xl w-full border border-slate-100/50">
            
            <div class="flex flex-col md:flex-row h-full">
                {{-- Left Side: Branding / Banner (Hidden on Mobile) --}}
                <div class="hidden md:flex md:w-5/12 bg-green-700 relative flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 opacity-20">
                        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid)"/>
                        </svg>
                    </div>
                    
                    {{-- Decorative Blobs --}}
                    <div class="absolute -top-24 -left-24 w-64 h-64 bg-green-500 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-amber-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

                    <div class="p-10 relative z-10 flex flex-col h-full justify-center">
                        <div class="w-16 h-16 bg-white rounded-2xl p-2 mb-8 shadow-lg flex items-center justify-center">
                            <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-3xl font-bold text-white font-heading mb-4 leading-tight">Selamat Datang Kembali!</h3>
                        <p class="text-green-50 text-base leading-relaxed opacity-90">
                            Silakan masuk untuk melanjutkan proses pendaftaran, melengkapi dokumen, atau memantau status putra-putri Anda.
                        </p>
                    </div>
                </div>

                {{-- Right Side: Form --}}
                <div class="w-full md:w-7/12 bg-white relative">
                    {{-- Close Button --}}
                    <button @click="loginModalOpen = false" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors focus:outline-none z-10">
                        <i class="fas fa-times text-lg"></i>
                    </button>

                    <div class="p-8 sm:p-12">
                        <div class="md:hidden flex items-center gap-3 mb-8">
                            <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                            <h3 class="text-2xl font-bold text-slate-800 font-heading">Login Akun</h3>
                        </div>

                        <div class="hidden md:block mb-8">
                            <h3 class="text-2xl font-bold text-slate-800 font-heading">Login ke Dashboard</h3>
                            <p class="text-slate-500 mt-1">Masukkan kredensial Anda untuk mengakses portal.</p>
                        </div>

                        {{-- Alert Error jika gagal login --}}
                        @if(session('error') || ($errors->any() && !old('name')))
                            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg flex items-start gap-3">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-red-800">Login Gagal</h4>
                                    <p class="text-sm text-red-600 mt-1">Email atau password yang Anda masukkan tidak sesuai. Silakan coba lagi.</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('loginproses') }}" method="post" @submit="validateCaptcha($event)">
                            @csrf
                            <div class="space-y-6">
                                {{-- Email / Username Input --}}
                                <div>
                                    <label for="loginEmail" class="block text-sm font-semibold text-slate-700 mb-2">Email / Username <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <input type="text" name="email" id="loginEmail" value="{{ old('email') }}" 
                                               class="pl-11 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-medium" 
                                               placeholder="nama@email.com atau username" required>
                                    </div>
                                    @error('email')
                                        <p class="text-red-500 text-xs font-medium mt-1.5"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                {{-- Password Input --}}
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label for="loginPassword" class="block text-sm font-semibold text-slate-700">Password <span class="text-red-500">*</span></label>
                                        <button type="button" @click="loginModalOpen = false; setTimeout(() => forgotPasswordModalOpen = true, 200)" class="text-xs font-semibold text-green-600 hover:text-green-700 hover:underline cursor-pointer focus:outline-none">
                                            Lupa password?
                                        </button>
                                    </div>
                                    <div class="relative" x-data="{ show: false }">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <input :type="show ? 'text' : 'password'" name="password" id="loginPassword" 
                                               class="pl-11 pr-11 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-medium" 
                                               placeholder="••••••••" required>
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-green-600 focus:outline-none">
                                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <p class="text-red-500 text-xs font-medium mt-1.5"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Captcha --}}
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Verifikasi Keamanan</label>
                                    <div class="flex gap-3">
                                        <div class="shrink-0 flex items-center justify-center gap-2 bg-slate-100 border border-slate-200 px-4 py-3 rounded-xl font-bold text-lg text-slate-700 shadow-inner min-w-[120px]">
                                            <span x-text="num1"></span>
                                            <span class="text-green-600">+</span>
                                            <span x-text="num2"></span>
                                            <span class="text-slate-400">=</span>
                                        </div>
                                        <input type="hidden" name="num1" :value="num1">
                                        <input type="hidden" name="num2" :value="num2">
                                        <input type="number" name="captcha" x-model="userAnswer" 
                                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-900 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-bold text-lg placeholder-slate-300 text-center" 
                                               placeholder="?" required>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-green-600/30 hover:shadow-green-600/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 mt-4">
                                    Masuk ke Dashboard <i class="fas fa-arrow-right text-sm"></i>
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-8 text-center border-t border-slate-100 pt-6">
                            <p class="text-sm font-medium text-slate-600">
                                Belum mendaftar? 
                                <button @click="loginModalOpen = false; setTimeout(() => registerModalOpen = true, 300)" class="text-green-600 font-bold hover:text-green-700 hover:underline">
                                    Buat Akun Sekarang
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Register Modal --}}
<div x-show="registerModalOpen" 
     class="fixed inset-0 z-[100] overflow-y-auto" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div x-show="registerModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="registerModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="registerModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-data="authModal"
             class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-4xl w-full border border-slate-100/50">
            
            <div class="flex flex-col md:flex-row h-full">
                {{-- Left Side: Branding / Banner (Hidden on Mobile) --}}
                <div class="hidden md:flex md:w-5/12 bg-amber-500 relative flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 opacity-20">
                        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <circle cx="2" cy="2" r="2" fill="white"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#dots)"/>
                        </svg>
                    </div>
                    
                    {{-- Decorative Blobs --}}
                    <div class="absolute -top-24 -left-24 w-64 h-64 bg-amber-300 rounded-full mix-blend-multiply filter blur-3xl opacity-70"></div>
                    <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-40"></div>

                    <div class="p-10 relative z-10 flex flex-col h-full justify-center">
                        <div class="w-16 h-16 bg-white rounded-2xl p-2 mb-8 shadow-lg flex items-center justify-center">
                            <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-3xl font-bold text-white font-heading mb-4 leading-tight">Mulai Pendaftaran Anda</h3>
                        <div class="space-y-4 text-amber-50">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle mt-1 opacity-80"></i>
                                <p class="text-sm font-medium">Buat akun dengan mudah</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle mt-1 opacity-80"></i>
                                <p class="text-sm font-medium">Isi formulir pendaftaran online</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle mt-1 opacity-80"></i>
                                <p class="text-sm font-medium">Pantau hasil seleksi secara realtime</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: Form --}}
                <div class="w-full md:w-7/12 bg-white relative max-h-[90vh] overflow-y-auto">
                    {{-- Close Button --}}
                    <button @click="registerModalOpen = false" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-colors focus:outline-none z-10">
                        <i class="fas fa-times text-lg"></i>
                    </button>

                    <div class="p-6 sm:p-10">
                        <div class="md:hidden flex items-center gap-3 mb-6">
                            <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo" class="w-10 h-10 object-contain">
                            <h3 class="text-2xl font-bold text-slate-800 font-heading">Registrasi</h3>
                        </div>

                        <div class="hidden md:block mb-6">
                            <h3 class="text-2xl font-bold text-slate-800 font-heading">Registrasi Akun Baru</h3>
                        </div>

                        {{-- Alert Error Validation --}}
                        @if($errors->any() && (old('name') || old('email') || old('username')))
                            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                                <p class="text-sm font-bold text-red-800 mb-1">Terdapat Kesalahan:</p>
                                <ul class="list-disc pl-5 text-xs text-red-600 space-y-1 font-medium">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('registrasi') }}" method="post" @submit="
                            if(document.getElementById('newPassword').value !== document.getElementById('confirmPassword').value) {
                                $event.preventDefault();
                                alert('Konfirmasi password tidak cocok!');
                                return false;
                            }
                            return validateCaptcha($event);
                        ">
                            @csrf
                            <div class="space-y-4">
                                {{-- Nama Casis --}}
                                <div>
                                    <label for="newName" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Nama Lengkap Casis <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <input type="text" name="name" id="newName" value="{{ old('name') }}"
                                               class="pl-11 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-medium text-sm" 
                                               placeholder="Sesuai Akta Kelahiran" required>
                                    </div>
                                </div>

                                {{-- Username (Opsional) --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="regUsername" class="block text-sm font-semibold text-slate-700">
                                            Username
                                        </label>
                                        <span class="text-xs text-slate-400 font-medium">Opsional</span>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-at"></i>
                                        </div>
                                        <input type="text" name="username" id="regUsername" value="{{ old('username') }}"
                                               class="pl-11 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-medium text-sm" 
                                               placeholder="Contoh: budi_santoso">
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin dibuat otomatis dari alamat email.</p>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="newEmail" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Email Aktif <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <input type="email" name="email" id="newEmail" value="{{ old('email') }}"
                                               class="pl-11 w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-medium text-sm" 
                                               placeholder="nama@email.com" required>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-4">
                                    {{-- Password --}}
                                    <div x-data="{
                                        show: false,
                                        pw: '',
                                        get strength() {
                                            if (this.pw.length === 0) return 0;
                                            if (this.pw.length <= 10) return 1; // Lemah (1 - 10)
                                            if (this.pw.length <= 14) return 2; // Sedang (11 - 14)
                                            return 3; // Kuat (15 - 20+)
                                        }
                                    }">
                                        <label for="newPassword" class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fas fa-lock text-sm"></i>
                                            </div>
                                            <input :type="show ? 'text' : 'password'" name="password" id="newPassword" x-model="pw"
                                                   class="pl-9 pr-9 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all text-sm font-medium" 
                                                   placeholder="Min. 8 karakter" required minlength="8">
                                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-amber-500 focus:outline-none">
                                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                        {{-- Strength Bar --}}
                                        <div class="mt-2 space-y-1.5" x-show="pw.length > 0">
                                            <div class="flex gap-1 h-1">
                                                <div class="flex-1 rounded-full transition-colors duration-300"
                                                    :class="strength >= 1 ? 'bg-red-500' : 'bg-slate-200'">
                                                </div>

                                                <div class="flex-1 rounded-full transition-colors duration-300"
                                                    :class="strength >= 2 ? 'bg-amber-500' : 'bg-slate-200'">
                                                </div>

                                                <div class="flex-1 rounded-full transition-colors duration-300"
                                                    :class="strength >= 3 ? 'bg-green-500' : 'bg-slate-200'">
                                                </div>
                                            </div>

                                            <div class="flex justify-between text-xs">
                                                <span class="text-slate-400" x-text="pw.length + ' karakter'"></span>

                                                <span class="font-semibold"
                                                    :class="{
                                                        'text-red-500': strength === 1,
                                                        'text-amber-500': strength === 2,
                                                        'text-green-600': strength === 3
                                                    }"
                                                    x-text="
                                                        strength === 1 ? 'Lemah' :
                                                        (strength === 2 ? 'Sedang' : 'Kuat')
                                                    ">
                                                </span>
                                            </div>

                                            {{-- Keterangan --}}
                                            <div class="text-[11px] text-slate-400">
                                                <span></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Konfirmasi Password --}}
                                    <div>
                                        <label for="confirmPassword" class="block text-sm font-semibold text-slate-700 mb-1.5">Ulangi Password <span class="text-red-500">*</span></label>
                                        <div class="relative" x-data="{ show: false }">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fas fa-check-circle text-sm"></i>
                                            </div>
                                            <input :type="show ? 'text' : 'password'" name="password_confirmation" id="confirmPassword" 
                                                   class="pl-9 pr-9 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all text-sm font-medium" 
                                                   placeholder="Ulangi password" required>
                                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-amber-500 focus:outline-none">
                                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Captcha --}}
                                <div class="pt-2">
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Verifikasi Keamanan</label>
                                    <div class="flex gap-3">
                                        <div class="shrink-0 flex items-center justify-center gap-2 bg-slate-100 border border-slate-200 px-3 py-2.5 rounded-xl font-bold text-base text-slate-700 shadow-inner min-w-[100px]">
                                            <span x-text="num1"></span>
                                            <span class="text-amber-500">+</span>
                                            <span x-text="num2"></span>
                                            <span class="text-slate-400">=</span>
                                        </div>
                                        <input type="hidden" name="num1" :value="num1">
                                        <input type="hidden" name="num2" :value="num2">
                                        <input type="number" name="captcha" x-model="userAnswer" 
                                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all font-bold text-base placeholder-slate-300 text-center" 
                                               placeholder="?" required>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-amber-500/30 hover:shadow-amber-500/40 hover:-translate-y-0.5 transition-all mt-4">
                                    Daftar Sekarang
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-6 text-center border-t border-slate-100 pt-5">
                            <p class="text-sm font-medium text-slate-600">
                                Sudah punya akun? 
                                <button @click="registerModalOpen = false; setTimeout(() => loginModalOpen = true, 300)" class="text-amber-600 font-bold hover:text-amber-700 hover:underline">
                                    Masuk di sini
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Forgot Password Modal --}}
<div x-show="forgotPasswordModalOpen" 
     class="fixed inset-0 z-[100] overflow-y-auto" 
     aria-labelledby="forgot-password-modal-title" role="dialog" aria-modal="true" style="display: none;"
     @keydown.escape.window="forgotPasswordModalOpen = false">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        {{-- Background overlay --}}
        <div x-show="forgotPasswordModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="forgotPasswordModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        {{-- Modal panel --}}
        <div x-show="forgotPasswordModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-data="authModal"
             class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-4xl w-full border border-slate-100/50">
            
            <div class="flex flex-col md:flex-row h-full">
                {{-- Left Side: Branding / Banner (Hidden on Mobile) --}}
                <div class="hidden md:flex md:w-5/12 bg-emerald-800 relative flex-col justify-between overflow-hidden p-10 text-white" style="background: linear-gradient(145deg, #065f46 0%, #064e3b 60%, #022c22 100%);">
                    <div class="absolute inset-0 opacity-15">
                        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="dots-forgot" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <circle cx="2" cy="2" r="2" fill="white"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#dots-forgot)"/>
                        </svg>
                    </div>

                    {{-- Decorative Blobs --}}
                    <div class="absolute -top-20 -left-20 w-60 h-60 bg-emerald-600 rounded-full mix-blend-screen filter blur-3xl opacity-30"></div>
                    <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-amber-500 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>

                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white rounded-2xl p-2 mb-6 shadow-lg flex items-center justify-center">
                            <img src="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="inline-block px-3 py-1 bg-white/15 backdrop-blur-md text-emerald-200 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">
                            Pusat Bantuan Akun
                        </span>
                        <h3 class="text-2xl font-bold font-heading mb-3 leading-tight">Lupa Kata Sandi?</h3>
                        <p class="text-emerald-100/90 text-sm leading-relaxed mb-6">
                            Jangan khawatir, Anda dapat mereset kata sandi secara mandiri menggunakan nomor HP WhatsApp atau NIK calon siswa yang terdaftar.
                        </p>
                        
                        <div class="space-y-3 text-xs text-emerald-100/80">
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-shield-alt text-emerald-300 mt-1 shrink-0"></i>
                                <span>Verifikasi aman mencocokkan data akun Anda di sistem.</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="fas fa-check-circle text-emerald-300 mt-1 shrink-0"></i>
                                <span>Password baru langsung aktif untuk masuk ke portal.</span>
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp Quick Contact --}}
                    <div class="relative z-10 pt-6 border-t border-emerald-700/50 mt-6">
                        <p class="text-xs text-emerald-200 mb-2 font-medium">Masih butuh bantuan panitia?</p>
                        <a href="https://wa.me/6281234567890?text=Halo%20Panitia%20PPDB%20SD%20Kristen%20Rantai%20Damai,%20saya%20membutuhkan%20bantuan%20reset%20password%20akun%20PPDB" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-700/70 hover:bg-emerald-600/80 text-white text-xs font-semibold border border-emerald-500/30 transition-all shadow-sm">
                            <i class="fab fa-whatsapp text-green-300 text-sm"></i>
                            <span>Hubungi Panitia (0812-3456-7890)</span>
                        </a>
                    </div>
                </div>

                {{-- Right Side: Form --}}
                <div class="w-full md:w-7/12 p-8 sm:p-10 relative flex flex-col justify-between">
                    {{-- Close Button --}}
                    <button @click="forgotPasswordModalOpen = false" 
                            class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-lg hover:bg-slate-100 focus:outline-none"
                            aria-label="Tutup Modal">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div>
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-slate-900 font-heading" id="forgot-password-modal-title">Reset Password</h3>
                            <p class="text-slate-500 text-sm mt-1">Masukkan email/username dan nomor HP atau NIK siswa terdaftar.</p>
                        </div>

                        {{-- Alert Error --}}
                        @if(session('forgot_error'))
                            <div class="mb-5 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg flex items-start gap-3">
                                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-red-800">Verifikasi Gagal</h4>
                                    <p class="text-xs text-red-700 mt-1 leading-relaxed">{{ session('forgot_error') }}</p>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('password.reset.public') }}" method="post" @submit="validateCaptcha($event)">
                            @csrf
                            <div class="space-y-4">
                                {{-- Email / Username Input --}}
                                <div>
                                    <label for="forgotEmail" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Email atau Username <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <input type="text" name="email_reset" id="forgotEmail" value="{{ old('email_reset') }}" 
                                               class="pl-10 w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all text-sm font-medium" 
                                               placeholder="nama@email.com atau username" required>
                                    </div>
                                    @error('email_reset')
                                        <p class="text-red-500 text-xs font-medium mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Nomor HP / NIK Casis Input --}}
                                <div>
                                    <label for="forgotNoHp" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Nomor HP Terdaftar / NIK Siswa <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <input type="text" name="no_hp_reset" id="forgotNoHp" value="{{ old('no_hp_reset') }}" 
                                               class="pl-10 w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all text-sm font-medium" 
                                               placeholder="08xxxxxxxxxx atau 16 digit NIK" required>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        <i class="fas fa-shield-alt text-emerald-600 mr-1"></i>Digunakan untuk memvalidasi kepemilikan akun Anda secara otomatis.
                                    </p>
                                    @error('no_hp_reset')
                                        <p class="text-red-500 text-xs font-medium mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Password Baru & Konfirmasi (2 kolom pada layar lebih lebar) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    {{-- Password Baru --}}
                                    <div>
                                        <label for="forgotNewPw" class="block text-sm font-semibold text-slate-700 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                                        <div class="relative" x-data="{ show: false }">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fas fa-lock text-sm"></i>
                                            </div>
                                            <input :type="show ? 'text' : 'password'" name="password" id="forgotNewPw" 
                                                   class="pl-9 pr-9 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all text-sm font-medium" 
                                                   placeholder="Min. 6 karakter" required>
                                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-green-600 focus:outline-none">
                                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="text-red-500 text-xs font-medium mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Konfirmasi Password --}}
                                    <div>
                                        <label for="forgotConfirmPw" class="block text-sm font-semibold text-slate-700 mb-1.5">Ulangi Password <span class="text-red-500">*</span></label>
                                        <div class="relative" x-data="{ show: false }">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fas fa-check-circle text-sm"></i>
                                            </div>
                                            <input :type="show ? 'text' : 'password'" name="password_confirmation" id="forgotConfirmPw" 
                                                   class="pl-9 pr-9 w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all text-sm font-medium" 
                                                   placeholder="Ulangi password" required>
                                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-green-600 focus:outline-none">
                                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Captcha --}}
                                <div class="pt-1">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Verifikasi Keamanan <span class="text-red-500">*</span></label>
                                    <div class="flex gap-3">
                                        <div class="shrink-0 flex items-center justify-center gap-2 bg-slate-100 border border-slate-200 px-3 py-2.5 rounded-xl font-bold text-base text-slate-700 shadow-inner min-w-[100px]">
                                            <span x-text="num1"></span>
                                            <span class="text-green-600">+</span>
                                            <span x-text="num2"></span>
                                            <span class="text-slate-400">=</span>
                                        </div>
                                        <input type="hidden" name="num1" :value="num1">
                                        <input type="hidden" name="num2" :value="num2">
                                        <input type="number" name="captcha" x-model="userAnswer" 
                                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 focus:bg-white focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-bold text-base placeholder-slate-300 text-center" 
                                               placeholder="?" required>
                                    </div>
                                    @error('captcha')
                                        <p class="text-red-500 text-xs font-medium mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-green-600/30 hover:shadow-green-600/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 mt-2">
                                    <i class="fas fa-key text-sm"></i> Perbarui Kata Sandi
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="mt-6 text-center border-t border-slate-100 pt-4 space-y-2">
                        <p class="text-xs text-slate-500 md:hidden">
                            Butuh bantuan? 
                            <a href="https://wa.me/6281234567890?text=Halo%20Panitia%20PPDB%20SD%20Kristen%20Rantai%20Damai,%20saya%20membutuhkan%20bantuan%20reset%20password%20akun%20PPDB" target="_blank" class="text-emerald-700 font-semibold hover:underline inline-flex items-center gap-1">
                                <i class="fab fa-whatsapp text-emerald-600"></i> WhatsApp Panitia
                            </a>
                        </p>
                        <p class="text-sm font-medium text-slate-600">
                            Sudah ingat password Anda? 
                            <button @click="forgotPasswordModalOpen = false; setTimeout(() => loginModalOpen = true, 200)" class="text-green-600 font-bold hover:text-green-700 hover:underline">
                                Masuk ke Akun
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
