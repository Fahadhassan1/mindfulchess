<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Coupon') }}
            </h2>
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Coupons
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-6">
                        @csrf

                        <!-- Coupon Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Coupon Code</label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   placeholder="e.g., SAVE20, WELCOME10"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('code') border-red-300 @enderror">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Coupon code will be automatically converted to uppercase.</p>
                        </div>

                        <!-- Discount Percentage -->
                        <div>
                            <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount Percentage</label>
                            <input type="number" 
                                   id="discount_percentage" 
                                   name="discount_percentage" 
                                   value="{{ old('discount_percentage') }}" 
                                   min="1" 
                                   max="100" 
                                   step="0.01"
                                   placeholder="e.g., 20"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('discount_percentage') border-red-300 @enderror">
                            @error('discount_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Enter a value between 1 and 100.</p>
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date (Optional)</label>
                            <input type="datetime-local" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   value="{{ old('expiry_date') }}" 
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('expiry_date') border-red-300 @enderror">
                            @error('expiry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Leave empty for no expiry date.</p>
                        </div>

                        <!-- Usage Limit -->
                        <div>
                            <label for="usage_limit" class="block text-sm font-medium text-gray-700">Usage Limit (Optional)</label>
                            <input type="number" 
                                   id="usage_limit" 
                                   name="usage_limit" 
                                   value="{{ old('usage_limit') }}" 
                                   min="1"
                                   placeholder="e.g., 100"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('usage_limit') border-red-300 @enderror">
                            @error('usage_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Leave empty for unlimited usage.</p>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                    Active
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Uncheck to create an inactive coupon.</p>
                        </div>

                        <!-- Preview Section -->
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Coupon Preview</h3>
                            <div class="bg-white p-4 rounded border-2 border-dashed border-gray-300">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600" id="preview-code">COUPON CODE</div>
                                    <div class="text-lg text-gray-600 mt-1">
                                        <span id="preview-discount">0</span>% OFF
                                    </div>
                                    <div class="text-sm text-gray-500 mt-2">
                                        <div id="preview-expiry">No expiry</div>
                                        <div id="preview-limit">Unlimited usage</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('admin.coupons.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Create Coupon
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('code');
            const discountInput = document.getElementById('discount_percentage');
            const expiryInput = document.getElementById('expiry_date');
            const limitInput = document.getElementById('usage_limit');
            
            const previewCode = document.getElementById('preview-code');
            const previewDiscount = document.getElementById('preview-discount');
            const previewExpiry = document.getElementById('preview-expiry');
            const previewLimit = document.getElementById('preview-limit');

            function updatePreview() {
                previewCode.textContent = codeInput.value.toUpperCase() || 'COUPON CODE';
                previewDiscount.textContent = discountInput.value || '0';
                
                if (expiryInput.value) {
                    const date = new Date(expiryInput.value);
                    previewExpiry.textContent = 'Expires: ' + date.toLocaleDateString();
                } else {
                    previewExpiry.textContent = 'No expiry';
                }
                
                if (limitInput.value) {
                    previewLimit.textContent = 'Limited to ' + limitInput.value + ' uses';
                } else {
                    previewLimit.textContent = 'Unlimited usage';
                }
            }

            codeInput.addEventListener('input', updatePreview);
            discountInput.addEventListener('input', updatePreview);
            expiryInput.addEventListener('input', updatePreview);
            limitInput.addEventListener('input', updatePreview);

            // Initialize preview
            updatePreview();
        });
    </script>
</x-app-layout>
