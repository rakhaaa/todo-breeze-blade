### Step-by-Step Guide

#### 1. Set Up Laravel Project with Breeze

1. **Create a new Laravel project:**
   ```sh
   composer create-project laravel/laravel todo-app
   cd todo-app
   ```

2. **Install Laravel Breeze:**
   ```sh
   composer require laravel/breeze --dev
   php artisan breeze:install
   npm install && npm run dev
   php artisan migrate
   ```

#### 2. Create Database Migrations and Models

1. **Create User and ToDo migrations and models:**
   ```sh
   php artisan make:model ToDo -m
   ```

2. **Update the User migration:**
   **database/migrations/XXXX_XX_XX_create_users_table.php:**
   ```php
   public function up()
   {
       Schema::create('users', function (Blueprint $table) {
           $table->ulid('id')->primary();
           $table->string('name');
           $table->string('email')->unique();
           $table->timestamp('email_verified_at')->nullable();
           $table->string('password');
           $table->string('role')->default('user'); // Add role column
           $table->rememberToken();
           $table->timestamps();
       });
   }
   ```

3. **Create the ToDo migration:**
   **database/migrations/XXXX_XX_XX_create_todos_table.php:**
   ```php
   public function up()
   {
       Schema::create('todos', function (Blueprint $table) {
           $table->ulid('id')->primary();
           $table->string('title');
           $table->text('description')->nullable();
           $table->foreignId('user_id')->constrained()->onDelete('cascade');
           $table->timestamps();
       });
   }
   ```

4. **Run migrations:**
   ```sh
   php artisan migrate
   ```

5. **Edit Setup Config and Relationship in User Model**
    ```php
    use Illuminate\Database\Eloquent\Concerns\HasUlids;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    use HasUlids;

    protected $keyType = "string";
    public $incrementing = false;

    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }
    ```

6. **Edit Setup Config and Relationship in Todo Model**
    ```php
    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Concerns\HasUlids;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Todo extends Model
    {
        use HasFactory, HasUlids;

        protected $keyType = "string";
        public $incrementing = false;

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
    }

    ```
7. **Setup Configuration Factory Todo:**
    ```php
    <?php

    namespace Database\Factories;

    use App\Models\User;
    use Illuminate\Database\Eloquent\Factories\Factory;

    class TodoFactory extends Factory
    {
        public function definition(): array
        {
            return [
                'title' => fake()->sentence(),
                'description' => fake()->text(),
                'user_id' => User::factory()
            ];
        }
    }
    ```
8. **Setup TodoSeeder:**
    ```php
    <?php

    namespace Database\Seeders;

    use App\Models\Todo;
    use App\Models\User;
    use Illuminate\Database\Console\Seeds\WithoutModelEvents;
    use Illuminate\Database\Seeder;

    class TodoSeeder extends Seeder
    {
        public function run(): void
        {
            Todo::factory()
                ->count(5)
                ->for(User::factory()->state([
                    'name' => 'Admin'
                ]))
                ->create();
                
            User::factory(9)
                ->hasTodos(5)
                ->create();
        }
    }

    ```
9. **Setup Configuration DatabaseSeeder:**
    ```php
    <?php

    namespace Database\Seeders;

    use App\Models\User;
    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        public function run(): void
        {
            $this->call([UserSeeder::class, TodoSeeder::class]);
        }
    }

    ```
10. **Run Seeder:**
    ```sh
    php artisan make:seed
    ```

#### 3. Create Form Requests

1. **Create UserRequest:**
   ```sh
   php artisan make:request UserRequest
   ```

