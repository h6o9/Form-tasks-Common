<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\JobNotification;
use App\Mail\UserEmailOtp;
use App\Models\EmailOtp;
use App\Models\User;
use App\Models\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        try {
            $data = $request->only('email', 'phone', 'name');

            $rules = [];
            $messages = [];

            if (!empty($data['email'])) {
                $rules['email'] = [
                    'unique:users,email',
                ];
                $messages['email.unique'] = 'This email is already taken';
            }

            if (!empty($data['phone'])) {
                $rules['phone'] = [
                    'unique:users,phone',
                ];
                $messages['phone.unique'] = 'This phone number is already taken';
            }

            $request->validate($rules, $messages);

            $otp = rand(1000, 9999);
            $otpToken = Str::uuid();

            $condition = [];

            if (!empty($request->email)) {
                $condition['email'] = $request->email;
            } else {
                $condition['phone'] = $request->phone;
            }

            EmailOtp::updateOrCreate(
                $condition,
                [
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'country' => $request->country,
                    'otp' => $otp,
                    'otp_token' => $otpToken,
                ]
            );

            // if (!empty($request->email)) {
            //     Mail::to($request->email)->send(new UserEmailOtp($otp));
            // } else {
                // Here you can implement sending OTP via SMS if needed
                // $phone = $request->phone;
                // $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
                // $twilio->messages->create($phone, [
                //     'from' => env('TWILIO_PHONE_NUMBER'),
                //     'body' => "Dear user, your One-Time Password (OTP) is $otp. Please do not share this code with anyone. - RenSolutions",
                // ]);
            // }

            return response()->json([
                'message' => !empty($request->email)
                    ? 'A verification OTP has been sent to your email'
                    : 'A verification OTP has been sent to your phone',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'message' => $firstError,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->all();

        try {
            // OTP base query
            $query = EmailOtp::where('otp', $request->otp);

            if (!empty($data['email'])) {
                $query->where('email', $data['email']);
            }

            if (!empty($data['phone'])) {
                $query->where('phone', $data['phone']);
            }

            $otpRecord = $query->latest()->first();

            if (! $otpRecord) {
                return response()->json([
                    'message' => 'Invalid OTP',
                ], 400);
            }

            return response()->json([
                'message' => 'OTP verified successfully',
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerUser(Request $request)
    {
        try {
            $request->validate(
                [
                    'email' => 'nullable|unique:users,email',
                    'phone' => 'nullable|unique:users,phone',
                    'password' => 'required|min:6',
                ],
                [
                    'email.unique' => 'This email is already taken.',
                    'phone.unique' => 'This phone number is already taken.',
                ]
            );

            // OTP Record Check
            $otpRecord = EmailOtp::where('email', $request->email)->first();
            if (! $otpRecord) {
                return response()->json([
                    'error' => 'OTP record not found for the given email.',
                ], 404);
            }

            // ✅ User Create
            $user = User::create([
                'email' => $otpRecord->email,
                'phone' => $otpRecord->phone,
                'name' => $otpRecord->name,
                'password' => Hash::make($request->password),
                'status' => is_null($otpRecord->phone) ? 1 : (is_null($otpRecord->email) ? 2 : null),
            ]);

            // ✅ Delete OTP record
            $otpRecord->delete();

            // ✅ Notification send (Push + DB)
            // Title & Description   
            return response()->json([
                'message' => 'Registered successfully.',
                'user' => $user,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

     public function requestUpdateOtp(Request $request)
    {
        try {

            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // ======= ✅ CUSTOM VALIDATION (Unique Email/Phone) =======
            try {
                $request->validate([
                    'email' => [
                        'nullable',
                        'email',
                        Rule::unique('users', 'email')->ignore($user->id),
                    ],
                    'phone' => [
                        'nullable',
                        Rule::unique('users', 'phone')->ignore($user->id),
                    ],
                ], [
                    'email.unique' => 'This email is already taken by another user.',
                    'phone.unique' => 'This phone number is already taken by another user.',
                ]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors()->first();

                return response()->json(['message' => $errors], 422);
            }

            // ======= DATA READY =======
            $data = $request->only('email', 'phone', 'name', 'image');
            $status = (string) $user->status;
            $updatedFields = [];

            // ✅ Name
            if (!empty($data['name'])) {
                $updatedFields['name'] = $data['name'];
            }

            // ✅ Image
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $imagePath = 'admin/assets/images/users/';
                $image->move(public_path($imagePath), $imageName);
                $updatedFields['image'] = $imagePath.$imageName;
            }

            $otp = null;
            $otpToken = null;
            $sendOtpTo = null;

            $pendingData = [
                'email' => $user->email,
                'phone' => $user->phone,
                'name' => $data['name'] ?? null,
                'image' => $updatedFields['image'] ?? null,
                'country' => $user->country ?? 'N/A',
            ];

			

            // ======= STATUS HANDLING =======
            if ($status === '1') {
               if (isset($data['email']) && empty($updatedFields) && empty($data['phone'])) {

                    return response()->json(['message' => "You can't update your email"], 200);
                }

				if (isset($data['phone']) && trim($data['phone']) === trim($user->phone)) {

					if (isset($updatedFields['name']) && trim($updatedFields['name']) !== '') {
						$user->name = $updatedFields['name'];
					}

					if (isset($updatedFields['image']) && trim($updatedFields['image']) !== '') {
						$user->image = $updatedFields['image'];
					}

					$user->save(); // changes persist in DB

					return response()->json([
						'message' => "Profile updated successfully."
					]);
				}
                // email locked
                if (!empty($data['phone']) && $data['phone'] !== $user->phone) {
                    $otp = rand(1000, 9999);
                    $otpToken = Str::uuid();
                    $pendingData['phone'] = $data['phone'];
                    $sendOtpTo = 'phone';
                }

                if (empty($data['phone']) && !empty($updatedFields)) {
                    $user->name = $updatedFields['name'] ?? $user->name;
                    $user->image = $updatedFields['image'] ?? $user->image;
                    $user->save();

                    return response()->json(['message' => 'Profile updated successfully.'], 200);
                }
            } elseif ($status === '2') { // phone locked
                 if (isset($data['phone']) && empty($updatedFields) && empty($data['email'])) {
                    return response()->json(['message' => "You can't update your phone"], 200);
                }

				if (isset($data['email']) && trim($data['email']) === trim($user->email)) {

					if (isset($data['name']) && trim($data['name']) !== '') {
						$user->name = $data['name'];
					}

					if (isset($data['image']) && trim($data['image']) !== '') {
						$user->image = $data['image'];
					}

					$user->save(); // changes persist in DB

					return response()->json([
						'message' => "Profile updated successfully."
					]);
				}

                if (!empty($data['email']) && $data['email'] !== $user->email) {
                    $otp = rand(1000, 9999);
                    $otpToken = Str::uuid();
                    $pendingData['email'] = $data['email'];
                    $sendOtpTo = 'email';
                }

                if (empty($data['email']) && !empty($updatedFields)) {
                    $user->name = $updatedFields['name'] ?? $user->name;
                    $user->image = $updatedFields['image'] ?? $user->image;
                    $user->save();

                    return response()->json(['message' => 'Profile updated successfully.'], 200);
                }
            } else { // no restriction

				
                if (!empty($data['email'])) {
                    $user->email = $data['email'];
                }
                if (!empty($data['phone'])) {
                    $user->phone = $data['phone'];
                }
                if (!empty($updatedFields['name'])) {
                    $user->name = $updatedFields['name'];
                }
                if (!empty($updatedFields['image'])) {
                    $user->image = $updatedFields['image'];
                }
                $user->save();

                return response()->json(['message' => 'Profile updated successfully.'], 200);
            }

            // ======= CREATE OTP IF NEEDED =======
            if ($otp && $otpToken) {
                $pendingData['otp'] = $otp;
                $pendingData['otp_token'] = $otpToken;

                 $condition = [];

				if (!empty($pendingData['email'])) {
					$condition = ['email' => $pendingData['email']];
				} elseif (!empty($pendingData['phone'])) {
					$condition = ['phone' => $pendingData['phone']];
				}

				EmailOtp::updateOrCreate(
					$condition, // search by email OR phone
					[
						'otp' => $pendingData['otp'], 
						'name' => $pendingData['name'],
						'image' => $pendingData['image'],
						'country' => $pendingData['country'],
					]
				);

                if ($sendOtpTo === 'phone') {
                    $phone = $pendingData['phone'];
                    if (substr($phone, 0, 1) !== '+') {
                        $phone = '+'.$phone;
                    }
                    try {
                    //     $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
                    //     $twilio->messages->create($phone, [
                    //         'from' => env('TWILIO_PHONE_NUMBER'),
                    //         'body' => "Your OTP is $otp",
                    //     ]
					// );
                    } catch (\Exception $e) {
                        // return response()->json(['error' => 'Twilio failed', 'message' => $e->getMessage()], 500);
                    }
                    $msg = 'A verification OTP has been sent to your phone.';
                } else {
                    // Mail::to($pendingData['email'])->send(new UserEmailOtp($otp));
                    // $msg = 'A verification OTP has been sent to your email.';
                }

                return response()->json(['message' => $msg, 'otp_token' => $otpToken], 200);
            }

            if (!empty($updatedFields)) {
                if (!empty($updatedFields['image'])) {
                    $user->image = $updatedFields['image'];
                }
                if (!empty($updatedFields['name'])) {
                    $user->name = $updatedFields['name'];
                }

                $user->save();

                return response()->json([
                    'message' => 'Profile updated successfully.',
                ], 200);

            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyAndUpdateContact(Request $request)
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }


			 try {
                $request->validate([
                    'email' => [
                        'nullable',
                        'email',
                        Rule::unique('users', 'email')->ignore($user->id),
                    ],
                    'phone' => [
                        'nullable',
                        Rule::unique('users', 'phone')->ignore($user->id),
                    ],
                ], [
                    'email.unique' => 'This email is already taken by another user.',
                    'phone.unique' => 'This phone number is already taken by another user.',
                ]);
            } catch (ValidationException $e) {
                $errors = $e->validator->errors()->first();

                return response()->json(['message' => $errors], 422);
            }

            $otpRecord = EmailOtp::where('otp', $request->otp)->first();

            if (! $otpRecord) {
                return response()->json(['error' => 'Invalid Otp'], 400);
            }

            $updated = false;

            if (!empty($otpRecord->email) && $otpRecord->email !== $user->email) {
                $user->email = $otpRecord->email;
                $updated = true;
            }

            if (!empty($otpRecord->email) && $otpRecord->phone !== $user->phone) {
                $user->phone = $otpRecord->phone;
                $updated = true;
            }

            if (!empty($otpRecord->name)) {
                $user->name = $otpRecord->name;
                $updated = true;
            }

            if (!empty($otpRecord->image)) {
                $user->image = $otpRecord->image;
                $updated = true;
            }

            if (! $updated) {
                return response()->json(['error' => 'Nothing to update'], 422);
            }

            $user->save();
            $otpRecord->delete();

            return response()->json(['message' => 'Profile updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'.$e->getMessage()], 500);
        }
    }


	 public function login(Request $request)
    {

        try {
            // Validate request

            $loginInput = $request->input('email') ?: $request->input('phone');

            // Trim and clean the input
            $loginInput = trim($loginInput);

            // Determine if login is email or phone
            $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            // For phone numbers, remove any non-digit characters

            $user = User::where($fieldType, $loginInput)->first();

            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            if (! Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid password'], 401);
            }

			 if ($user->toggle == 0) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please check your email for details or contact the administrator.'
            ], 403);
             }

            // Update FCM token
            $user->fcm = $request->fcm;
            $user->save();

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                    'phone' => $user->phone ?? null,
                    'image' => $user->image ?? null,
                    'country' => $user->country ?? null,
                    'fcm' => $user->fcm ?? null,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed: '.$e->getMessage(),
            ], 500);
        }
    }

    // Logout function
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'User not authenticated',

                ], 401);
            }

            // Revoke all tokens
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logged out failed: '.$e->getMessage(),
            ], 500);
        }
    }

	// forget passwords

	 public function forgotPassword(Request $request)
    {
        try {
            $type = $request->type; // 'email' or 'phone'
            $identifier = $request->identifier; // This will hold either email or phone

            // Validate type existence
            if (! in_array($type, ['email', 'phone'])) {
                return response()->json(['message' => 'Invalid type provided'], 400);
            }

            // Cross validation: If type = email but phone entered

            // Find user by email or phone
            $user = User::where($type, $identifier)->first();

            $label = $type === 'phone' ? 'Phone number' : 'Email';

            if (! $user) {
                return response()->json([
                    'message' => $label.' does not exist',
                ], 404);
            }

            // Generate OTP and token
            $otp = rand(1000, 9999);
            $otpToken = Str::uuid();

            // Store in database
             EmailOtp::updateOrCreate(
				[$type => $identifier], // condition (email OR phone)
				[
					'otp'       => $otp,
					'otp_token' => $otpToken,
				]
			);
            // if ($type === 'email') {
            //     Mail::to($identifier)->send(new ForgotOTPMail($otp));
            // }
            // if ($type === 'phone') {
            //     // Send SMS
            //     $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            //     $twilio->messages->create($identifier, [
            //         'from' => env('TWILIO_PHONE_NUMBER'),
            //         'body' => "Dear user, your One-Time Password (OTP) is $otp. Please do not share this code with anyone. - RenSolutions",
            //     ]);
            // }

            $msg = $type === 'phone'
            ? 'OTP sent successfully to your phone'
            : 'OTP sent successfully to your email';

            return response()->json([
                'message' => $msg,
                'otp_token' => $otpToken,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function forgotverifyOtp(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'otp' => 'required|digits:4',
                // 'otp_token' => 'required'
            ]);

            // Find OTP record
            $otpRecord = EmailOtp::where('otp_token', $request->otp_token)->latest()->first();

            if (! $otpRecord) {
                return response()->json([
                    'message' => 'Invalid OTP token',
                ], 400);
            }

            // Check OTP value
            if ($otpRecord->otp !== $request->otp) {
                return response()->json([
                    'message' => 'Invalid OTP',
                ], 402);
            }

            // Match user by email or phone
            $user = null;

            if ($otpRecord->email) {
                $user = User::where('email', $otpRecord->email)->first();
            } elseif ($otpRecord->phone) {
                $user = User::where('phone', $otpRecord->phone)->first();
            }

            if (! $user) {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }

            // Mark OTP as verified
            $otpRecord->update(['verified' => true]);

            return response()->json([
                'message' => 'OTP verified successfully',
                'otp_token' => $otpRecord->otp_token,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   

    public function resendOtp(Request $request)
    {
        try {
            $type = $request->type;
            $identifier = $request->identifier;

            if (! in_array($type, ['email', 'phone'])) {
                return response()->json(['message' => 'Invalid type provided'], 400);
            }

            $recentOtp = EmailOtp::where($type, $identifier)
                ->latest()
                ->first();

            if (! $recentOtp) {
                return response()->json([
                    'message' => 'No OTP record found for this email',
                ], 404);
            }

            $otp = rand(1000, 9999);
            $otpToken = Str::uuid();

            $recentOtp->update([
                'otp' => $otp,
                'otp_token' => $otpToken,
            ]);

            // if ($type === 'email') {
            //     Mail::to($identifier)->send(new ForgotOTPMail($otp));
            // }

            // if ($type === 'phone') {
            //     $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            //     $twilio->messages->create($identifier, [
            //         'from' => env('TWILIO_PHONE_NUMBER'),
            //         'body' => "Dear user, your One-Time Password (OTP) is $otp. Please do not share this code with anyone. - RenSolutions",
            //     ]);
            // }

         return response()->json([
    'message' => !empty($request->type === 'email')
        ? 'A verification OTP has been sent to your email'
        : 'A verification OTP has been sent to your phone',
    'otp_token' => $otpToken,
	], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kuch ghalat ho gaya',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {

        try {
            // Validate input
            $request->validate([
                'otp_token' => 'required|uuid',
                'new_password' => 'required|min:8',
            ]);

            // Fetch OTP record using token
            $otpRecord = EmailOtp::where('otp_token', $request->otp_token)->first();

            if (! $otpRecord || ! $otpRecord->verified) {
                return response()->json(['message' => 'Invalid or unverified OTP token'], 400);
            }

            // Find the user by email or phone
            if ($otpRecord->email !== null) {
                $user = User::where('email', $otpRecord->email)->first();
            } elseif ($otpRecord->phone !== null) {
                $user = User::where('phone', $otpRecord->phone)->first();
            }

            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Prevent reuse of old password
            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'message' => 'This password is already in use. Please choose a different password',
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Delete OTP record
            $otpRecord->delete();

            return response()->json([
                'message' => 'Password reset successfully',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

	public function getLoggedInUserInfo()
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'name' => $user->name ?? null,
                'image' => $user->image ? 'public/'.$user->image : 'https://ranglerzwp.xyz/myren/public/admin/assets/images/avator.png',
                'country' => $user->country ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something Went Wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

