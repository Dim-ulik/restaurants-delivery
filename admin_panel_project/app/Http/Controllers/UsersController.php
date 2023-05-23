<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UsersController extends Controller
{
    protected $connection = 'mysql_auth';

    function index(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'nullable'
            ]);

            if (!$request->get('name')) {
                $users = DB::connection($this->connection)->table('users')->get();
            } else {
                $users = DB::connection($this->connection)->table('users')->where('fullName', 'LIKE', '%' . $validatedData['name'] . '%')->get();
            }

            return view('usersPage', ['users' => $users, 'currentUser' => $validatedData['name'] ?? null]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function store()
    {
        try {
            $restaurants = DB::connection('mysql_backend')->table('restaurants')->select('id', 'name')->get();

            return view('createUser', ['restaurants' => $restaurants]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
                'fullName' => 'required|string|max:255',
                'birthDate' => 'nullable|date_format:Y-m-d|before_or_equal:now|after:1900-01-01',
                'gender' => 'nullable|in:male,female',
                'phone' => 'required|string|regex:/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/i',
                'address' => 'nullable|string|min:3',
            ]);

            DB::connection($this->connection)->table('users')->insert([
                'email' => $validatedData['email'],
                'fullName' => $validatedData['fullName'],
                'birthDate' => $validatedData['birthDate'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'] ?? null,
                'password' => password_hash($validatedData['password'], PASSWORD_DEFAULT)
            ]);

            return redirect()->intended(route('index-users'))->with('success', 'Пользователь успешно создан!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function edit($userId)
    {
        try {
            $user = DB::connection($this->connection)->table('users')->where('id', $userId)->first();
            $roles = DB::connection($this->connection)->table('roles')->select('role')->where('userId', $userId)->get();
            $restaurants = DB::connection('mysql_backend')->table('restaurants')->select('id', 'name')->get();
            $userRestaurant = DB::connection($this->connection)->table('restaurant_affiliation')->where('userId', $userId)->first();
            if (!$user) {
                return view('errors', ['errors' => 'Такого пользователя не существует!']);
            }

            return view('editUser', ['user' => $user, 'roles' => $roles, 'restaurants' => $restaurants, 'userRestaurant' => $userRestaurant]);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|string|email|max:255',
                'fullName' => 'required|string|max:255',
                'birthDate' => 'nullable|date_format:Y-m-d|before_or_equal:now|after:1900-01-01',
                'gender' => 'nullable|in:male,female',
                'phone' => 'required|string|regex:/^\+7-\d{3}-\d{3}-\d{2}-\d{2}$/i',
                'address' => 'nullable|string|min:3',
            ]);

            $user = DB::connection($this->connection)->table('users')->where('id', $id);

            $user->update([
                'email' => $validatedData['email'],
                'fullName' => $validatedData['fullName'],
                'birthDate' => $validatedData['birthDate'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'] ?? null,
            ]);

            return redirect()->back()->with('success', 'Пользователь успешно отредактирован');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function destroy($userId)
    {
        try {
            $userQuery = DB::connection($this->connection)->table('users')->where('id', $userId);

            if (!$userQuery->exists()) {
                return view('errors', ['errors' => 'Такого пользователя не существует!']);
            }

            $userQuery->delete();
            return redirect()->intended(route('index-users'))->with('success', 'Пользователь успешно удален!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function ban($userId)
    {
        try {
            $userQuery = DB::connection($this->connection)->table('users')->where('id', $userId);
            if (!$userQuery->exists()) {
                return view('errors', ['errors' => 'Такого пользователя не существует!']);
            }

            $user = $userQuery->first();

            if ($user->isBanned) {
                return view('errors', ['errors' => 'Данный пользователь уже заблокирован!']);
            }

            $userQuery->update([
                'isBanned' => 1
            ]);

            return redirect()->back()->with('success', 'Пользователь успешно заблокирован');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function unban($userId)
    {
        try {
            $userQuery = DB::connection($this->connection)->table('users')->where('id', $userId);
            if (!$userQuery->exists()) {
                return view('errors', ['errors' => 'Такого пользователя не существует!']);
            }

            $user = $userQuery->first();

            if (!$user->isBanned) {
                return view('errors', ['errors' => 'Данный пользователь не находится в блокировке']);
            }

            $userQuery->update([
                'isBanned' => 0
            ]);

            return redirect()->back()->with('success', 'Пользователь успешно разблокирован');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    function setRoles(Request $request, $userId)
    {
        try {
            if (!DB::connection($this->connection)->table('users')->where('id', $userId)->exists()) {
                return view('errors', ['errors' => 'Такого пользователя не существует!']);
            }
            $restaurantId = $request->get('restaurant');
            if (($restaurantId != '0') && !DB::connection('mysql_backend')->table('restaurants')->where('id', $restaurantId)->exists()) {
                return view('errors', ['errors' => 'Такого ресторана не существует не существует!']);
            }

            if ($restaurantId == '0') {
                if (!$request->exists('Cook') && !$request->exists('Courier') && !$request->exists('Manager')) {
                    DB::connection($this->connection)->table('restaurant_affiliation')->where('userId', $userId)->delete();
                }
                else {
                    return redirect()->back()->withErrors(['errors' => 'Поле выбора ресторана имеет пустое значение, хотя роли выбраны!']);
                }
            } else {
                DB::connection($this->connection)->table('restaurant_affiliation')->where('userId', $userId)->delete();
                DB::connection($this->connection)->table('restaurant_affiliation')->where('userId', $userId)->insert([
                    'userId' => $userId,
                    'restaurantId' => $request->get('restaurant'),
                ]);
            }

            $roles = DB::connection($this->connection)->table('roles')->where('userId', $userId)->get();

            $rolesState = [
                'isManager' => false,
                'isCook' => false,
                'isCourier' => false
            ];

            foreach ($roles as $role) {
                if ($role->role == 'Manager') {
                    $rolesState['isManager'] = true;
                }
                if ($role->role == 'Cook') {
                    $rolesState['isCook'] = true;
                }
                if ($role->role == 'Courier') {
                    $rolesState['isCourier'] = true;
                }
            }

            if ($request->exists('Cook')) {
                if (!$rolesState['isCook']) {
                    DB::connection($this->connection)->table('roles')->insert([
                        'userId' => $userId,
                        'role' => 'Cook',
                    ]);
                }
            } else {
                if ($rolesState['isCook']) {
                    DB::connection($this->connection)->table('roles')->where('userId', $userId)->where('role', 'Cook')->delete();
                }
            }

            if ($request->exists('Manager')) {
                if (!$rolesState['isManager']) {
                    DB::connection($this->connection)->table('roles')->insert([
                        'userId' => $userId,
                        'role' => 'Manager',
                    ]);
                }
            } else {
                if ($rolesState['isManager']) {
                    DB::connection($this->connection)->table('roles')->where('userId', $userId)->where('role', 'Manager')->delete();
                }
            }

            if ($request->exists('Courier')) {
                if (!$rolesState['isCourier']) {
                    DB::connection($this->connection)->table('roles')->insert([
                        'userId' => $userId,
                        'role' => 'Courier',
                    ]);
                }
            } else {
                if ($rolesState['isCourier']) {
                    DB::connection($this->connection)->table('roles')->where('userId', $userId)->where('role', 'Courier')->delete();
                }
            }

            return redirect()->back()->with('success', 'Роли успешно назначены');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }
}
