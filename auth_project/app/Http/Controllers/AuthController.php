<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\PasswordService;
use App\Services\TokenService;

class AuthController extends Controller
{
    function login(Request $request) {
        try {
            $validatedData = $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $userQuery = User::query()->where('email', $validatedData['email']);

            if (!$userQuery->exists()) {
                return $this->returnBadResponse(404, 'User with this email does not exist');
            }

            $user = $userQuery->first();
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            if (!PasswordService::checkPassword($user->password, $validatedData['password'])) {
                return $this->returnBadResponse(400, 'Wrong password');
            }

            return TokenService::getTokensPair($user);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function register(Request $request) {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'fullName' => 'required|string|max:255',
                'birthDate' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|before_or_equal:now|after:1900-01-01',
                'gender' => 'nullable|in:male,female',
                'phone' => 'required|string|regex:/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/i',
                'address' => 'nullable|string|min:3',
            ]);

            $user = Customer::create([
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
                'fullName' => $validatedData['fullName'],
                'birthDate' => \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $validatedData['birthDate']) ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'] ?? null
            ]);

            return TokenService::getTokensPair($user);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function logout(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            $user->token = null;
            $user->save();

            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function refresh(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return $this->returnBadResponse(401, 'Unauthorized');
            }

            $userQuery = User::query()->where('token', $token);

            if (!$userQuery->exists()) {
                return $this->returnBadResponse(404, 'Undefined user with this token');
            }

            $user = User::query()->where('token', $token)->first();
            $time = Carbon::parse($user->tokenGetTime);

            if ($time->diffInMonths(Carbon::now()) >= 3) {
                $user->token = null;
                $user->tokenGetTime = null;
                $user->save();
                return $this->returnBadResponse(403, 'Token expired');
            }

            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            return TokenService::getTokensPair($user);
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }


    function getProfile(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            return $user;
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function putProfile(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            $validatedData = $request->validate([
                'fullName' => 'required|string|max:255',
                'birthDate' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|before_or_equal:now|after:1900-01-01',
                'gender' => 'nullable|in:male,female',
                'phone' => 'required|string|regex:/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/i',
                'address' => 'nullable|string|min:3',
            ]);

            $user->update([
                'fullName' => $validatedData['fullName'],
                'birthDate' => \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', $validatedData['birthDate']) ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'] ?? null
            ]);

            return response('', 200);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        }  catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function getRoles(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            return TokenService::getRoles($user);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function changePassword(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            $validatedData = $request->validate([
                'oldPassword' => 'required|string|max:255',
                'password' => 'required|string|max:255',
            ]);

            if (!PasswordService::checkPassword($user->password, $validatedData['oldPassword'])) {
                return $this->returnBadResponse(400, 'Wrong password');
            }

            $user->password = PasswordService::codePassword($validatedData['password']);
            $user->save();

            return response('', 200);
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }

    function getRestaurant(Request $request) {
        try {
            $userId = $request->input('userId');
            $user = User::findOrFail($userId);

            if ($user->token == null) {
                return $this->returnBadResponse(403, 'Token expired');
            }
            if ($user->isBanned) {
                return $this->returnBadResponse(403, 'User banned');
            }

            return [
                'restaurantId' => TokenService::getRestaurant($user)
            ];
        } catch (ValidationException $e) {
            return $this->returnBadResponse(400, $e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->returnBadResponse(404, 'Undefined user');
        } catch (Throwable $e) {
            return $this->returnBadResponse(500, $e->getMessage());
        }
    }
}
