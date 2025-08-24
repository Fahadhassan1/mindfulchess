<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <form action="{{ $route }}" method="GET" class="flex flex-wrap gap-4">
        @if(isset($showSearch) && $showSearch)
        <div class="flex-1 min-w-[200px]">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ $searchLabel ?? 'Search' }}</label>
            <input 
                type="text" 
                name="search" 
                id="search" 
                placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
                value="{{ request('search') }}" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
            >
        </div>
        @endif

        @if(isset($filterOptions) && count($filterOptions) > 0)
            <div class="w-full sm:w-auto">
                <label for="{{ $filterName }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $filterLabel }}</label>
                <select 
                    name="{{ $filterName }}" 
                    id="{{ $filterName }}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-800 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                >
                    <option value="">{{ $filterAllLabel ?? 'All' }}</option>
                    @foreach($filterOptions as $value => $label)
                        <option value="{{ $value }}" {{ request($filterName) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex items-end">
            <button type="submit" class="bg-primary-800 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded">
                {{ $buttonLabel ?? 'Filter' }}
            </button>
            
            @if(count(array_filter(request()->all())) > 0)
                <a href="{{ $route }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>
