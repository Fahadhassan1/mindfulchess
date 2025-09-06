<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Management') }}
            </h2>
            <a href="{{ route('admin.teachers.statistics') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                </svg>
                View Statistics
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
         

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __("Manage Teachers") }}</h3>
                    
                    <!-- Search and Filter Form -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input 
                                    type="text" 
                                    name="search" 
                                    id="search" 
                                    placeholder="Name, email or qualification..."
                                    value="{{ request('search') }}" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                            </div>
                            
                            <div class="w-full sm:w-auto">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select 
                                    name="status" 
                                    id="status" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="no_profile" {{ request('status') === 'no_profile' ? 'selected' : '' }}>No Profile</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <x-primary-button>
                                    Filter
                                </x-primary-button>

                                @if(request('search') || request('status'))
                                    <a href="{{ route('admin.teachers.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
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

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teaching Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sessions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($teachers as $teacher)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($teacher->teacherProfile && $teacher->teacherProfile->teaching_type)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($teacher->teacherProfile->teaching_type == 'adult') bg-purple-100 text-purple-800
                                            @elseif($teacher->teacherProfile->teaching_type == 'kids') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($teacher->teacherProfile->teaching_type) }}
                                            </span>
                                        @else
                                            Not specified
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-sm leading-5 font-medium rounded-full {{ $teacher->sessions_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $teacher->sessions_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!$teacher->teacherProfile)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                No Profile
                                            </span>
                                        @elseif($teacher->teacherProfile->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 text-right py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.teachers.show', $teacher) }}" class="text-green-600 hover:text-green-900 mr-2">View</a>
                                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="text-blue-600 hover:text-blue-900 mr-2">Edit</a>
                                        <a href="{{ route('admin.teachers.statistics.show', $teacher) }}" class="text-purple-600 hover:text-purple-900 mr-2">Stats</a>
                                        
                                        <form action="{{ route('admin.teachers.toggle-active', $teacher) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-{{ $teacher->teacherProfile && !$teacher->teacherProfile->is_active ? 'green' : 'red' }}-600 hover:text-{{ $teacher->teacherProfile && !$teacher->teacherProfile->is_active ? 'green' : 'red' }}-900">
                                                {{ $teacher->teacherProfile && $teacher->teacherProfile->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                
                                @if($teachers->isEmpty())
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No teachers found matching your criteria
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $teachers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