2.  **Edit UserRequest:**
    **app/Http/Requests/UserRequest.php:**
   ```php
        <?php

        namespace App\Http\Requests;

        use Illuminate\Foundation\Http\FormRequest;

        class UserRequest extends FormRequest
        {
            public function authorize()
            {
                return true;
            }

            public function rules()
            {
                return [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users,email,' . $this->user,
                    'password' => 'nullable|string|min:8|confirmed',
                    'role' => 'required|string|in:admin,user',
                ];
            }
        }
    ```
    **app/Http/Requests/UpdateUserRequest.php:**
     ```php
        <?php

        namespace App\Http\Requests;

        use Illuminate\Foundation\Http\FormRequest;

        class UpdateUserRequest extends FormRequest
        {
            public function authorize()
            {
                return true;
            }

            public function rules()
            {
                return [
                    'name' => 'required|string|max:255',
                    'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id),],
                    'password' => 'nullable|string|min:8|confirmed',
                    'role' => 'required|string|in:admin,user',
                ];
            }
        }
    ```

3. **Create ToDoRequest**
   ```sh
   php artisan make:request ToDoRequest
   ```

   **app/Http/Requests/ToDoRequest.php:**
   ```php
   <?php

   namespace App\Http\Requests;

   use Illuminate\Foundation\Http\FormRequest;

   class ToDoRequest extends FormRequest
   {
       public function authorize()
       {
           return true;
       }

       public function rules()
       {
           return [
               'title' => 'required|string|max:255',
               'description' => 'nullable|string',
           ];
       }
   }
   ```

#### 4. Create Policies

1. **Create ToDoPolicy:**
   ```sh
   php artisan make:policy ToDoPolicy --model=ToDo
   ```

   **app/Policies/ToDoPolicy.php:**
   ```php
   <?php

   namespace App\Policies;

   use App\Models\ToDo;
   use App\Models\User;
   use Illuminate\Auth\Access\HandlesAuthorization;

   class ToDoPolicy
   {
       use HandlesAuthorization;

       public function update(User $user, ToDo $todo)
       {
           return $user->id === $todo->user_id || $user->role === 'admin';
       }

       public function delete(User $user, ToDo $todo)
       {
           return $user->id === $todo->user_id || $user->role === 'admin';
       }
   }
   ```

#### 5. Create Controllers

1. **Create UserController:**
   ```sh
   php artisan make:controller UserController
   ```

   **app/Http/Controllers/UserController.php:**
   ```php
   <?php

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Http\Requests\UserRequest;
    use Illuminate\Routing\Controllers\HasMiddleware;
    use Illuminate\Routing\Controllers\Middleware;

    class UserController extends Controller implements HasMiddleware
    {
        public static function middleware()
        {
            return [
                'auth',
                new Middleware('role')
            ];
        }

        public function index()
        {
            $users = User::latest()->get();
            return view('users.index', compact('users'));
        }

        public function create()
        {
            return view('users.create');
        }

        public function store(UserRequest $request)
        {
            User::create($request->validated());

            return redirect()->route('users.index')->with('status', 'User created successfully');
        }

        public function show(User $user)
        {
            return view('users.show', compact('user'));
        }

        public function edit(User $user)
        {
            return view('users.edit', compact('user'));
        }

        public function update(UserRequest $request, User $user)
        {
            $user->update($request->validated());

            return redirect()->route('users.index')->with('status', 'User update successfully');
        }

        public function destroy(User $user)
        {
            $user->delete();

            return redirect()->route('users.index')->with('status', 'User delete successfully');
        }
    }

   ```

2. **Create ToDoController:**
   ```sh
   php artisan make:controller ToDoController
   ```

   **app/Http/Controllers/ToDoController.php:**
   ```php
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
            $todos = Auth::user()->role === 'admin' ? ToDo::latest()->get() : ToDo::where('user_id', Auth::id())->get();

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

   ```

#### 6. Create Middleware
1. **Create Role Middleware:**
    ```sh
    php artisan make:middleware Role
    ```

2. **Setup Configuratuin Role Middleware**
   **app/Http/Middleware/Role.php:**
   ```php
   <?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Symfony\Component\HttpFoundation\Response;

    class Role
    {
        
        public function handle(Request $request, Closure $next): Response
        {
            if (Auth::check() && Auth::user()->role === 'admin') {
                return $next($request);
            }
            return redirect('/');
        }
    }

   ```

