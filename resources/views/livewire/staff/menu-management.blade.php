<flux:main>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Menu Management</flux:heading>
    </div>

    {{-- Low Stock Alert --}}
    @if ($lowStockProducts->isNotEmpty())
        <div class="mb-6 rounded-xl p-4 shadow-sm" style="background-color: #D0342C;">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.exclamation-triangle class="size-5 text-white" />
                    <span class="text-sm font-bold text-white">Low Stock Alert</span>
                </div>
                <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white">
                    {{ $lowStockProducts->count() }} {{ Str::plural('item', $lowStockProducts->count()) }} need restocking
                </span>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lowStockProducts as $product)
                    <div class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white">{{ $product->name }}</p>
                            <p class="text-xs text-white/70">{{ $product->category->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="ml-3 shrink-0 text-right">
                            @if ($product->stock === 0)
                                <span class="rounded-md bg-white px-2 py-0.5 text-xs font-bold" style="color: #D0342C;">OUT OF STOCK</span>
                            @else
                                <span class="text-sm font-bold text-white">{{ $product->stock }}</span>
                                <p class="text-xs text-white/70">remaining</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 flex items-center justify-between gap-2">
        <div class="flex gap-2">
            <button wire:click="$set('activeTab', 'products')" @class([
                'rounded-full px-4 py-2 text-sm font-medium transition',
                'bg-orange-500 text-white' => $activeTab === 'products',
                'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300' => $activeTab !== 'products',
            ])>Products</button>
            <button wire:click="$set('activeTab', 'categories')" @class([
                'rounded-full px-4 py-2 text-sm font-medium transition',
                'bg-orange-500 text-white' => $activeTab === 'categories',
                'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300' => $activeTab !== 'categories',
            ])>Categories</button>
        </div>
        @if ($activeTab === 'products')
            <flux:button wire:click="createProduct" variant="primary" icon="plus">Add Product</flux:button>
        @elseif ($activeTab === 'categories')
            <flux:button wire:click="createCategory" variant="primary" icon="plus">Add Category</flux:button>
        @endif
    </div>

    @if ($activeTab === 'products')

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="px-4 py-3 font-medium text-zinc-500">Product</th>
                            <th class="px-4 py-3 font-medium text-zinc-500">Category</th>
                            <th class="px-4 py-3 text-end font-medium text-zinc-500">Price</th>
                            <th class="px-4 py-3 text-end font-medium text-zinc-500">Stock</th>
                            <th class="px-4 py-3 font-medium text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-end font-medium text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($product->imageUrl())
                                            <img src="{{ $product->imageUrl() }}" alt="" class="size-10 rounded-lg object-cover">
                                        @else
                                            <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                                                <flux:icon.photo class="size-5 text-zinc-400" />
                                            </div>
                                        @endif
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $product->category->name }}</td>
                                <td class="px-4 py-3 text-end font-semibold text-zinc-900 dark:text-zinc-100">₱{{ number_format($product->price, 0) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <span @class(['font-medium', 'text-red-500' => $product->stock <= 5, 'text-zinc-900 dark:text-zinc-100' => $product->stock > 5])>
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <button wire:click="toggleProductAvailability({{ $product->id }})" class="cursor-pointer" @disabled($product->stock === 0)>
                                        @if ($product->stock === 0)
                                            <flux:badge color="zinc" size="sm">Out of Stock</flux:badge>
                                        @else
                                            <flux:badge :color="$product->is_available ? 'green' : 'red'" size="sm">
                                                {{ $product->is_available ? 'Available' : 'Unavailable' }}
                                            </flux:badge>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex justify-end gap-1">
                                        <flux:button wire:click="editProduct({{ $product->id }})" size="sm" variant="ghost" icon="pencil" aria-label="Edit {{ $product->name }}" />
                                        <flux:button wire:click="confirmDelete({{ $product->id }}, 'product')" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" aria-label="Delete {{ $product->name }}" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-500">No products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Product Modal --}}
        <flux:modal wire:model="showProductModal" class="max-w-lg md:min-w-lg">
            <form wire:submit.prevent="saveProduct" class="space-y-4">
                <flux:heading size="lg">{{ $editingProductId ? 'Edit Product' : 'Add Product' }}</flux:heading>

                @if ($errors->any())
                    <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <flux:input wire:model.defer="productName" :label="__('Name')" required />
                @error('productName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror

                <div>
                    <flux:select wire:model.defer="productCategory" :label="__('Category')" placeholder="Select category..." required>
                        @foreach ($categoryOptions as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('productCategory') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <flux:textarea wire:model.defer="productDescription" :label="__('Description')" rows="2" />
                @error('productDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input wire:model.defer="productPrice" :label="__('Price (₱)')" type="number" step="0.01" min="1" required />
                        @error('productPrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <flux:input wire:model.defer="productStock" :label="__('Stock')" type="number" min="0" required />
                        @error('productStock') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <flux:input wire:model="productImage" :label="__('Image')" type="file" accept="image/*" />
                @error('productImage') <span class="text-xs text-red-600">{{ $message }}</span> @enderror

                <flux:checkbox wire:model.defer="productAvailable" :label="__('Available for ordering')" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" wire:click="$set('showProductModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingProductId ? 'Update' : 'Create' }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @else
        {{-- Categories --}}

        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="px-4 py-3 font-medium text-zinc-500">Category</th>
                            <th class="px-4 py-3 text-end font-medium text-zinc-500">Products</th>
                            <th class="px-4 py-3 font-medium text-zinc-500">Status</th>
                            <th class="px-4 py-3 text-end font-medium text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @forelse ($categories as $cat)
                            <tr>
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $cat->name }}</td>
                                <td class="px-4 py-3 text-end text-zinc-600 dark:text-zinc-400">{{ $cat->products_count }}</td>
                                <td class="px-4 py-3">
                                    <flux:badge :color="$cat->is_active ? 'green' : 'red'" size="sm">
                                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex justify-end gap-1">
                                        <flux:button wire:click="editCategory({{ $cat->id }})" size="sm" variant="ghost" icon="pencil" aria-label="Edit {{ $cat->name }}" />
                                        <flux:button wire:click="confirmDelete({{ $cat->id }}, 'category')" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" aria-label="Delete {{ $cat->name }}" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-zinc-500">No categories yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Category Modal --}}
        <flux:modal wire:model="showCategoryModal" class="max-w-md">
            <form wire:submit.prevent="saveCategory" class="space-y-4">
                <flux:heading size="lg">{{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}</flux:heading>

                <flux:input wire:model.defer="categoryName" :label="__('Name')" required />
                <flux:checkbox wire:model.defer="categoryActive" :label="__('Active')" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" wire:click="$set('showCategoryModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingCategoryId ? 'Update' : 'Create' }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm md:min-w-sm">
        <div class="space-y-4">
            <flux:heading size="lg">Delete {{ $deletingType === 'product' ? 'Product' : 'Category' }}</flux:heading>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingName }}</span>?
                @if ($deletingType === 'category')
                    This only works if the category has no products.
                @else
                    This action cannot be undone.
                @endif
            </p>
            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="deleteConfirmed" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
