<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::guest()) {
            return redirect()->route('login');
        }

        $message = setting('shop.home', trans('shop::messages.welcome'));

        /** @var Collection<Category> $categories */
        $categories = $this->getCategories();

        return view('shop::categories.show', [
            'category' => $categories->first(),
            'categories' => $categories,
            'goal' => $this->getMonthGoal(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $categories = $this->getCategories($category);

        $category->load('packages.discounts');

        foreach ($category->packages as $package) {
            $package->setRelation('category', $category);
        }

        return view('shop::categories.show', [
            'category' => $category,
            'categories' => $categories,
            'goal' => $this->getMonthGoal(),
        ]);
    }

    protected function getMonthGoal()
    {
        if (! setting('shop.month_goal')) {
            return false;
        }

        $total = Payment::scopes(['completed', 'withRealMoney'])
            ->where('created_at', '>', now()->startOfMonth())
            ->sum('price');

        return round(($total / setting('shop.month_goal')) * 100, 2);
    }

    protected function getCategories(Category $current = null)
    {
        return Category::scopes(['parents', 'enabled'])
            ->with('categories')
            ->withCount('packages')
            ->get();
    }
}
