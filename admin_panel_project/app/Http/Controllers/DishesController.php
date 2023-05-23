<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\DishService;
use GuzzleHttp\Client;

class DishesController extends Controller
{
    private $connection = 'mysql_backend';

    public function index(Request $request)
    {
        try {
            $dishes = DishService::getDishes($request);
            $categories = DB::connection($this->connection)->table('categories')->get();
            $restaurants = DB::connection($this->connection)->table('restaurants')->get();

            return view('dishesPage', [
                'dishes' => $dishes['dishes'],
                'pagination' => $dishes['pagination'],
                'restaurants' => $restaurants,
                'restaurantId' => $request->get('restaurantId'),
                'categories' => $categories,
                'checked' => $request->exists('isActive'),
                'sort' => $request->get('sorting'),
            ]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $dishQuery = DB::connection($this->connection)->table('dishes')->where('id', $id);

            if (!$dishQuery->exists()) {
                return view('errors', ['errors' => 'Такого блюда не существует!']);
            }

            $dish = $dishQuery->first();
            $restaurants = DB::connection($this->connection)->table('restaurants')->get();
            $menus = DB::connection($this->connection)->table('menus')->where('restaurant_id', $dish->restaurant_id)->get();
            $currentMenus = DB::connection($this->connection)->table('dish_menu')->where('dish_menu.dish_id', $id)
                ->join('menus', 'dish_menu.menu_id', '=', 'menus.id')->get();
            $categories = DB::connection($this->connection)->table('categories')->get();
            $restaurantName = DB::connection($this->connection)->table('restaurants')->where('id', $dish->restaurant_id)->first();

            return view('editDish', [
                'dish' => $dish,
                'restaurants' => $restaurants,
                'menus' => $menus,
                'currentMenus' => $currentMenus,
                'categories' => $categories,
                'restaurantName' => $restaurantName
            ]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'isVegetarian' => 'nullable|in:true,false,0,1',
                'photo' => 'nullable',
                'category' => 'nullable',
                'isActive' => 'nullable|in:true,false,0,1',
            ]);

            $dishQuery = DB::connection($this->connection)->table('dishes')->where('id', $id);
            if (!$dishQuery->exists()) {
                return view('errors', ['errors' => 'Такого блюда не существует!']);
            }

            $dish = $dishQuery->first();

            if ($request->hasFile('photo')) {
                $client = new Client();

                $response = $client->request('POST', 'http://127.0.0.1:8000/api/dish/savePhoto', [
                    'multipart' => [
                        [
                            'name' => 'photo',
                            'contents' => $request->file('photo')->openFile(),
                            'filename' => $request->file('photo')->getClientOriginalName(),
                        ],
                        [
                            'name' => 'token',
                            'contents' => password_hash(env('JWT_SECRET'), PASSWORD_DEFAULT),
                        ]
                    ],
                ]);

                $path = json_decode($response->getBody(), true)['path'];
            }

            DB::connection($this->connection)->table('dishes')->where('id', $id)->update([
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'] ?? $dish->description,
                'isVegetarian' => $validatedData['isVegetarian'] ?? $dish->isVegetarian,
                'category' => $validatedData['category'] ?? $dish->category,
                'isActive' => $validatedData['isActive'] ?? $dish->isActive,
                'photo' => $path ?? $dish->photo
            ]);

            return redirect()->back()->with('success', 'Блюдо успешно отредактировано!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $dishQuery = DB::connection($this->connection)->table('dishes')->where('id', $id);

            if (!$dishQuery->exists()) {
                return view('errors', ['errors' => 'Такого блюда не существует!']);
            }
            $dishQuery->delete();

            return redirect(route('index-dishes'))->with('success', 'Блюдо успешно удалено!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'isVegetarian' => 'nullable|in:true,false,0,1',
                'photo' => 'nullable',
                'category' => 'nullable',
                'isActive' => 'nullable|in:true,false,0,1',
                'restaurantId' => 'required'
            ]);

            if (!DB::connection($this->connection)->table('restaurants')->where('id', $validatedData['restaurantId'])->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Такого ресторана не существует']);
            }

            if ($request->hasFile('photo')) {
                $client = new Client();

                $response = $client->request('POST', 'http://127.0.0.1:8000/api/dish/savePhoto', [
                    'multipart' => [
                        [
                            'name' => 'photo',
                            'contents' => $request->file('photo')->openFile(),
                            'filename' => $request->file('photo')->getClientOriginalName(),
                        ],
                        [
                            'name' => 'token',
                            'contents' => password_hash(env('JWT_SECRET'), PASSWORD_DEFAULT),
                        ]
                    ],
                ]);

                $path = json_decode($response->getBody(), true)['path'];
            }

            $dish = DB::connection($this->connection)->table('dishes')->insert([
                'name' => $validatedData['name'],
                'price' => $validatedData['price'],
                'description' => $validatedData['description'] ?? null,
                'isVegetarian' => $validatedData['isVegetarian'] ?? false,
                'category' => $validatedData['category'] ?? null,
                'isActive' => $validatedData['isActive'] ?? false,
                'restaurant_id' => $validatedData['restaurantId'],
                'photo' => $path ?? null
            ]);

            return redirect()->intended(route('index-dishes'))->with('success', 'Блюдо успешно создано!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $restaurants = DB::connection($this->connection)->table('restaurants')->get();
            $categories = DB::connection($this->connection)->table('categories')->get();

            return view('createDish', ['restaurants' => $restaurants, 'categories' => $categories]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function deleteFromMenu($menuId, $dishId)
    {
        try {
            $menu = DB::connection($this->connection)->table('menus')->where('id', $menuId);
            if (!$menu->exists()) {
                return view('errors', ['errors' => 'Такого меню не существует!']);
            }

            $dish = DB::connection($this->connection)->table('dishes')->where('id', $dishId);
            if (!$dish->exists()) {
                return view('errors', ['errors' => 'Такого блюда не существует!']);
            }

            DB::connection($this->connection)->table('dish_menu')->where('menu_id', $menuId)->where('dish_id', $dishId)
                ->delete();

            return redirect()->back()->with('success', 'Блюдо успешно удалено из меню!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function addToMenu(Request $request, $dishId)
    {
        try {
            $validatedData = $request->validate([
                'menuId' => 'required'
            ]);
            $menuId = $validatedData['menuId'];

            $menu = DB::connection($this->connection)->table('menus')->where('id', $menuId);
            if (!$menu->exists()) {
                return view('errors', ['errors' => 'Такого меню не существует!']);
            }
            $dish = DB::connection($this->connection)->table('dishes')->where('id', $dishId);
            if (!$dish->exists()) {
                return view('errors', ['errors' => 'Такого блюда не существует!']);
            }

            $query = DB::connection($this->connection)->table('dish_menu')->where('menu_id', $menuId)->where('dish_id', $dishId);

            if ($query->exists()) {
                return redirect()->back()->with('success', 'Блюдо и так находится в этом меню!');
            }

            DB::connection($this->connection)->table('dish_menu')->insert([
                'menu_id' => $menuId,
                'dish_id' => $dishId
            ]);

            return redirect()->back()->with('success', 'Блюдо успешно добавлено в меню!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ", array_map(function ($a) {
                return implode("~", $a);
            }, $e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }
}
