<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RestaurantsController extends Controller
{
    protected $connection = 'mysql_backend';

    public function index()
    {
        try {
            $restaurants = DB::connection($this->connection)->table('restaurants')->select('id', 'name')->get();
            return view('restaurantsPage', ['restaurants' => $restaurants]);
        }
        catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $restaurant = DB::connection($this->connection)->table('restaurants')->select('id', 'name')->where('id', $id)->first();

            if (!$restaurant) {
                return view('errors', ['errors' => 'Такого ресторана не существует!']);
            }

            return view('editRestaurant', ['restaurant' => $restaurant]);
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);
            if (DB::connection($this->connection)->table('restaurants')->where('name', $validatedData['name'])->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Ресторан с таким названием уже существует!']);
            }

            $restaurantQuery = DB::connection($this->connection)->table('restaurants')->where('id', $id);
            $restaurant = $restaurantQuery->first();

            if (!$restaurant) {
                return view('errors', ['errors' => 'Такого ресторана не существует!']);
            }

            $restaurantQuery->update([
                'name' => $validatedData['name']
            ]);

            return redirect()->back()->with('success', 'Ресторан успешно отредактирован');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $restaurantQuery = DB::connection($this->connection)->table('restaurants')->where('id', $id);
            $restaurant = $restaurantQuery->exists();
            if (!$restaurant) {
                return view('errors', ['errors' => 'Такого ресторана не существует!']);
            }
            $restaurantQuery->delete();
            return redirect()->intended(route('index-restaurants'))->with('success', 'Ресторан успешно удален!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);

            if (DB::connection($this->connection)->table('restaurants')->where('name', $validatedData['name'])->exists()) {
                return redirect()->back()->withErrors(['errors' => 'Ресторан с таким названием уже существует!']);
            }

            DB::connection($this->connection)->table('restaurants')->insert([
                'name' => $validatedData['name']
            ]);

            return redirect()->intended(route('index-restaurants'))->with('success', 'Ресторан успешно создан!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function create()
    {
        try {
            return view('createRestaurant');
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }
}