3. **Register Middleware:**
   **app/Bootstrap/app.php:**
   ```php
    <?php

    use App\Http\Middleware\Role;
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;

    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware) {
            $middleware->alias([
                'role' => Role::class
            ]);
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })->create();

   ```
### 7. Define Routes

1. **Routes**
    **routes/web.php:**
    ```php
        <?php

        use App\Http\Controllers\UserController;
        use App\Http\Controllers\ToDoController;
        use Illuminate\Support\Facades\Route;


        // User Management Routes
        Route::resource('users', UserController::class);

        // ToDo Management Routes
        Route::resource('todos', UserController::class);
    ```
### 8. Make Testing

1. **Create UserTest**
    **cli:**
    ```sh
        php artisan make:test UserTest
    ```

2. **Setup UserTest**
    **tests/Feature/UserTest.php:**
    ```php
    <?php

    use App\Models\User;
    use Illuminate\Foundation\Testing\RefreshDatabase;

    uses(RefreshDatabase::class);

    test('admin can create a user', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    });

    test('admin can edit a user', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    });

    test('admin can delete a user', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $this->actingAs($admin);

        $response = $this->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    test('non-admin cannot access user management', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/users');
        $response->assertRedirect('/');
    });

    ```

3. **Run UserTest**
    ```sh
    php artisan make:test --filter="UserTest"
    ```

4. **Create UserTest**
    ```sh
    php artisan make:test TodoTest
    ```

5. **Setup TodoTest**
    ```php
    <?php

    use App\Models\User;
    use App\Models\ToDo;
    use Illuminate\Foundation\Testing\RefreshDatabase;

    uses(RefreshDatabase::class);

    test('user can create a todo', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/todos', [
            'title' => 'Test ToDo',
            'description' => 'Test Description',
            'user_id' => $user->id
        ]);

        $response->assertRedirect('/todos');
        $this->assertDatabaseHas('todos', ['title' => 'Test ToDo']);
    });

    test('user can edit their own todo', function () {
        $user = User::factory()->create();
        $todo = ToDo::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->put("/todos/{$todo->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $response->assertRedirect('/todos');
        $this->assertDatabaseHas('todos', ['title' => 'Updated Title']);
    });

    test('user can delete their own todo', function () {
        $user = User::factory()->create();
        $todo = ToDo::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->delete("/todos/{$todo->id}");

        $response->assertRedirect('/todos');
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    });

    test('user cannot edit others todo', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = ToDo::factory()->create(['user_id' => $otherUser->id]);
        $this->actingAs($user);

        $response = $this->put("/todos/{$todo->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $response->assertForbidden();
    });

    test('admin can edit any todo', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $todo = ToDo::factory()->create(['user_id' => $user->id]);
        $this->actingAs($admin);

        $response = $this->put("/todos/{$todo->id}", [
            'title' => 'Admin Updated Title',
            'description' => 'Admin Updated Description',
        ]);

        $response->assertRedirect('/todos');
        $this->assertDatabaseHas('todos', ['title' => 'Admin Updated Title']);
    });

    ```

6. **Run TodoTest**
    ```sh
    php artisan make:test --filter="UserTest"
    ```

### 9. Create Blade Views Using Components

1. **Create Layout Page:**
    **resources/views/layouts/app.blade.php:**
    ```html
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'ToDo App')</title>
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="//unpkg.com/alpinejs" defer></script>
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex flex-col">
            <header class="bg-blue-600 text-white p-4">
                <div class="container mx-auto flex justify-between items-center">
                    <h1 class="text-2xl font-bold">ToDo App</h1>
                    <nav>
                        <a href="{{ route('todos.index') }}" class="text-white px-4">Todos</a>
                        <a href="{{ route('users.index') }}" class="text-white px-4">Users</a>
                    </nav>
                </div>
            </header>
            <main class="flex-grow container mx-auto p-4">
                @if (session('status'))
                    <x-notification>
                        {{ session('status') }}
                    </x-notification>
                @endif
                @yield('content')
            </main>
            <footer class="bg-gray-800 text-white p-4 mt-4">
                <div class="container mx-auto">
                    <p>&copy; {{ date('Y') }} ToDo App</p>
                </div>
            </footer>
        </div>
    </body>
    </html>
    ```

