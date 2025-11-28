<x-admin-layout>
    <x-slot name="header">Edit Product: {{ $product->name }}</x-slot>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column: Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Product Information</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', $product->name) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="5" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                            <input type="number" step="0.01" name="price" id="price"
                                value="{{ old('price', $product->price) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Current Images Gallery -->
                @if ($product->images->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Images</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ($product->images as $image)
                                <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ asset('storage/' . $image->image_url) }}" alt="{{ $image->alt_text }}"
                                        class="w-full h-32 object-cover">
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <!-- We need to use a separate form or AJAX for deletion to avoid submitting the main form -->
                                        <!-- For simplicity in this edit form, we can't easily nest forms.
                                     We'll make these buttons trigger a separate form submission via JS or use a link/button outside.
                                     Actually, HTML5 doesn't allow nested forms.
                                     Best approach: Use a button that submits to a delete route via JS fetch or a separate hidden form.
                                     Let's use a simple link that acts as a form submit. -->
                                        <button type="button"
                                            onclick="deleteImage('{{ route('admin.products.images.delete', $image) }}')"
                                            class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 focus:outline-none">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Meta & Media -->
            <div class="space-y-6">

                <!-- Organization -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Organization</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock
                                Quantity</label>
                            <input type="number" name="stock_quantity" id="stock_quantity"
                                value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('stock_quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="metric" class="block text-sm font-medium text-gray-700">Metric</label>
                            <select name="metric" id="metric"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="kg"
                                    {{ old('metric', $product->metric->value ?? $product->metric) == 'kg' ? 'selected' : '' }}>
                                    Kg</option>
                                <option value="liter"
                                    {{ old('metric', $product->metric->value ?? $product->metric) == 'liter' ? 'selected' : '' }}>
                                    Liter</option>
                                <option value="piece"
                                    {{ old('metric', $product->metric->value ?? $product->metric) == 'piece' ? 'selected' : '' }}>
                                    Piece</option>
                                <option value="dozen"
                                    {{ old('metric', $product->metric->value ?? $product->metric) == 'dozen' ? 'selected' : '' }}>
                                    Dozen</option>
                            </select>
                            @error('metric')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="categories" class="block text-sm font-medium text-gray-700">Categories</label>
                            <select name="categories[]" id="categories" multiple
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-32">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $product->categories->contains($category->id) ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                            @error('categories')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Images</h3>

                    <div
                        class="mt-1 flex justify-center px-6 pt-8 pb-8 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors cursor-pointer relative bg-gray-50 hover:bg-gray-100">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48" aria-hidden="true">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="images"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 px-2 py-1">
                                    <span>Upload images</span>
                                    <input id="images" name="images[]" type="file" multiple accept="image/*"
                                        class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
                        </div>
                        <script>
                            document.getElementById('images').addEventListener('change', function(e) {
                                const count = e.target.files.length;
                                const text = count > 0 ? `${count} file(s) selected` : 'Upload images';
                                e.target.parentElement.querySelector('span').textContent = text;
                            });
                        </script>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="w-full inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Update Product
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Hidden form for image deletion -->
    <form id="delete-image-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function deleteImage(url) {
            if (confirm('Are you sure you want to delete this image?')) {
                const form = document.getElementById('delete-image-form');
                form.action = url;
                form.submit();
            }
        }
    </script>
</x-admin-layout>
