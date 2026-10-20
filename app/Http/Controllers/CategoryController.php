<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::query()->create($data);

        return redirect()
            ->route('settings.index')
            ->with('success', 'Kategori "'.$data['name'].'" berhasil ditambahkan.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;

        $category->delete();

        return redirect()
            ->route('settings.index')
            ->with('success', 'Kategori "'.$name.'" berhasil dihapus.');
    }
}