2. **Create Components:**
    **Notification Component:**
    **resources/views/components/notification.blade.php:**
    ```html
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-500 text-white p-4 mb-4 rounded">
        {{ $slot }}
    </div>
    ```

2. **Modal Component**

   **resources/views/components/form-modal.blade.php:**
   ```html
   <div x-show="open" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
       <div @click.away="open = false" class="bg-white p-6 rounded-lg shadow-lg w-1/3">
           <div class="flex justify-between items-center mb-4">
               <h3 class="text-xl font-bold">{{ $title }}</h3>
               <button @click="open = false" class="text-gray-500">&times;</button>
           </div>
           {{ $slot }}
       </div>
   </div>
   ```

3. **Table Component**

   **resources/views/components/table.blade.php:**
   ```html
   <table class="min-w-full bg-white">
       <thead class="bg-gray-800 text-white">
           <tr>
               @foreach ($headers as $header)
                   <th class="py-2 px-4">{{ $header }}</th>
               @endforeach
           </tr>
       </thead>
       <tbody>
           {{ $slot }}
       </tbody>
   </table>
   ```

4. **Form Input Component**

   **resources/views/components/form-input.blade.php:**
   ```html
   @props(['id', 'label', 'name', 'type' => 'text', 'value' => '', 'required' => false])

   <div class="mb-4">
       <label for="{{ $id }}" class="block text-gray-700">{{ $label }}</label>
       <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @if($required) required @endif class="w-full p-2 border border-gray-300 rounded mt-2">
   </div>
   ```

5. **Form Select Component**

   **resources/views/components/form-select.blade.php:**
   ```html
   @props(['id', 'label', 'name', 'options', 'value' => '', 'required' => false])

   <div class="mb-4">
       <label for="{{ $id }}" class="block text-gray-700">{{ $label }}</label>
       <select name="{{ $name }}" id="{{ $id }}" @if($required) required @endif class="w-full p-2 border border-gray-300 rounded mt-2">
           @foreach ($options as $option)
               <option value="{{ $option }}" @if ($option == $value) selected @endif>{{ ucfirst($option) }}</option>
           @endforeach
       </select>
   </div>
   ```

