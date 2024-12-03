<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Todo') }}
        </h2>
    </x-slot>
    <div class="w-full flex justify-center items-center py-4">
        @if (session('status'))
            <x-notification color="green">
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
                                        x-on:click="editModal('{{ route('todos.update', $todo) }}', '{{ $todo->title }}', '{{ $todo->description }}')"
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
