<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Coupon Details') }}: {{ $coupon->code }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Coupon
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Coupons
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">


            <!-- Coupon Preview Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-8 rounded-lg inline-block">
                            <div class="text-4xl font-bold mb-2">{{ $coupon->code }}</div>
                            <div class="text-2xl mb-1">{{ $coupon->discount_percentage }}% OFF</div>
                            <div class="text-sm opacity-90">
                                @if($coupon->expiry_date)
                                    Valid until {{ $coupon->expiry_date->format('M d, Y') }}
                                @else
                                    No expiry date
                                @endif
                            </div>
                            @if($coupon->usage_limit)
                                <div class="text-sm opacity-90 mt-1">
                                    Limited to {{ $coupon->usage_limit }} uses
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupon Status and Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-medium text-gray-900">Current Status</h3>
                            @if($coupon->is_active)
                                @if($coupon->expiry_date && $coupon->expiry_date->isPast())
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit)
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                                        Limit Reached
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            @else
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <form method="POST" action="{{ route('admin.coupons.toggle-active', $coupon) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="bg-{{ $coupon->is_active ? 'yellow' : 'green' }}-500 hover:bg-{{ $coupon->is_active ? 'yellow' : 'green' }}-700 text-white font-bold py-2 px-4 rounded">
                                    {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to delete this coupon? This action cannot be undone.')" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupon Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Coupon Code</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $coupon->code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Discount Percentage</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->discount_percentage }}%</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Active Status</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->is_active ? 'Yes' : 'No' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->created_at->format('M d, Y \a\t H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->updated_at->format('M d, Y \a\t H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Usage Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Times Used</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->usage_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usage Limit</dt>
                                <dd class="text-sm text-gray-900">{{ $coupon->usage_limit ?? 'Unlimited' }}</dd>
                            </div>
                            @if($coupon->usage_limit)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Remaining Uses</dt>
                                    <dd class="text-sm text-gray-900">{{ max(0, $coupon->usage_limit - $coupon->usage_count) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Usage Progress</dt>
                                    <dd class="text-sm text-gray-900">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($coupon->usage_count / $coupon->usage_limit) * 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 mt-1">{{ round(($coupon->usage_count / $coupon->usage_limit) * 100, 1) }}% used</span>
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($coupon->expiry_date)
                                        {{ $coupon->expiry_date->format('M d, Y \a\t H:i') }}
                                        @if($coupon->expiry_date->isPast())
                                            <span class="text-red-600 font-medium">(Expired)</span>
                                        @elseif($coupon->expiry_date->isToday())
                                            <span class="text-yellow-600 font-medium">(Expires today)</span>
                                        @elseif($coupon->expiry_date->diffInDays() <= 7)
                                            <span class="text-orange-600 font-medium">(Expires in {{ $coupon->expiry_date->diffInDays() }} days)</span>
                                        @endif
                                    @else
                                        No expiry date
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Validity Check -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Validity Status</h3>
                    
                    @if($coupon->isValid())
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="text-green-800 font-medium">This coupon is valid and can be used</h4>
                                    <p class="text-green-600 text-sm mt-1">Customers can apply this coupon code during checkout.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="text-red-800 font-medium">This coupon is not valid</h4>
                                    <p class="text-red-600 text-sm mt-1">
                                        @if(!$coupon->is_active)
                                            The coupon is currently inactive.
                                        @elseif($coupon->expiry_date && $coupon->expiry_date->isPast())
                                            The coupon has expired.
                                        @elseif($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit)
                                            The coupon has reached its usage limit.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
