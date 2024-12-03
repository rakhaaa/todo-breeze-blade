<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Http\Requests\TodoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;

class TodoController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware()
    {
        return ['auth'];
    }
    
    public function index()
    {
        $todos = Auth::user()->role === 'admin' ? ToDo::latest()->get() : ToDo::where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('todos.index', compact('todos'));
    }

    public function create()
    {
        return view('todos.create');
    }

    public function store(TodoRequest $request)
    {
        ToDo::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('todos.index')->with('status', 'Todo create successfully');
    }

    public function show(Todo $todo)
    {
        return view('todos.show', compact($todo));
    }

    public function edit(Todo $todo)
    {
        return view('todos.edit', compact($todo));
    }

    public function update(TodoRequest $request, Todo $todo)
    {
        $this->authorize('update', $todo);

        $todo->update($request->validated());

        return redirect()->route('todos.index')->with('status', 'Todo update successfully');
    }

    public function destroy(Todo $todo)
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return redirect()->route('todos.index')->with('status', 'Todo delete successfully');
    }
}
