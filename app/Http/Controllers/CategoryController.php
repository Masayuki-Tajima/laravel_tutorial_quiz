<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Dotenv\Util\Str;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * 管理画面トップページ兼カテゴリー一覧表示
     */
    public function top()
    {
        //カテゴリー一覧を取得
        $categories = Category::get();
        // dd($categories);

        return view('admin.top', [
            'categories' => $categories,
        ]);
    }

    /**
     * カテゴリー新規登録画面表示
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * カテゴリー新規登録処理
     */
    public function store(StoreCategoryRequest $request)
    {
        // dd('カテゴリー新規登録処理のルートです', $request);
        // dd($request->name, $request->description);
        $category = new Category();
        $category->name        = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('admin.top');
    }

    /**
     *カテゴリー詳細画面表示
     */
    public function show(Request $request, int $categoryId)
    {
        // dd($categoryId, $request);
        $category = Category::findOrFail($categoryId);
        dd($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