**User Management Blade Views**
1. **Make User Blade:**
    **resources/views/users/index.blade.php:**
    ```html
    <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User') }}
        </h2>
    </x-slot>
    <div class="w-full flex justify-center items-center py-4">
        @if (session('status'))
            <x-notification>
                {{ session('status') }}
            </x-notification>
        @endif
        @if ($errors->any())
            <x-notification color="red">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-notification>

        @endif
    </div>
    <div class="pt-4 pb-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="{
                    openCreateModal: false,
                    openEditModal: false,
                    editUrl: '',
                    userName: '',
                    userEmail: '',
                    userPassword: '',
                    userPasswordConfirmation: '',
                    userRole: null,
                    editModal(editUrl, userName, userEmail, userPassword, userPasswordConfirmation, userRole) {
                        this.openEditModal = !this.openEditModal;
                        this.editUrl = editUrl;
                        this.userName = userName;
                        this.userEmail = userEmail;
                        this.userPassword = userPassword;
                        this.userPasswordConfirmation = userPasswordConfirmation;
                        this.userRole = userRole == 'admin' ? 1 : 2;
                
                    }
                }">
                    <div class="flex justify-between mb-4">
                        <h2 class="text-2xl font-bold">User Management</h2>

                        <button x-on:click="openCreateModal = true"
                            class="bg-blue-500 text-white py-2 px-4 rounded">Create
                            New User</button>
                    </div>
                    <x-table :headers="['Name', 'Email', 'Role', 'Actions']">
                        @foreach ($users as $user)
                            <tr>
                                <td class="">{{ $user->name }}</td>
                                <td class="">{{ $user->email }}</td>
                                <td class="">{{ $user->role }}</td>
                                <td class=" flex space-x-2">
                                    <button
                                        x-on:click="editModal('{{ route('users.update', $user) }}', '{{ $user->name }}', '{{ $user->email }}', '{{ $user->password }}','{{ $user->password_confirmation }}', '{{ $user->role }}')"
                                        class="bg-yellow-500 text-white py-1 px-2 rounded">Edit</button>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 text-white py-1 px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                    <!-- Create User Modal -->
                    <div x-show="openCreateModal"
                        class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                        <div class="theme p-6 rounded-lg shadow-lg w-1/3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Create New User</h3>
                                <button x-on:click="openCreateModal = false" class="text-gray-500">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('users.store') }}">
                                @csrf
                                <x-form-input id="name" label="Name" name="name" required />
                                <x-form-input id="email" label="Email" name="email" type="email" required />
                                <x-form-input id="password" label="Password" name="password" type="password"
                                    required />
                                <x-form-input id="password_confirmation" label="Pasword Confirmation"
                                    name="password_confirmation" type="password" required />
                                <x-form-select id="role" label="Role" name="role" :options="[[2, 'user'], [1, 'admin']]"
                                    value="userRole" x-model="userRole" required />
                                <div class="flex justify-end">
                                    <button type="button" x-on:click="openCreateModal = false"
                                        class="bg-gray-500 text-white py-2 px-4 rounded mr-2">Cancel</button>
                                    <button type="submit"
                                        class="bg-blue-500 text-white py-2 px-4 rounded">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Edit User Modal -->
                    <div x-show="openEditModal"
                        class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                        <div class="theme p-6 rounded-lg shadow-lg w-1/3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Edit User</h3>
                                <button x-on:click="openEditModal = false" class="text-gray-500">&times;</button>
                            </div>
                            <form :action="editUrl" method="POST">
                                @csrf
                                @method('PUT')
                                <x-form-input id="edit_name" label="Name" name="name" value="userName"
                                    x-model="userName" required />
                                <x-form-input id="edit_email" label="Email" name="email" type="email"
                                    value="userEmail" x-model="userEmail" required />
                                <x-form-input id="edit_password" label="Password" name="password" type="password"
                                    value="userPassword" x-model="password" />
                                <x-form-input id="edit_password_confirmation" label="Password Confiramtion"
                                    name="password_confirmation" type="password" value="userPassword"
                                    x-model="password" />
                                <x-form-select id="edit_role" label="Role" name="role" :options="[[2, 'user'], [1, 'admin']]"
                                    value="userRole" x-model="userRole" required />
                                <div class="flex justify-end">
                                    <button type="button" x-on:click="openEditModal = false"
                                        class="bg-gray-500 text-white py-2 px-4 rounded mr-2">Cancel</button>
                                    <button type="submit"
                                        class="bg-blue-500 text-white py-2 px-4 rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </x-app-layout>

    ```

**ToDo Management Blade Views**

