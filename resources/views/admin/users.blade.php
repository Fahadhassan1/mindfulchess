<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
            <a href="{{ route('admin.users.create') }}" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                Create User
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
         
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Manage System Users") }}</h3>
                    
                    <!-- Search and Filter Form -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input 
                                    type="text" 
                                    name="search" 
                                    id="search" 
                                    placeholder="Name or email..."
                                    value="{{ request('search') }}" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                            </div>
                            
                            <div class="w-full sm:w-auto">
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select 
                                    name="role" 
                                    id="role" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                               <x-primary-button>
                                    Filter
                                </x-primary-button>

                                @if(request('search') || request('role'))
                                    <a href="{{ route('admin.users') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @foreach($user->roles as $role)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($role->name == 'admin') bg-red-100 text-red-800
                                            @elseif($role->name == 'teacher') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800 @endif">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    {{-- // can't be delete or edit admin user --}}
                                    @if($user->hasRole('admin'))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                            --
                                        </td>
                                        @continue
                                    @else
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                                        
                                        <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                                
                                @if($users->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No users found matching your criteria
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
