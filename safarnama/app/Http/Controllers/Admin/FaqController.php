<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.faqs.index', ['faqs' => Faq::orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['faq' => new Faq(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'Question added.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('status', 'Question saved.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('status', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:5000',
            'sort_order' => 'nullable|integer',
        ]) + ['is_published' => $request->boolean('is_published')];
    }
}
