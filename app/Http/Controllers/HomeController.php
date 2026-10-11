<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\PageBuilder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PageBuilder $builder): View
    {
        return $builder->view(Page::system('home'));
    }
}
