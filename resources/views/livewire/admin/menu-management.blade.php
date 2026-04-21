<flux:main>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Menu Management</flux:heading>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle" class="mb-4">{{ session('error') }}</flux:callout>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 flex gap-2">
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
        {{-- Products --}}
        <div class="mb-4 flex justify-end">
            <flux:button wire:click="createProduct" variant="primary" icon="plus">Add Product</flux:button>
        </div>

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
                                    <button wire:click="toggleProductAvailability({{ $product->id }})" class="cursor-pointer">
                                        <flux:badge :color="$product->is_available ? 'green' : 'red'" size="sm">
                                            {{ $product->is_available ? 'Available' : 'Unavailable' }}
                                        </flux:badge>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="flex justify-end gap-1">
                                        <flux:button wire:click="editProduct({{ $product->id }})" size="sm" variant="ghost" icon="pencil" aria-label="Edit {{ $product->name }}" />
                                        <flux:button wire:click="deleteProduct({{ $product->id }})" wire:confirm="Delete {{ $product->name }}?" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" aria-label="Delete {{ $product->name }}" />
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
            <form wire:submit="saveProduct" class="space-y-4">
                <flux:heading size="lg">{{ $editingProductId ? 'Edit Product' : 'Add Product' }}</flux:heading>

                <flux:input wire:model="productName" :label="__('Name')" required />

                <flux:select wire:model="productCategory" :label="__('Category')" placeholder="Select category..." required>
                    @foreach ($categoryOptions as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="productDescription" :label="__('Description')" rows="2" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="productPrice" :label="__('Price (₱)')" type="number" step="0.01" min="1" required />
                    <flux:input wire:model="productStock" :label="__('Stock')" type="number" min="0" required />
                </div>

                <flux:input wire:model="productImage" :label="__('Image')" type="file" accept="image/*" />

                <flux:checkbox wire:model="productAvailable" :label="__('Available for ordering')" />

                <div class="flex justify-end gap-2">
                    <flux:button wire:click="$set('showProductModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingProductId ? 'Update' : 'Create' }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @else
        {{-- Categories --}}
        <div class="mb-4 flex justify-end">
            <flux:button wire:click="createCategory" variant="primary" icon="plus">Add Category</flux:button>
        </div>

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
                                        <flux:button wire:click="deleteCategory({{ $cat->id }})" wire:confirm="Delete {{ $cat->name }}? This only works if it has no products." size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" aria-label="Delete {{ $cat->name }}" />
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
            <form wire:submit="saveCategory" class="space-y-4">
                <flux:heading size="lg">{{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}</flux:heading>

                <flux:input wire:model="categoryName" :label="__('Name')" required />
                <flux:checkbox wire:model="categoryActive" :label="__('Active')" />

                <div class="flex justify-end gap-2">
                    <flux:button wire:click="$set('showCategoryModal', false)" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">{{ $editingCategoryId ? 'Update' : 'Create' }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</flux:main>
