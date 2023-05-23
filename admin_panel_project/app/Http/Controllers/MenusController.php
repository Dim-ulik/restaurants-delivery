<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class MenusController extends Controller
{
    protected $connection = 'mysql_backend';

    public function index(Request $request)
    {
        try {
            $menuQuery = DB::connection($this->connection)->table('menus')
            ->join('restaurants', 'menus.restaurant_id', '=', 'restaurants.id')->
            select('menus.id', 'menus.name', 'restaurants.name as restaurantName');
            $restaurants = DB::connection($this->connection)->table('restaurants')->get();
            if (!$request->get('restaurantId')) {
                $menus = $menuQuery->get();
                return view('menusPage', ['menus' => $menus, 'restaurantId' => '', 'restaurants' => $restaurants]);
            } else {
                $restaurantId = $request->get('restaurantId');
                if (!DB::connection($this->connection)->table('restaurants')->where('id', $restaurantId)->exists()) {
                    return view('errors', ['errors' => 'Такого ресторана не существует!']);
                }
                $menus = $menuQuery->where('restaurant_id', $request->get('restaurantId'))->get();

                return view('menusPage', ['menus' => $menus, 'restaurantId' => $restaurantId, 'restaurants' => $restaurants]);
            }
        }
        catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $menu = DB::connection($this->connection)->table('menus')->select('id', 'name', 'restaurant_id')->where('id', $id)->first();

            if (!$menu) {
                return view('errors', ['errors' => 'Такого меню не существует!']);
            }

            $restaurants = DB::connection($this->connection)->table('restaurants')->get();

            return view('editMenu', ['menu' => $menu, 'restaurants' => $restaurants]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'restaurantId' => 'required'
            ]);

            if ($validatedData['restaurantId'] == 0) {
                return redirect()->back()->withErrors(['errors' => 'Ресторан не был выбран!']);
            }
            if (!DB::connection($this->connection)->table('restaurants')->where('id', $validatedData['restaurantId'])->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Такого ресторана не существует']);
            }

            $menuQuery = DB::connection($this->connection)->table('menus')->where('id', $id);

            if (!$menuQuery->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Такого меню не существует']);
            }

            $menuQuery->update([
                'name' => $validatedData['name'],
                'restaurant_id' => $validatedData['restaurantId']
            ]);

            return redirect()->intended(route('index-menus'))->with('success', 'Меню успешно отредактировано!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $menuQuery = DB::connection($this->connection)->table('menus')->where('id', $id);
            $menu = $menuQuery->exists();
            if (!$menu) {
                return view('errors', ['errors' => 'Такого меню не существует!']);
            }
            $menuQuery->delete();
            return redirect()->intended(route('index-menus'))->with('success', 'Меню успешно удалено!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'restaurantId' => 'required'
            ]);

            if ($validatedData['restaurantId'] == 0) {
                return redirect()->back()->withErrors(['errors' => 'Ресторан не был выбран!']);
            }
            if (!DB::connection($this->connection)->table('restaurants')->where('id', $validatedData['restaurantId'])->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Такого ресторана не существует']);
            }

            DB::connection($this->connection)->table('menus')->insert([
                'name' => $validatedData['name'],
                'restaurant_id' => $validatedData['restaurantId']
            ]);

            return redirect()->intended(route('index-menus'))->with('success', 'Меню успешно создано!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $restaurants = DB::connection($this->connection)->table('restaurants')->get();
            return view('createMenu', ['restaurants' => $restaurants]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }
}
