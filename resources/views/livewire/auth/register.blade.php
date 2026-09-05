<x-user-layout>
    <div class="container-xxl col-lg-6">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <!-- Register Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-6">
                            <a href="index.html" class="app-brand-link">
                                <span class="app-brand-logo demo">
                                    <span class="text-primary">
                                        <svg
                                            width="32"
                                            height="22"
                                            viewBox="0 0 32 22"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                                fill="currentColor"
                                            />
                                            <path
                                                opacity="0.06"
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                                fill="#161616"
                                            />
                                            <path
                                                opacity="0.06"
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                                fill="#161616"
                                            />
                                            <path
                                                fill-rule="evenodd"
                                                clip-rule="evenodd"
                                                d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                                fill="currentColor"
                                            />
                                        </svg>
                                    </span>
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">MedGambit</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">Welcome To MedGambit</h4>
                        <p class="mb-6">Fast You Learning up to 50%</p>

                        <form
                            id="formAuthentication"
                            class="mb-6"
                            action="{{ route('register.store') }}"
                            method="POST"
                            enctype="multipart/form-data"
                        >
                            @csrf
                            <div class="form-control-validation mb-6">
                                <label for="username" class="form-label">Username</label>
                                <input
                                    name="name"
                                    value="{{ old('name') }}"
                                    type="text"
                                    autofocus
                                    autocomplete="name"
                                    class="form-control"
                                    id="username"
                                    placeholder="Enter your name"
                                    autofocus
                                />
                                @error('name')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-control-validation mb-6">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    class="form-control"
                                    name="email"
                                    :label="__('Email address')"
                                    value="{{ old('email') }}"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="Enter your email"
                                />
                                @error('email')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="my-4">
                                <label class="for m-lable">Gender </label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="male" @selected(old("gender") ==="male")>Male</option>
                                    <option value="female" @selected(old("gender") ==="female")>Female</option>
                                </select>
                                @error('gender')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-control-validation mb-6">
                                <label for="country" class="form-label">Country</label>
                                <select name="country" class="form-select select2">
                                    <option value="">Select your country</option>
                                    <option value="AF" @selected(old('country', $country ?? '') === 'AF')>
                                        Afghanistan
                                    </option>
                                    <option value="DZ" @selected(old('country', $country ?? '') === 'DZ')>
                                        Algeria
                                    </option>
                                    <option value="AR" @selected(old('country', $country ?? '') === 'AR')>
                                        Argentina
                                    </option>
                                    <option value="AU" @selected(old('country', $country ?? '') === 'AU')>
                                        Australia
                                    </option>
                                    <option value="AT" @selected(old('country', $country ?? '') === 'AT')>
                                        Austria
                                    </option>
                                    <option value="BD" @selected(old('country', $country ?? '') === 'BD')>
                                        Bangladesh
                                    </option>
                                    <option value="BE" @selected(old('country', $country ?? '') === 'BE')>
                                        Belgium
                                    </option>
                                    <option value="BR" @selected(old('country', $country ?? '') === 'BR')>
                                        Brazil
                                    </option>
                                    <option value="CA" @selected(old('country', $country ?? '') === 'CA')>
                                        Canada
                                    </option>
                                    <option value="CN" @selected(old('country', $country ?? '') === 'CN')>China</option>
                                    <option value="CO" @selected(old('country', $country ?? '') === 'CO')>
                                        Colombia
                                    </option>
                                    <option value="CY" @selected(old('country', $country ?? '') === 'CY')>
                                        Cyprus
                                    </option>
                                    <option value="CZ" @selected(old('country', $country ?? '') === 'CZ')>
                                        Czech Republic
                                    </option>
                                    <option value="DK" @selected(old('country', $country ?? '') === 'DK')>
                                        Denmark
                                    </option>
                                    <option value="EG" @selected(old('country', $country ?? '') === 'EG')>Egypt</option>
                                    <option value="FI" @selected(old('country', $country ?? '') === 'FI')>
                                        Finland
                                    </option>
                                    <option value="FR" @selected(old('country', $country ?? '') === 'FR')>
                                        France
                                    </option>
                                    <option value="DE" @selected(old('country', $country ?? '') === 'DE')>
                                        Germany
                                    </option>
                                    <option value="GR" @selected(old('country', $country ?? '') === 'GR')>
                                        Greece
                                    </option>
                                    <option value="IN" @selected(old('country', $country ?? '') === 'IN')>India</option>
                                    <option value="ID" @selected(old('country', $country ?? '') === 'ID')>
                                        Indonesia
                                    </option>
                                    <option value="IQ" @selected(old('country', $country ?? '') === 'IQ')>Iraq</option>
                                    <option value="IE" @selected(old('country', $country ?? '') === 'IE')>
                                        Ireland
                                    </option>
                                    <option value="IT" @selected(old('country', $country ?? '') === 'IT')>Italy</option>
                                    <option value="JP" @selected(old('country', $country ?? '') === 'JP')>Japan</option>
                                    <option value="JO" @selected(old('country', $country ?? '') === 'JO')>
                                        Jordan
                                    </option>
                                    <option value="KW" @selected(old('country', $country ?? '') === 'KW')>
                                        Kuwait
                                    </option>
                                    <option value="LB" @selected(old('country', $country ?? '') === 'LB')>
                                        Lebanon
                                    </option>
                                    <option value="MY" @selected(old('country', $country ?? '') === 'MY')>
                                        Malaysia
                                    </option>
                                    <option value="MX" @selected(old('country', $country ?? '') === 'MX')>
                                        Mexico
                                    </option>
                                    <option value="MA" @selected(old('country', $country ?? '') === 'MA')>
                                        Morocco
                                    </option>
                                    <option value="NL" @selected(old('country', $country ?? '') === 'NL')>
                                        Netherlands
                                    </option>
                                    <option value="NZ" @selected(old('country', $country ?? '') === 'NZ')>
                                        New Zealand
                                    </option>
                                    <option value="NG" @selected(old('country', $country ?? '') === 'NG')>
                                        Nigeria
                                    </option>
                                    <option value="NO" @selected(old('country', $country ?? '') === 'NO')>
                                        Norway
                                    </option>
                                    <option value="OM" @selected(old('country', $country ?? '') === 'OM')>Oman</option>
                                    <option value="PK" @selected(old('country', $country ?? '') === 'PK')>
                                        Pakistan
                                    </option>
                                    <option value="PS" @selected(old('country', $country ?? '') === 'PS')>
                                        Palestine
                                    </option>
                                    <option value="PE" @selected(old('country', $country ?? '') === 'PE')>Peru</option>
                                    <option value="PH" @selected(old('country', $country ?? '') === 'PH')>
                                        Philippines
                                    </option>
                                    <option value="PL" @selected(old('country', $country ?? '') === 'PL')>
                                        Poland
                                    </option>
                                    <option value="PT" @selected(old('country', $country ?? '') === 'PT')>
                                        Portugal
                                    </option>
                                    <option value="QA" @selected(old('country', $country ?? '') === 'QA')>Qatar</option>
                                    <option value="RO" @selected(old('country', $country ?? '') === 'RO')>
                                        Romania
                                    </option>
                                    <option value="RU" @selected(old('country', $country ?? '') === 'RU')>
                                        Russia
                                    </option>
                                    <option value="SA" @selected(old('country', $country ?? '') === 'SA')>
                                        Saudi Arabia
                                    </option>
                                    <option value="SG" @selected(old('country', $country ?? '') === 'SG')>
                                        Singapore
                                    </option>
                                    <option value="ZA" @selected(old('country', $country ?? '') === 'ZA')>
                                        South Africa
                                    </option>
                                    <option value="KR" @selected(old('country', $country ?? '') === 'KR')>
                                        South Korea
                                    </option>
                                    <option value="ES" @selected(old('country', $country ?? '') === 'ES')>Spain</option>
                                    <option value="SE" @selected(old('country', $country ?? '') === 'SE')>
                                        Sweden
                                    </option>
                                </select>
                                @error('country')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- ? Graduated --}}
                            <div x-data="{ show: false }">
                                <div class="my-4">
                                    <label class="for m-lable">Graduated</label>
                                    <select name="graduated" x-on:change="show = ! show" class="form-select">
                                        <option value="true" @selected(old('graduated') == 'true')>Yes</option>
                                        <option value="false" @selected(old('graudated') == 'false')>No</option>
                                    </select>
                                    @error('graduated')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="my-4" x-show="show">
                                    <label class="form-lable">Year</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        placeholder="Enter Year"
                                        min="1"
                                        max="6"
                                        name="year"
                                        value="{{ old('year') }}"
                                    />
                                </div>
                                @error('year')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- ? End Graduated --}}
                            <div x-data="{ showImage: false }">
                                <div class="my-4">
                                    <label class="for m-lable">Add Image </label>
                                    <select x-on:change="showImage = ! showImage" class="form-select">
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                                <div x-show="showImage">
                                    <div class="preview">
                                        <img width="100%" height="300px" src="" alt="" id="preview" />
                                    </div>
                                    <div class="d-flex justify-content-center align-items-center border-secondary my-4 rounded border border-dashed py-10">
                                        <div class="fallback">
                                            <input class="" id="userImage" name="image" type="file" />
                                        </div>
                                    </div>
                                    @error('image')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="my-4">
                                <label class="for m-lable">Where Did You Know About Us </label>
                                <select name="know_about_us" class="form-select">
                                    <option value="friend" @selected(old('know_about_us') === 'friend')>Friend</option>
                                    <option value="social" @selected(old('know_about_us') === 'social')>Social</option>
                                </select>
                                @error('know_about_us')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-password-toggle form-control-validation mb-6">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password"
                                    />
                                    <span class="input-group-text cursor-pointer"
                                        ><i class="icon-base ti tabler-eye-off"></i
                                    ></span>
                                </div>
                                @error('password')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-password-toggle form-control-validation mb-6">
                                <label class="form-label" for="password">Password Confirmation</label>
                                <div class="input-group input-group-merge">
                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password_confirmation"
                                        placeholder="Password Confirmation"
                                        aria-describedby="password"
                                    />
                                    <span class="input-group-text cursor-pointer"
                                        ><i class="icon-base ti tabler-eye-off"></i
                                    ></span>
                                </div>
                                @error('password_confirmation')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <button class="btn btn-primary d-grid w-100">Sign up</button>
                        </form>
                        <p class="text-center">
                            <span>Already have an account?</span>
                            <a href="{{ route('login') }}">
                                <span>Sign in instead</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- Register Card -->
            </div>
        </div>
    </div>
    <script>
        let input = document.getElementById('userImage');
        let preview = document.getElementById('preview');
        if (preview) {
            preview.style.display = 'none';
        }
        input.addEventListener('change', () => {
            const file = input.files[0];
            preview.style.display = 'block';
            if (file) {
                preview.src = URL.createObjectURL(file);
            }
        });
    </script>
    {{--
  <script>
    function selected(e) {
      if (e.target.value === "false") {
        document.getElementById("year").style.display = "flex";
      } else {
        document.getElementById("year").style.display = "none";
      }
    }
  </script> --}}
</x-user-layout>