1. **ToDo Index View**

   **resources/views/todos/index.blade.php:**
   ```html
   <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Todo') }}
        </h2>
    </x-slot>
    <div class="w-full flex justify-center items-center py-4">
        @if (session('status'))
            <x-notification>
                {{ session('status') }}
            </x-notification>
        @endif
        @if ($errors->any())
            <x-notification color="red">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-notification>

        @endif
    </div>
    <div class="pt-4 pb-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="{
                    openCreateModal: false,
                    openEditModal: false,
                    editUrl: '',
                    todoTitle: '',
                    todoDescription: '',
                    editModal(editUrl, todoTitle, todoDescription) {
                        this.openEditModal = !this.openEditModal;
                        this.editUrl = editUrl;
                        this.todoTitle = todoTitle;
                        this.todoDescription = todoDescription;
                    }
                }">
                    <div class="flex justify-between mb-4">
                        <h2 class="text-2xl font-bold">User Management</h2>

                        <button x-on:click="openCreateModal = true"
                            class="bg-blue-500 text-white py-2 px-4 rounded">Create
                            New User</button>
                    </div>
                    <x-table :headers="['User', 'Title', 'Description', 'Actions']">
                        @foreach ($todos as $todo)
                            <tr>
                                <td class="">{{ $todo->user->name }}</td>
                                <td class="">{{ $todo->title }}</td>
                                <td class="">{{ $todo->description }}</td>
                                <td class=" flex space-x-2">
                                    <button
                                        x-on:click="editModal('{{ route('todos.update', $todo) }}', 
                                        '{{ $todo->title }}', '{{ $todo->description }}')"
                                        class="bg-yellow-500 text-white py-1 px-2 rounded">Edit</button>
                                    <form action="{{ route('todos.destroy', $todo) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 text-white py-1 px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                    <!-- Create Todo Modal -->
                    <div x-show="openCreateModal"
                        class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                        <div class="theme p-6 rounded-lg shadow-lg w-1/3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Create New Todo</h3>
                                <button x-on:click="openCreateModal = false" class="text-gray-500">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('todos.store') }}">
                                @csrf
                                <x-form-input id="title" label="Title" name="title" required />
                                <x-form-input id="email" label="Description" name="description" type="text" required />
                                <div class="flex justify-end">
                                    <button type="button" x-on:click="openCreateModal = false"
                                        class="bg-gray-500 text-white py-2 px-4 rounded mr-2">Cancel</button>
                                    <button type="submit"
                                        class="bg-blue-500 text-white py-2 px-4 rounded">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Edit User Modal -->
                    <div x-show="openEditModal"
                        class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                        <div class="theme p-6 rounded-lg shadow-lg w-1/3">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Edit Todo</h3>
                                <button x-on:click="openEditModal = false" class="text-gray-500">&times;</button>
                            </div>
                            <form :action="editUrl" method="POST">
                                @csrf
                                @method('PUT')
                                <x-form-input id="edit_title" label="Title" name="title" value="todoTitle"
                                    x-model="todoTitle" required />
                                <x-form-input id="edit_description" label="Description" name="description" type="string"
                                    value="todoDescription" x-model="todoDescription" required />
                                <div class="flex justify-end">
                                    <button type="button" x-on:click="openEditModal = false"
                                        class="bg-gray-500 text-white py-2 px-4 rounded mr-2">Cancel</button>
                                    <button type="submit"
                                        class="bg-blue-500 text-white py-2 px-4 rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </x-app-layout>

   ```

2. **ToDo Create and Edit Views**

   We'll handle the creating and editing of todos via the modals defined in the `todos/index.blade.php`.

3. **Create Custom Blade:**
    **app/Providers/AppServiceProvider:**
    ```php
    <?php

    namespace App\Providers;

    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Blade;
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        /**
        * Register any application services.
        */
        public function register(): void
        {
            //
        }

        /**
        * Bootstrap any application services.
        */
        public function boot(): void
        {
            Blade::if('admin', function() {
                return Auth::check() && Auth::user()->role === 'admin';
            });
        }
    }

    ```
4. **Edit Navigation:**
    **app/resources/views/layouts/navigation.blade.php:**
    ```php
    <?php
    <!-- Navigation Links -->
    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-nav-link>
        @admin
            <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                {{ __('User') }}
            </x-nav-link>
        @endadmin
        <x-nav-link :href="route('todos.index')" :active="request()->routeIs('todos.index')">
            {{ __('Todo') }}
        </x-nav-link>
    </div>
    ```


By following these steps, you've built a comprehensive ToDo project with User and ToDo management using Laravel Breeze, Blade components, Tailwind CSS, and Alpine.js. The use of components for tables, modals, and notifications ensures a clean and maintainable codebase.