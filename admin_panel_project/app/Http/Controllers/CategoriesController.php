<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoriesController extends Controller
{
    protected $connection = 'mysql_backend';

    public function index(Request $request)
    {
        try {
            $categories = DB::connection($this->connection)->table('categories')->get();
            return view('categoriesPage', ['categories' => $categories]);
        }
        catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $category = DB::connection($this->connection)->table('categories')->where('id', $id)->first();

            if (!$category) {
                return view('errors', ['errors' => 'Такой категории не существует!']);
            }

            return view('editCategory', ['category' => $category]);
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

            $categoryQuery = DB::connection($this->connection)->table('categories')->where('id', $id);

            if (!$categoryQuery->exists()) {
                return view('errors', ['errors' => 'Такой категории не существует!']);
            }

            $category = $categoryQuery->first();

            $categoryQuery->update([
                'category' => $validatedData['name']
            ]);

            DB::connection($this->connection)->table('dishes')->where('category', $category->category)->update([
                'category' => $validatedData['name']
            ]);

            return redirect()->intended(route('index-categories'))->with('success', 'Категория успешно отредактирована!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $categoryQuery = DB::connection($this->connection)->table('categories')->where('id', $id);

            $category = $categoryQuery->exists();

            if (!$category) {
                return view('errors', ['errors' => 'Такой категории не существует!']);
            }

            $categoryName = $categoryQuery->first()->category;
            $categoryQuery->delete();

            DB::connection($this->connection)->table('dishes')->where('category', $categoryName)->update([
                'category' => null,
            ]);

            return redirect()->intended(route('index-categories'))->with('success', 'Категория успешно удалена!');
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
            ]);

            DB::connection($this->connection)->table('categories')->insert([
                'category' => $validatedData['name']
            ]);

            return redirect()->intended(route('index-categories'))->with('success', 'Категория успешно создана!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['errors' => implode("  //  ",array_map(function($a) {return implode("~",$a);},$e->errors()))]);
        } catch (Throwable $e) {
            return redirect()->back()->withErrors(['errors' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            return view('createCategory');
        } catch (Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }
}
